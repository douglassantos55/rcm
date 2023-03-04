<?php

namespace App\Http\Controllers;

use App\Http\Requests\EquipmentRequest;
use App\Models\Equipment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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

        $response = $this->client->post('/api/renting-values', [
            'values' => $values,
        ]);

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

        $response = $this->client->put('/api/renting-values', [
            'values' => $request->input('values'),
        ]);

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
