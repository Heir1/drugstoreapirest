<?php

namespace App\Http\Controllers;

use App\Models\Molecule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class MoleculeController extends Controller
{
    /**
     * Display a listing of molecules.
     */
    public function index()
    {
        try {

            $molecules = Molecule::with(['articles'])->get(); // Retrieve all molecules
            return response()->json($molecules, 200); // Return 200 OK with molecules data

        } catch (\Exception $e) {
            Log::error('Error retrieving molecules: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error retrieving molecules',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Store a newly created molecule in the database.
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

            // Create a new molecule record
            $molecule = Molecule::create([
                'name' => $request->name,
                'row_id' => $row_id, // Generated UUID for row_id
                'created_by' => auth()->id() ?? null, // Assuming you are using authentication
            ]);

            return response()->json($molecule, 201); // Return created molecule with 201 status
        } catch (ValidationException $e) {
            Log::error('Validation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors(),
            ], 422); // Validation error
        } catch (\Exception $e) {
            Log::error('Error storing molecule: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error storing molecule',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Display the specified molecule.
     */
    public function show($id)
    {
        try {
            $molecule = Molecule::findOrFail($id); // Find the molecule by ID
            return response()->json($molecule, 200); // Return molecule data with 200 status
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Molecule not found: ' . $e->getMessage());
            return response()->json([
                'error' => 'Molecule not found',
                'message' => 'The molecule with the given ID does not exist.',
            ], 404); // 404 Not Found
        } catch (\Exception $e) {
            Log::error('Error retrieving molecule: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error retrieving molecule',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Update the specified molecule in the database.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate incoming data
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Find the molecule by ID
            $molecule = Molecule::findOrFail($id);

            // Update the molecule record
            $molecule->update([
                'name' => $request->name,
                'updated_by' => auth()->id() ?? null, // Assuming you are using authentication
            ]);

            return response()->json($molecule, 200); // Return updated molecule data with 200 status
        } catch (ValidationException $e) {
            Log::error('Validation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors(),
            ], 422); // Validation error
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Molecule not found: ' . $e->getMessage());
            return response()->json([
                'error' => 'Molecule not found',
                'message' => 'The molecule with the given ID does not exist.',
            ], 404); // 404 Not Found
        } catch (\Exception $e) {
            Log::error('Error updating molecule: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error updating molecule',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Remove the specified molecule from the database.
     */
    public function destroy($id)
    {
        try {
            // Find the molecule by ID
            $molecule = Molecule::findOrFail($id);

            // Delete the molecule record
            $molecule->delete();

            return response()->json([
                'message' => 'Molecule deleted successfully',
            ], 200); // Return 200 OK with success message
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Molecule not found: ' . $e->getMessage());
            return response()->json([
                'error' => 'Molecule not found',
                'message' => 'The molecule with the given ID does not exist.',
            ], 404); // 404 Not Found
        } catch (\Exception $e) {
            Log::error('Error deleting molecule: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error deleting molecule',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }
}
