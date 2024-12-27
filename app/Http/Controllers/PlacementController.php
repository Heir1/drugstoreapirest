<?php

namespace App\Http\Controllers;

use App\Models\Placement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class PlacementController extends Controller
{
    /**
     * Display a listing of all placements.
     */
    public function index()
    { 
        try {
            $placements = Placement::with(['articles'])->get(); // Retrieve all placements
            return response()->json($placements, 200); // Return 200 OK with placements data
        } catch (\Exception $e) {
            Log::error('Error retrieving placements: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error retrieving placements',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Store a newly created placement in the database.
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

            // Create a new placement record
            $placement = Placement::create([
                'name' => $request->name,
                'row_id' => $row_id, // Generated UUID for row_id
                'created_by' => auth()->id() ?? null, // Assuming you are using authentication
            ]);

            return response()->json($placement, 201); // Return created placement with 201 status
        } catch (ValidationException $e) {
            Log::error('Validation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors(),
            ], 422); // Validation error
        } catch (\Exception $e) {
            Log::error('Error storing placement: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error storing placement',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Display the specified placement.
     */
    public function show($id)
    {
        try {
            $placement = Placement::findOrFail($id); // Find the placement by ID
            return response()->json($placement, 200); // Return placement data with 200 status
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Placement not found: ' . $e->getMessage());
            return response()->json([
                'error' => 'Placement not found',
                'message' => 'The placement with the given ID does not exist.',
            ], 404); // 404 Not Found
        } catch (\Exception $e) {
            Log::error('Error retrieving placement: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error retrieving placement',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Update the specified placement in the database.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate incoming data
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Find the placement by ID
            $placement = Placement::findOrFail($id);

            // Update the placement record
            $placement->update([
                'name' => $request->name,
                'updated_by' => auth()->id() ?? null, // Assuming you are using authentication
            ]);

            return response()->json($placement, 200); // Return updated placement data with 200 status
        } catch (ValidationException $e) {
            Log::error('Validation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors(),
            ], 422); // Validation error
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Placement not found: ' . $e->getMessage());
            return response()->json([
                'error' => 'Placement not found',
                'message' => 'The placement with the given ID does not exist.',
            ], 404); // 404 Not Found
        } catch (\Exception $e) {
            Log::error('Error updating placement: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error updating placement',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Remove the specified placement from the database.
     */
    public function destroy($id)
    {
        try {
            // Find the placement by ID
            $placement = Placement::findOrFail($id);

            // Delete the placement record
            $placement->delete();

            return response()->json([
                'message' => 'Placement deleted successfully',
            ], 200); // Return 200 OK with success message
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Placement not found: ' . $e->getMessage());
            return response()->json([
                'error' => 'Placement not found',
                'message' => 'The placement with the given ID does not exist.',
            ], 404); // 404 Not Found
        } catch (\Exception $e) {
            Log::error('Error deleting placement: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error deleting placement',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }
}
