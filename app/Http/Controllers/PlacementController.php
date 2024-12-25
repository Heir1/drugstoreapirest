<?php

namespace App\Http\Controllers;

use App\Models\Placement;
use Illuminate\Http\Request;

class PlacementController extends Controller
{
    // Display a listing of the placements
    public function index()
    {
        $placements = Placement::all(); // You can add pagination if needed
        return response()->json($placements);
    }

    // Store a newly created placement in the database
    public function store(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'row_id' => 'required|string|max:255|unique:placements',
        ]);

        // Create a new placement record
        $placement = Placement::create([
            'name' => $request->name,
            'row_id' => $request->row_id,
            'created_by' => auth()->id(), // Optional, if you want to track who created the record
        ]);

        return response()->json($placement, 201); // Return 201 Created status
    }

    // Display the specified placement
    public function show($id)
    {
        $placement = Placement::findOrFail($id); // Will return 404 if not found
        return response()->json($placement);
    }

    // Update the specified placement in the database
    public function update(Request $request, $id)
    {
        // Validate the incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'row_id' => 'required|string|max:255|unique:placements,row_id,' . $id,
        ]);

        // Find the placement and update it
        $placement = Placement::findOrFail($id);
        $placement->update([
            'name' => $request->name,
            'row_id' => $request->row_id,
            'updated_by' => auth()->id(), // Optional, if you want to track who updated the record
        ]);

        return response()->json($placement);
    }

    // Remove the specified placement from the database
    public function destroy($id)
    {
        $placement = Placement::findOrFail($id);
        $placement->delete();

        return response()->json(['message' => 'Placement deleted successfully']);
    }
}
