<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeriodRequest;
use App\Models\Period;

class PeriodController extends Controller
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
    public function store(PeriodRequest $request)
    {
        return Period::create($request->validated())->refresh();
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
    public function update(PeriodRequest $request, Period $period)
    {
        $period->update($request->validated());
        return $period;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Period $period)
    {
        $period->delete();
        return response()->noContent();
    }
}
