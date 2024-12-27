<?php

namespace App\Http\Controllers;

use App\Models\Packaging;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class PackagingController extends Controller
{
    /**
     * Display a listing of all packagings.
     */
    public function index()
    {
        try {
            $packagings = Packaging::all(); // Retrieve all packagings
            return response()->json($packagings, 200); // Return 200 OK with packagings data
        } catch (\Exception $e) {
            Log::error('Error retrieving packagings: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error retrieving packagings',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Store a newly created packaging in the database.
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

            // Create a new packaging record
            $packaging = Packaging::create([
                'name' => $request->name,
                'row_id' => $row_id, // Generated UUID for row_id
                'created_by' => auth()->id() ?? null, // Assuming you are using authentication
            ]);

            return response()->json($packaging, 201); // Return created packaging with 201 status
        } catch (ValidationException $e) {
            Log::error('Validation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors(),
            ], 422); // Validation error
        } catch (\Exception $e) {
            Log::error('Error storing packaging: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error storing packaging',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Display the specified packaging.
     */
    public function show($id)
    {
        try {
            $packaging = Packaging::findOrFail($id); // Find the packaging by ID
            return response()->json($packaging, 200); // Return packaging data with 200 status
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Packaging not found: ' . $e->getMessage());
            return response()->json([
                'error' => 'Packaging not found',
                'message' => 'The packaging with the given ID does not exist.',
            ], 404); // 404 Not Found
        } catch (\Exception $e) {
            Log::error('Error retrieving packaging: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error retrieving packaging',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Update the specified packaging in the database.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate incoming data
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Find the packaging by ID
            $packaging = Packaging::findOrFail($id);

            // Update the packaging record
            $packaging->update([
                'name' => $request->name,
                'updated_by' => auth()->id() ?? null, // Assuming you are using authentication
            ]);

            return response()->json($packaging, 200); // Return updated packaging data with 200 status
        } catch (ValidationException $e) {
            Log::error('Validation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors(),
            ], 422); // Validation error
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Packaging not found: ' . $e->getMessage());
            return response()->json([
                'error' => 'Packaging not found',
                'message' => 'The packaging with the given ID does not exist.',
            ], 404); // 404 Not Found
        } catch (\Exception $e) {
            Log::error('Error updating packaging: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error updating packaging',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Remove the specified packaging from the database.
     */
    public function destroy($id)
    {
        try {
            // Find the packaging by ID
            $packaging = Packaging::findOrFail($id);

            // Delete the packaging record
            $packaging->delete();

            return response()->json([
                'message' => 'Packaging deleted successfully',
            ], 200); // Return 200 OK with success message
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Packaging not found: ' . $e->getMessage());
            return response()->json([
                'error' => 'Packaging not found',
                'message' => 'The packaging with the given ID does not exist.',
            ], 404); // 404 Not Found
        } catch (\Exception $e) {
            Log::error('Error deleting packaging: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error deleting packaging',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }
}
