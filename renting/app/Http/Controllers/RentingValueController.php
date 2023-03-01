<?php

namespace App\Http\Controllers;

use App\Models\RentingValue;
use Illuminate\Http\Request;

class RentingValueController extends Controller
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'values.*.value' => ['required', 'numeric'],
            'values.*.period_id' => ['required', 'exists:App\Models\Period,id'],
            'values.*.equipment_id' => ['required'],
        ]);

        foreach ($validated['values'] as $value) {
            RentingValue::create($value);
        }

        return response(null, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(RentingValue $rentingValue)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RentingValue $rentingValue)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RentingValue $rentingValue)
    {
        //
    }
}
