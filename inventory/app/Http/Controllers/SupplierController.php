<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Repositories\SupplierRepository;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * @var SupplierRepository
     */
    private $repository;

    public function __construct(SupplierRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->repository
            ->orderBy('social_name')
            ->contains('social_name', $request->query('social_name'))
            ->contains('email', $request->query('email'))
            ->contains('cnpj', $request->query('cnpj'))
            ->paginate($request->query('page', 1), $request->query('per_page', 50));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request)
    {
        return $this->repository->create($request->input());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $supplier)
    {
        return $this->repository->find($supplier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, string $supplier)
    {
        return $this->repository->update($supplier, $request->input());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $supplier)
    {
        $this->repository->delete($supplier);
        return response()->noContent();
    }
}
