<?php

namespace Tests\Unit;

use App\Metrics\Prometheus\Registry;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use Tests\TestCase;

class PrometheusRegistryTest extends TestCase
{
    /**
     * @var CollectorRegistry
     */
    private $promRegistry;

    /**
     * @var Registry
     */
    private $registry;

    public function setUp(): void
    {
        parent::setUp();

        $this->promRegistry = new CollectorRegistry(new InMemory());
        $this->registry = new Registry($this->promRegistry);
    }

    public function test_counter_increment()
    {
        $counter = $this->registry->getOrCreateCounter('total_errors');

        $counter->increment();
        $counter->increment();

        $this->assertValue('total_errors', 2);
    }

    public function test_counter_increment_by()
    {
        $counter = $this->registry->getOrCreateCounter('total_requests');
        $counter->incrementBy(10);

        $this->assertValue('total_requests', 10);
    }

    public function test_counter_with_labels()
    {
        $counter = $this->registry->getOrCreateCounter('total_requests', '', ['status']);

        $counter->increment([404]);
        $counter->incrementBy(10, [200]);
        $counter->incrementBy(50, [500]);

        $this->assertValue('total_requests', 10, [200]);
        $this->assertValue('total_requests', 1, [404]);
        $this->assertValue('total_requests', 50, [500]);
    }

    public function test_gauge_increment()
    {
        $gauge = $this->registry->getOrCreateGauge('concurrent_requests');

        $gauge->increment();
        $gauge->increment();
        $gauge->increment();

        $this->assertValue('concurrent_requests', 3);
    }

    public function test_gauge_increment_by()
    {
        $gauge = $this->registry->getOrCreateGauge('concurrent_requests');
        $gauge->incrementBy(7);
        $this->assertValue('concurrent_requests', 7);
    }

    public function test_gauge_increment_with_labels()
    {
        $gauge = $this->registry->getOrCreateGauge('concurrent_requests', '', ['status']);

        $gauge->increment([200]);
        $gauge->increment([404]);
        $gauge->increment([500]);
        $gauge->increment([500]);

        $this->assertValue('concurrent_requests', 1, [200]);
        $this->assertValue('concurrent_requests', 1, [404]);
        $this->assertValue('concurrent_requests', 2, [500]);
    }

    public function test_gauge_increment_by_with_labels()
    {
        $gauge = $this->registry->getOrCreateGauge('concurrent_requests', '', ['status']);

        $gauge->incrementBy(10, [200]);
        $gauge->incrementBy(5, [404]);
        $gauge->incrementBy(5, [500]);
        $gauge->incrementBy(5, [500]);

        $this->assertValue('concurrent_requests', 10, [200]);
        $this->assertValue('concurrent_requests', 5, [404]);
        $this->assertValue('concurrent_requests', 10, [500]);
    }

    public function test_gauge_decrement()
    {
        $gauge = $this->registry->getOrCreateGauge('concurrent_requests');

        $gauge->incrementBy(7);
        $gauge->decrement();

        $this->assertValue('concurrent_requests', 6);
    }

    public function test_gauge_decrement_with_labels()
    {
        $gauge = $this->registry->getOrCreateGauge('concurrent_requests', '', ['status']);

        $gauge->incrementBy(3, [200]);
        $gauge->decrement([200]);
        $gauge->decrement([404]);

        $this->assertValue('concurrent_requests', 2, [200]);
        $this->assertValue('concurrent_requests', -1, [404]);
    }

    public function test_gauge_decrement_by()
    {
        $gauge = $this->registry->getOrCreateGauge('concurrent_requests');

        $gauge->incrementBy(7);
        $gauge->decrementBy(5);

        $this->assertValue('concurrent_requests', 2);
    }

    public function test_gauge_decrement_by_with_labels()
    {
        $gauge = $this->registry->getOrCreateGauge('concurrent_requests', '', ['status', 'origin']);

        $gauge->incrementBy(6, [200, 'consumer']);
        $gauge->decrementBy(5, [200, 'consumer']);
        $gauge->decrementBy(5, [404, 'consumer']);
        $gauge->decrementBy(5, [200, 'inventory']);

        $this->assertValue('concurrent_requests', 1, [200, 'consumer']);
        $this->assertValue('concurrent_requests', -5, [404, 'consumer']);
        $this->assertValue('concurrent_requests', -5, [200, 'inventory']);
    }

    public function test_gauge_set()
    {
        $gauge = $this->registry->getOrCreateGauge('concurrent_requests');
        $gauge->set(51);
        $this->assertValue('concurrent_requests', 51);
    }

    public function test_gauge_set_with_labels()
    {
        $gauge = $this->registry->getOrCreateGauge('concurrent_requests', '', ['status']);

        $gauge->set(51, [200]);
        $gauge->set(5, [500]);
        $gauge->set(1, [404]);

        $this->assertValue('concurrent_requests', 51, [200]);
        $this->assertValue('concurrent_requests', 5, [500]);
        $this->assertValue('concurrent_requests', 1, [404]);
    }

    public function test_histogram()
    {
        $histogram = $this->registry->getOrCreateHistogram('request_duration', '', [], [2, 5, 10]);

        $histogram->observe(1);
        $histogram->observe(3);
        $histogram->observe(5);
        $histogram->observe(7);

        $this->assertValue('request_duration', 1, [], ['2']);
        $this->assertValue('request_duration', 3, [], ['5']);
        $this->assertValue('request_duration', 4, [], ['10']);
    }

    public function test_histogram_with_labels()
    {
        $histogram = $this->registry->getOrCreateHistogram('request_duration', '', ['source'], [2, 3, 5]);

        $histogram->observe(1, ['front']);
        $histogram->observe(1, ['front']);
        $histogram->observe(2, ['pricing']);
        $histogram->observe(4, ['pricing']);
        $histogram->observe(5, ['pricing']);
        $histogram->observe(3, ['inventory']);
        $histogram->observe(5, ['inventory']);

        $this->assertValue('request_duration', 2, ['front'], ['2']);
        $this->assertValue('request_duration', 2, ['front'], ['3']);
        $this->assertValue('request_duration', 2, ['front'], ['5']);

        $this->assertValue('request_duration', 1, ['pricing'], ['2']);
        $this->assertValue('request_duration', 1, ['pricing'], ['3']);
        $this->assertValue('request_duration', 3, ['pricing'], ['5']);

        $this->assertValue('request_duration', 0, ['inventory'], ['2']);
        $this->assertValue('request_duration', 1, ['inventory'], ['3']);
        $this->assertValue('request_duration', 2, ['inventory'], ['5']);
    }

    private function assertValue(string $name, int|float $value, array $labels = [], array $buckets = [])
    {
        $metrics = $this->promRegistry->getMetricFamilySamples();
        foreach ($metrics as $metric) {
            if ($metric->getName() !== $name) {
                continue;
            }

            foreach ($metric->getSamples() as $sample) {
                $sampleLabels = $sample->getLabelValues();
                if (empty($sampleLabels) && (!empty($labels) || !empty($buckets))) {
                    continue;
                }

                if (
                    (empty($labels) && empty($buckets)) ||
                    (!empty($labels) && !empty($buckets) && $sampleLabels === array_merge($labels, $buckets)) ||
                    (!empty($labels) && $sampleLabels === $labels)
                    || (!empty($buckets) && $sampleLabels === $buckets)
                ) {
                    return $this->assertEquals($value, $sample->getValue());
                }
            }
        }
        $this->fail(sprintf('Could not find metric for name %s, labels %s and buckets %s', $name, print_r($labels, TRUE), print_r($buckets, TRUE)));
    }
}
