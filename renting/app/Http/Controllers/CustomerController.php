<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Rules\CpfCnpj;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CpfCnpj $cpfCnpj)
    {
        $validated = $request->validate([
            'name' => ['required'],
            'email' => ['nullable', 'email', 'unique:\App\Models\Customer,email'],
            'cpf_cnpj' => ['nullable', $cpfCnpj, 'unique:\App\Models\Customer,cpf_cnpj'],
            'state' => ['nullable', 'size:2'],
            'postcode' => ['nullable', 'regex:/^\d{5}-\d{3}$/'],
        ]);

        return Customer::create($validated)->refresh();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
