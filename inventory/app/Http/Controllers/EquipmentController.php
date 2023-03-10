<?php

namespace App\Http\Controllers;

use App\Http\Requests\EquipmentRequest;
use App\Http\Services\RentingService;
use App\Models\Equipment;
use Illuminate\Support\Facades\DB;

class EquipmentController extends Controller
{
    private $service;

    public function __construct(RentingService $service)
    {
        $this->service = $service;
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

        $response = $this->service->createRentingValues(
            array_map(function (array $value) use ($equipment) {
                return ['equipment_id' => $equipment->id, ...$value];
            }, $request->post('values', []))
        );

        if (!$response->isSuccessful()) {
            DB::rollBack();
            return $response;
        }

        DB::commit();
        return $equipment;
    }

    public function update(EquipmentRequest $request, Equipment $equipment)
    {
        DB::beginTransaction();
        $equipment->update($request->validated());

        $response = $this->service->updateRentingValues($request->input('values'));

        if (!$response->isSuccessful()) {
            DB::rollBack();
            return $response;
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
