<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Rules\CpfCnpj;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Supplier::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CpfCnpj $cnpj)
    {
        $validated = $request->validate([
            'social_name' => ['required'],
            'legal_name' => ['nullable'],
            'cnpj' => ['nullable', $cnpj, 'unique:App\Models\Supplier,cnpj'],
            'email' => ['nullable', 'email'],
            'website' => ['nullable', 'url'],
            'phone' => ['nullable', 'regex:/^\(\d{2}\) \d{4,5}-\d{4}$/'],
            'state' => ['nullable', 'size:2'],
            'postcode' => ['nullable', 'regex:/^\d{5}-?\d{3}$/'],
        ]);

        return Supplier::create($validated)->refresh();
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
