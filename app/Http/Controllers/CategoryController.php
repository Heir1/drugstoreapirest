<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    // Display a listing of the categories
    public function index()
    {
        $categories = Category::all();  // You can paginate if needed
        return response()->json($categories);
    }

    // Store a newly created category in the database
    public function store(Request $request)
    {
        // Validate incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'row_id' => 'required|string|max:255|unique:categories',
        ]);

        // Create a new category record
        $category = Category::create([
            'name' => $request->name,
            'row_id' => $request->row_id,
            'created_by' => auth()->id(), // Optional, if you track who created the record
        ]);

        return response()->json($category, 201);  // 201 Created
    }

    // Display the specified category
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    // Update the specified category in the database
    public function update(Request $request, $id)
    {
        // Validate incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'row_id' => 'required|string|max:255|unique:categories,row_id,' . $id,
        ]);

        // Find the category and update it
        $category = Category::findOrFail($id);
        $category->update([
            'name' => $request->name,
            'row_id' => $request->row_id,
            'updated_by' => auth()->id(),  // Optional, if you track who updated the record
        ]);

        return response()->json($category);
    }

    // Remove the specified category from the database
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
