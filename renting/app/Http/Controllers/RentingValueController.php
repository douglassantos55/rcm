<?php

namespace App\Http\Controllers;

use App\Models\RentingValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentingValueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $equipment = $request->query('equipment_id');
        if (is_null($equipment)) {
            return response('equipment_id required', 400);
        }

        return RentingValue::where('equipment_id', $equipment)
            ->get()
            ->keyBy('period_id');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'values' => ['required'],
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
    public function update(Request $request)
    {
        $validated = $request->validate([
            'values' => ['required'],
            'values.*.id' => ['required', 'exists:\App\Models\RentingValue,id'],
            'values.*.value' => ['required', 'numeric'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['values'] as $value) {
                /** @var RentingValue */
                $rentingValue = RentingValue::findOrFail($value['id']);
                $rentingValue->update(['value' => $value['value']]);
            }
        });

        return response(null, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RentingValue $rentingValue)
    {
        //
    }
}
