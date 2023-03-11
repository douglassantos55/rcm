<?php

namespace App\Http\Controllers;

use App\Http\Services\PaymentService;
use App\Models\Rent;
use App\Rules\Exists;
use Illuminate\Http\Request;

class RentController extends Controller
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
    public function store(Request $request, PaymentService $paymentService)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'qty_days' => ['required', 'integer'],
            'discount' => ['nullable', 'numeric'],
            'paid_value' => ['nullable', 'numeric'],
            'delivery_value' => ['nullable', 'numeric'],
            'bill' => ['nullable', 'numeric'],
            'customer_id' => ['required', 'exists:\App\Models\Customer,id'],
            'period_id' => ['required', 'exists:\App\Models\Period,id'],
            'payment_type_id' => ['required', new Exists($paymentService)],
            'payment_method_id' => ['required', new Exists($paymentService)],
            'payment_condition_id' => ['required', new Exists($paymentService)],
        ]);

        return response(Rent::create($validated)->refresh(), 201);
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
