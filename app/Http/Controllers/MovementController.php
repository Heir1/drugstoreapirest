<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

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
     * Get all movements with a specific movement type.
     */
    public function getMovementsByType($type, $firstrange, $secondrange)
    {
        // Retrieve movements with the specified type
        // $movements = Movement::whereHas('movementType', function ($query) use ($type) {
        //     $query->where('name', $type);
        // })->with(['movementType', 'article.placements', 'article.suppliers'])->get();

        // Récupérer la date du jour
        // $today = Carbon::today();

        $firstrange = Carbon::parse($firstrange)->startOfDay();
        $secondrange = Carbon::parse($secondrange)->endOfDay();

        
        // return response()->json($secondrange, 200);

        // $movements = Movement::whereDate('created_at', $firstrange)->with(['movementType', 'article.placements', 'article.suppliers'])->where("movement_type_id",$type)->get();

        $movements = Movement::whereBetween('created_at', [$firstrange, $secondrange])->with(['movementType', 'article.placements', 'article.suppliers'])->where('movement_type_id', $type)->get();

        if ($movements->isEmpty()) {
            return response()->json(['message' => 'No movements found for this type.'], 404);
        }

        return response()->json($movements, 200);
    }

    /**
     * Créer un nouveau mouvement.
     */
    public function store(Request $request)
    {

        try {
            //code...
                $validated = $request->validate([
                    'article_id' => 'required|exists:articles,id',
                    'quantity' => 'required|integer',
                    'movement_type_id' => 'required|exists:movement_types,id',
                    'reference' => 'nullable|string|max:100',
                    'purchase_price' => 'required|integer',
                    'selling_price' => 'required|integer',
                    'expiration_date' => 'required|string',
                ]);
        
                // $movement = Movement::create($validated);
        
                $article = Article::find($validated['article_id']);
        
        
                if($article->purchase_price !==  $validated['purchase_price']){
                    $article->purchase_price = $validated['purchase_price'];
                    $article->selling_price = $validated['selling_price']; 
                }

                if($article->expiration_date !==  $validated['expiration_date']){
                    $article->expiration_date = $validated['expiration_date'];
                }
        
                $movement = new Movement();
                $movement->article_id = $validated['article_id'];
                $movement->quantity = $validated['quantity'];
                $movement->movement_type_id = $validated['movement_type_id'];
                $movement->reference = $validated['reference'];
                $movement->old_article_stock = $article->quantity;
        
                $movement->save();
        
                // Mettre à jour la quantité en stock
                $movementType = $movement->movementType->name;
        
                if ($movementType === 'Entree') {
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
            } catch (ValidationException $e) {
                // Si une erreur de validation se produit, on renvoie une réponse avec le message d'erreur
                return response()->json([
                    'error' => $e->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY); // Code 422 pour une erreur de validation
            } catch (\Exception $e) {
                // Gérer les erreurs générales et renvoyer une réponse appropriée
                return response()->json([
                    'error' => 'Une erreur interne est survenue. Veuillez réessayer plus tard.',
                    'message' => $e->getMessage()
                ], Response::HTTP_INTERNAL_SERVER_ERROR); // Code 500 pour une erreur serveur interne
            }
        
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
