<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    // Display a listing of the currencies
    public function index()
    {
        $currencies = Currency::all(); // You can add pagination if needed
        return response()->json($currencies);
    }

    // Store a newly created currency in the database
    public function store(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|numeric',
            'symbol' => 'required|string|max:10',
            'row_id' => 'required|string|max:255|unique:currencies',
        ]);

        // Create a new currency record
        $currency = Currency::create([
            'name' => $request->name,
            'value' => $request->value,
            'symbol' => $request->symbol,
            'row_id' => $request->row_id,
            'created_by' => auth()->id(), // Optional, if you want to track the creator
        ]);

        return response()->json($currency, 201); // 201 Created response
    }

    // Display the specified currency
    public function show($id)
    {
        $currency = Currency::findOrFail($id);
        return response()->json($currency);
    }

    // Update the specified currency in the database
    public function update(Request $request, $id)
    {
        // Validate the incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|numeric',
            'symbol' => 'required|string|max:10',
            'row_id' => 'required|string|max:255|unique:currencies,row_id,' . $id,
        ]);

        // Find the currency and update it
        $currency = Currency::findOrFail($id);
        $currency->update([
            'name' => $request->name,
            'value' => $request->value,
            'symbol' => $request->symbol,
            'row_id' => $request->row_id,
            'updated_by' => auth()->id(), // Optional, if you want to track the updater
        ]);

        return response()->json($currency);
    }

    // Remove the specified currency from the database
    public function destroy($id)
    {
        $currency = Currency::findOrFail($id);
        $currency->delete();

        return response()->json(['message' => 'Currency deleted successfully']);
    }
}
