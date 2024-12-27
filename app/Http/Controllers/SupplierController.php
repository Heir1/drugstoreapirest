<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     */
    public function index()
    {
        try {
            $suppliers = Supplier::with(['articles'])->get(); // Retrieve all placements
            return response()->json($suppliers, 200); // Return 200 OK with suppliers data
        } catch (\Exception $e) {
            Log::error('Error retrieving suppliers: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error retrieving suppliers',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created supplier in the database.
     */
    public function store(Request $request)
    {
        try {
            // Validate incoming data
            $request->validate([
                'name' => 'required|string|max:255', // Validate the name field
            ]);

            // Generate a UUID for 'row_id'
            $row_id = (string) Str::uuid();  // Generate UUID for row_id

            // Create a new supplier record
            $supplier = Supplier::create([
                'name' => $request->name,
                'row_id' => $row_id, // Generated UUID for row_id
                'created_by' => auth()->id() ?? null, // Assuming you are using authentication
            ]);

            return response()->json($supplier, 201); // Return created supplier with 201 status
        } catch (ValidationException $e) {
            Log::error('Validation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors(),
            ], 422); // Validation error
        } catch (\Exception $e) {
            Log::error('Error storing supplier: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error storing supplier',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Display the specified supplier.
     */
    public function show($id)
    {
        try {
            $supplier = Supplier::findOrFail($id); // Find the supplier by ID
            return response()->json($supplier, 200); // Return supplier data with 200 status
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Supplier not found: ' . $e->getMessage());
            return response()->json([
                'error' => 'Supplier not found',
                'message' => 'The supplier with the given ID does not exist.',
            ], 404); // 404 Not Found
        } catch (\Exception $e) {
            Log::error('Error retrieving supplier: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error retrieving supplier',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Update the specified supplier in the database.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate incoming data
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Find the supplier by ID
            $supplier = Supplier::findOrFail($id);

            // Update the supplier record
            $supplier->update([
                'name' => $request->name,
                'updated_by' => auth()->id() ?? null, // Assuming you are using authentication
            ]);

            return response()->json($supplier, 200); // Return updated supplier data with 200 status
        } catch (ValidationException $e) {
            Log::error('Validation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors(),
            ], 422); // Validation error
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Supplier not found: ' . $e->getMessage());
            return response()->json([
                'error' => 'Supplier not found',
                'message' => 'The supplier with the given ID does not exist.',
            ], 404); // 404 Not Found
        } catch (\Exception $e) {
            Log::error('Error updating supplier: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error updating supplier',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Remove the specified supplier from the database.
     */
    public function destroy($id)
    {
        try {
            // Find the supplier by ID
            $supplier = Supplier::findOrFail($id);

            // Delete the supplier record
            $supplier->delete();

            return response()->json([
                'message' => 'Supplier deleted successfully',
            ], 200); // Return 200 OK with success message
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Supplier not found: ' . $e->getMessage());
            return response()->json([
                'error' => 'Supplier not found',
                'message' => 'The supplier with the given ID does not exist.',
            ], 404); // 404 Not Found
        } catch (\Exception $e) {
            Log::error('Error deleting supplier: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error deleting supplier',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }
}
