<?php

namespace App\Http\Controllers;

use App\Http\Requests\EquipmentRequest;
use App\Models\Equipment;

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
        return Equipment::create($request->validated());
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
