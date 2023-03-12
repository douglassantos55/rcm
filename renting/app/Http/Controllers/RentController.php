<?php

namespace App\Http\Controllers;

use App\Http\Requests\RentRequest;
use App\Models\Rent;

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
    public function store(RentRequest $request)
    {
        return response(Rent::create($request->validated())->refresh(), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Rent $rent)
    {
        return $rent;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RentRequest $request, Rent $rent)
    {
        $rent->update($request->validated());
        return $rent;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rent $rent)
    {
        $rent->delete();
        return response()->noContent();
    }
}
