<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MovementController extends Controller
{
    /**
     * Lister tous les mouvements.
     */
    public function index()
    {
        $movements = Movement::with(['movementType', 'article.placements', 'article.suppliers'])->get();

        return response()->json($movements, 200);
    }

    /**
     * Créer un nouveau mouvement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'quantity' => 'required|integer',
            'movement_type_id' => 'required|exists:movement_types,id',
            'movement_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
        ]);

        $movement = Movement::create($validated);

        // Mettre à jour la quantité en stock
        $article = Article::find($validated['article_id']);
        $movementType = $movement->movementType->name;

        if ($movementType === 'Entée') {
            $article->quantity += $validated['quantity'];
        } elseif ($movementType === 'Sortie') {
            if ($article->quantity < $validated['quantity']) {
                return response()->json([
                    'error' => 'La quantité est saisie est supérieure au stock disponible .'
                ], 400);
            }
            $article->quantity -= $validated['quantity'];
        } elseif ($movementType === 'Ajustment') {
            $article->quantity = $validated['quantity'];
        }

        $article->save();

        $movement->load(['article.placements', 'article.suppliers']);

        return response()->json($movement, Response::HTTP_CREATED);
        
    }

    /**
     * Afficher un mouvement spécifique.
     */
    public function show($id)
    {
        $movement = Movement::with(['movementType', 'article'])->find($id);

        if (!$movement) {
            return response()->json(['error' => 'Movement not found.'], 404);
        }

        return response()->json($movement, 200);
    }

    /**
     * Mettre à jour un mouvement existant.
     */
    public function update(Request $request, $id)
    {
        $movement = Movement::find($id);

        if (!$movement) {
            return response()->json(['error' => 'Movement not found.'], 404);
        }

        $validated = $request->validate([
            'article_id' => 'nullable|exists:articles,id',
            'quantity' => 'nullable|integer',
            'movement_type_id' => 'nullable|exists:movement_types,id',
            'movement_date' => 'nullable|date',
            'reference' => 'nullable|string|max:100',
        ]);


        // Réinitialiser le stock avant mise à jour
        $article = Article::find($movement->article_id);
        $oldQuantity = $movement->quantity;
        $oldMovementType = $movement->movementType->name;

        if ($oldMovementType === 'Entée') {
            $article->quantity -= $oldQuantity;
        } elseif ($oldMovementType === 'Sortie') {
            $article->quantity += $oldQuantity;
        }

        // Mettre à jour le mouvement
        $movement->update($validated);

        // Recalculer le stock après mise à jour
        $article = Article::find($movement->article_id);
        $newQuantity = $movement->quantity;
        $newMovementType = $movement->movementType->name;

        if ($newMovementType === 'Entée') {
            $article->quantity += $newQuantity;
        } elseif ($newMovementType === 'Sortie') {
            if ($article->quantity < $newQuantity) {
                return response()->json([
                    'error' => 'Not enough stock for this operation.'
                ], 400);
            }
            $article->quantity -= $newQuantity;
        } elseif ($newMovementType === 'adjustment') {
            $article->quantity = $newQuantity;
        }

        $article->save();

        return response()->json([
            'message' => 'Movement updated successfully.',
            'movement' => $movement,
        ], 200);
    }

    /**
     * Supprimer un mouvement.
     */
    public function destroy($id)
    {
        $movement = Movement::find($id);

        if (!$movement) {
            return response()->json(['error' => 'Movement not found.'], 404);
        }

        // Réinitialiser le stock avant suppression
        $article = Article::find($movement->article_id);
        $movementType = $movement->movementType->name;

        if ($movementType === 'entry') {
            $article->quantity -= $movement->quantity;
        } elseif ($movementType === 'exit') {
            $article->quantity += $movement->quantity;
        }

        $article->save();

        $movement->delete();

        return response()->json(['message' => 'Movement deleted successfully.'], 200);
    }
}
