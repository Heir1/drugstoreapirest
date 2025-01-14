<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use Illuminate\Http\Request;

class RateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rates = Rate::all();
        return response()->json($rates);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|numeric',
        ]);

        $rate = Rate::create($validated);

        return response()->json($rate, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Rate $rate)
    {
        //
        return response()->json($rate);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rate $rate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rate $rate)
    {
        //

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'value' => 'sometimes|required|numeric',
        ]);

        $rate->update($validated);

        return response()->json($rate);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rate $rate)
    {
        //
        $rate->delete();

        return response()->json(null, 204);
    }
}
