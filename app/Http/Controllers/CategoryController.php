<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;  // Import for generating UUID
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $categories = Category::all(); // Get all categories
            return response()->json($categories, 200); // Return categories as JSON
        } catch (\Exception $e) {
            Log::error('Error fetching categories: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error fetching categories',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Store a newly created category in the database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate incoming data
            $request->validate([
                'name' => 'required|string|max:255',
                // 'row_id' is not required here because it will be generated
            ]);

            // Generate a UUID for the 'id' and 'row_id'
            // $id = (string) Str::uuid();  // Generate UUID for the primary key
            $row_id = (string) Str::uuid();  // Generate UUID for row_id

            // Create a new category record
            $category = Category::create([
                // 'id' => $id,           // Provide the generated UUID for 'id'
                'name' => $request->name,
                'row_id' => $row_id,   // Generated UUID for row_id
                'created_by' => auth()->id() ?? null, // Assuming you are using authentication
            ]);

            return response()->json($category, 201); // Created
        } catch (ValidationException $e) {
            Log::error('Validation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors(),
            ], 422); // Validation error
        } catch (\Exception $e) {
            Log::error('Error storing category: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error storing category',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }
    /**
     * Display the specified category.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $category = Category::findOrFail($id); // Find category by ID

            return response()->json($category, 200); // Return the category
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Category not found',
                'message' => 'The category with the provided ID was not found.',
            ], 404); // Not Found
        } catch (\Exception $e) {
            Log::error('Error fetching category: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error fetching category',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Update the specified category in the database.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        try {
            // Validate incoming data
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Generate a UUID for row_id if it's not passed
            $row_id = $request->has('row_id') ? $request->row_id : (string) Str::uuid();

            // Find and update the category
            $category = Category::findOrFail($id);
            $category->update([
                'name' => $request->name,
                'updated_by' => auth()->id() ?? null, // Assuming you are using authentication
            ]);

            return response()->json($category, 200); // Updated
        } catch (ValidationException $e) {
            Log::error('Validation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors(),
            ], 422); // Validation error
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Category not found',
                'message' => 'The category with the provided ID was not found.',
            ], 404); // Not Found
        } catch (\Exception $e) {
            Log::error('Error updating category: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error updating category',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Remove the specified category from the database.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $category = Category::findOrFail($id); // Find category by ID
            $category->delete(); // Delete the category

            return response()->json([
                'message' => 'Category deleted successfully',
            ], 200); // Success
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Category not found',
                'message' => 'The category with the provided ID was not found.',
            ], 404); // Not Found
        } catch (\Exception $e) {
            Log::error('Error deleting category: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error deleting category',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }
}
