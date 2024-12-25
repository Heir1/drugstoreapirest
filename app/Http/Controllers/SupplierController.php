<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // Display a listing of the suppliers
    public function index()
    {
        $suppliers = Supplier::all(); // You can add pagination if needed
        return response()->json($suppliers);
    }

    // Store a newly created supplier in the database
    public function store(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'row_id' => 'required|string|max:255|unique:suppliers',
        ]);

        // Create a new supplier record
        $supplier = Supplier::create([
            'name' => $request->name,
            'row_id' => $request->row_id,
            'created_by' => auth()->id(), // Optional, if you want to track who created the record
        ]);

        return response()->json($supplier, 201); // Return 201 Created status
    }

    // Display the specified supplier
    public function show($id)
    {
        $supplier = Supplier::findOrFail($id); // Will return 404 if not found
        return response()->json($supplier);
    }

    // Update the specified supplier in the database
    public function update(Request $request, $id)
    {
        // Validate the incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'row_id' => 'required|string|max:255|unique:suppliers,row_id,' . $id,
        ]);

        // Find the supplier and update it
        $supplier = Supplier::findOrFail($id);
        $supplier->update([
            'name' => $request->name,
            'row_id' => $request->row_id,
            'updated_by' => auth()->id(), // Optional, if you want to track who updated the record
        ]);

        return response()->json($supplier);
    }

    // Remove the specified supplier from the database
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully']);
    }
}
