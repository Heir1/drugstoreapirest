<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Molecule;

class MoleculeController extends Controller
{
    // Display a listing of the molecules
    public function index()
    {
        $molecules = Molecule::all(); // You can add pagination if needed
        return response()->json($molecules);
    }

    // Store a newly created molecule in the database
    public function store(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'row_id' => 'required|string|max:255|unique:molecules',
        ]);

        // Create a new molecule record
        $molecule = Molecule::create([
            'name' => $request->name,
            'row_id' => $request->row_id,
            'created_by' => auth()->id(), // Optional, if you want to track who created the record
        ]);

        return response()->json($molecule, 201); // Return 201 Created status
    }

    // Display the specified molecule
    public function show($id)
    {
        $molecule = Molecule::findOrFail($id); // Will return 404 if not found
        return response()->json($molecule);
    }

    // Update the specified molecule in the database
    public function update(Request $request, $id)
    {
        // Validate the incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'row_id' => 'required|string|max:255|unique:molecules,row_id,' . $id,
        ]);

        // Find the molecule and update it
        $molecule = Molecule::findOrFail($id);
        $molecule->update([
            'name' => $request->name,
            'row_id' => $request->row_id,
            'updated_by' => auth()->id(), // Optional, if you want to track who updated the record
        ]);

        return response()->json($molecule);
    }

    // Remove the specified molecule from the database
    public function destroy($id)
    {
        $molecule = Molecule::findOrFail($id);
        $molecule->delete();

        return response()->json(['message' => 'Molecule deleted successfully']);
    }
}
