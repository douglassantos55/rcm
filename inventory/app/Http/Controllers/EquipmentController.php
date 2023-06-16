<?php

namespace App\Http\Controllers;

use App\Http\Requests\EquipmentRequest;
use App\Models\Equipment;
use App\Repositories\EquipmentRepository;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquipmentController extends Controller
{
    private $service;

    /**
     * @var EquipmentRepository
     */
    private $repository;

    public function __construct(PricingService $service, EquipmentRepository $repository)
    {
        $this->service = $service;
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        if ($request->query('description')) {
            $this->repository->contains('description', $request->query('description'));
        }

        if ($request->query('supplier')) {
            $this->repository->where('supplier_id', $request->query('supplier'));
        }

        return $this->repository->with('supplier')->get();
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
