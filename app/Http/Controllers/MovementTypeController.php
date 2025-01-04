<?php

namespace App\Http\Controllers;

use App\Models\MovementType;
use Illuminate\Http\Request;

class MovementTypeController extends Controller
{
    /**
     * Lister tous les types de mouvements.
     */
    public function index()
    {
        $movementTypes = MovementType::all();

        return response()->json($movementTypes, 200);
    }

    /**
     * Créer un nouveau type de mouvement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:movement_types|max:50',
            'description' => 'nullable|string',
        ]);

        $movementType = MovementType::create($validated);

        return response()->json([
            'message' => 'Movement type created successfully.',
            'movement_type' => $movementType,
        ], 201);
    }

    /**
     * Afficher un type de mouvement spécifique.
     */
    public function show($id)
    {
        $movementType = MovementType::find($id);

        if (!$movementType) {
            return response()->json(['error' => 'Movement type not found.'], 404);
        }

        return response()->json($movementType, 200);
    }

    /**
     * Mettre à jour un type de mouvement existant.
     */
    public function update(Request $request, $id)
    {
        $movementType = MovementType::find($id);

        if (!$movementType) {
            return response()->json(['error' => 'Movement type not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:movement_types,name,' . $id . '|max:50',
            'description' => 'nullable|string',
        ]);

        $movementType->update($validated);

        return response()->json([
            'message' => 'Movement type updated successfully.',
            'movement_type' => $movementType,
        ], 200);
    }

    /**
     * Supprimer un type de mouvement.
     */
    public function destroy($id)
    {
        $movementType = MovementType::find($id);

        if (!$movementType) {
            return response()->json(['error' => 'Movement type not found.'], 404);
        }

        // Vérifier si ce type de mouvement est utilisé
        if ($movementType->movements()->count() > 0) {
            return response()->json([
                'error' => 'Cannot delete movement type because it is associated with movements.'
            ], 400);
        }

        $movementType->delete();

        return response()->json(['message' => 'Movement type deleted successfully.'], 200);
    }
}