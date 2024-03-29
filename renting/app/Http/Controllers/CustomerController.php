<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Repositories\CustomerRepository;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * @var CustomerRepository
     */
    private $repository;

    /**
     * @param CustomerRepository $repository
     */
    public function __construct(CustomerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->repository
            ->orderBy('name', 'ASC')
            ->contains('name', $request->query('name'))
            ->contains('email', $request->query('email'))
            ->contains('cpf_cnpj', $request->query('cpf_cnpj'))
            ->paginate($request->query('page', 1), $request->query('per_page', 50));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request)
    {
        return $this->repository->create($request->input());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $customer)
    {
        return $this->repository->find($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, string $customer)
    {
        return $this->repository->update($customer, $request->input());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $customer)
    {
        $this->repository->delete($customer);
        return response()->noContent();
    }
}
