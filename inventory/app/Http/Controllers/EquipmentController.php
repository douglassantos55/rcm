<?php

namespace App\Http\Controllers;

use App\Http\Requests\EquipmentRequest;
use App\Models\Equipment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class EquipmentController extends Controller
{
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

        $response = Http::post('http://localhost:8001/api/renting-values', ['values' => $values]);

        if (!$response->successful()) {
            DB::rollBack();
            return $response;
        }

        DB::commit();

        return $equipment;
    }

    public function update(EquipmentRequest $request, Equipment $equipment)
    {
        $equipment->update($request->validated());
        return $equipment;
    }

    public function destroy(Equipment $equipment)
    {
        $equipment->delete();
        return response()->noContent();
    }
}
