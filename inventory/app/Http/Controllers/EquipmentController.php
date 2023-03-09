<?php

namespace App\Http\Controllers;

use App\Http\Requests\EquipmentRequest;
use App\Models\Equipment;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class EquipmentController extends Controller
{
    /** @var PendingRequest */
    private $client;

    public function __construct()
    {
        $this->client = Http::baseUrl(env('RENTING_SERVICE'))
            ->withHeaders(['accept' => 'application/json']);
    }

    public function index()
    {
        return Equipment::all();
    }

    public function show(Equipment $equipment)
    {
        return $equipment;
    }

    public function store(EquipmentRequest $request)
    {
        DB::beginTransaction();

        $equipment = Equipment::create($request->validated());

        $values = array_map(function (array $value) use ($equipment) {
            return ['equipment_id' => $equipment->id, ...$value];
        }, $request->post('values', []));

        $response = RateLimiter::attempt('renting-service', 5, function () use ($values) {
            try {
                return $this->client->timeout(2)->post('/api/renting-values', [
                    'values' => $values
                ]);
            } catch (HttpClientException $e) {
                Log::error('could not reach renting service: ' . $e->getMessage());
                return false;
            }
        });

        if ($response === false) {
            DB::rollBack();
            return response('could not reach renting service', 500);
        }

        if (!$response->successful()) {
            DB::rollBack();
            return response()->fromClient($response);
        }

        DB::commit();

        return $equipment;
    }

    public function update(EquipmentRequest $request, Equipment $equipment)
    {
        DB::beginTransaction();

        if (!$equipment->update($request->validated())) {
            DB::rollBack();
            return response(null, 500);
        }

        $response = RateLimiter::attempt('renting-service', 5, function () use ($request) {
            try {
                return $this->client->timeout(2)->put('/api/renting-values', [
                    'values' => $request->input('values'),
                ]);
            } catch (HttpClientException $e) {
                Log::error('could not reach renting service: ' . $e->getMessage());
                return false;
            }
        });

        if ($response === false) {
            DB::rollBack();
            return response('could not reach renting service', 500);
        }

        if (!$response->successful()) {
            DB::rollBack();
            return response()->fromClient($response);
        }

        DB::commit();

        return $equipment;
    }

    public function destroy(Equipment $equipment)
    {
        $equipment->delete();
        return response()->noContent();
    }
}
