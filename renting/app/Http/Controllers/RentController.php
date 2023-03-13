<?php

namespace App\Http\Controllers;

use App\Http\Requests\RentRequest;
use App\Models\Rent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $rents = Rent::query();

        if ($request->query('number')) {
            $rents->where('id', $request->query('number'));
        }

        if ($request->query('customer')) {
            $rents->where('customer_id', $request->query('customer'));
        }

        return $rents->paginate(50);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RentRequest $request)
    {
        $rent = Rent::create($request->validated());
        $rent->items()->createMany($request->validated('items'));

        return response($rent->refresh(), 201);
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
        DB::transaction(function () use ($rent, $request) {
            $rent->update($request->validated());
            $rent->items()->delete();
            $rent->items()->createMany($request->validated('items'));
        });

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
