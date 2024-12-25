<?php

namespace App\Http\Controllers;

use App\Models\Packaging;
use Illuminate\Http\Request;

class PackagingController extends Controller
{
    // Display a listing of the packaging
    public function index()
    {
        $packaging = Packaging::all(); // You can add pagination if needed
        return response()->json($packaging);
    }

    // Store a newly created packaging in the database
    public function store(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'row_id' => 'required|string|max:255|unique:packagings',
        ]);

        // Create a new packaging record
        $packaging = Packaging::create([
            'name' => $request->name,
            'row_id' => $request->row_id,
            'created_by' => auth()->id(), // Optional, if you want to track who created the record
        ]);

        return response()->json($packaging, 201); // Return 201 Created status
    }

    // Display the specified packaging
    public function show($id)
    {
        $packaging = Packaging::findOrFail($id); // Will return 404 if not found
        return response()->json($packaging);
    }

    // Update the specified packaging in the database
    public function update(Request $request, $id)
    {
        // Validate the incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'row_id' => 'required|string|max:255|unique:packagings,row_id,' . $id,
        ]);

        // Find the packaging and update it
        $packaging = Packaging::findOrFail($id);
        $packaging->update([
            'name' => $request->name,
            'row_id' => $request->row_id,
            'updated_by' => auth()->id(), // Optional, if you want to track who updated the record
        ]);

        return response()->json($packaging);
    }

    // Remove the specified packaging from the database
    public function destroy($id)
    {
        $packaging = Packaging::findOrFail($id);
        $packaging->delete();

        return response()->json(['message' => 'Packaging deleted successfully']);
    }
}
