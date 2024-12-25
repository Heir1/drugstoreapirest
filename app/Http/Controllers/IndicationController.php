<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndicationController extends Controller
{
    // Display a listing of the indications
    public function index()
    {
        $indications = Indication::all(); // You can add pagination if needed
        return response()->json($indications);
    }

    // Store a newly created indication in the database
    public function store(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'row_id' => 'required|string|max:255|unique:indications',
        ]);

        // Create a new indication record
        $indication = Indication::create([
            'name' => $request->name,
            'row_id' => $request->row_id,
            'created_by' => auth()->id(), // Optional, if you want to track who created the record
        ]);

        return response()->json($indication, 201); // Return 201 Created status
    }

    // Display the specified indication
    public function show($id)
    {
        $indication = Indication::findOrFail($id); // Will return 404 if not found
        return response()->json($indication);
    }

    // Update the specified indication in the database
    public function update(Request $request, $id)
    {
        // Validate the incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'row_id' => 'required|string|max:255|unique:indications,row_id,' . $id,
        ]);

        // Find the indication and update it
        $indication = Indication::findOrFail($id);
        $indication->update([
            'name' => $request->name,
            'row_id' => $request->row_id,
            'updated_by' => auth()->id(), // Optional, if you want to track who updated the record
        ]);

        return response()->json($indication);
    }

    // Remove the specified indication from the database
    public function destroy($id)
    {
        $indication = Indication::findOrFail($id);
        $indication->delete();

        return response()->json(['message' => 'Indication deleted successfully']);
    }
}
