<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\MovementType;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Invoice;


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
            // return response()->json(['message' => 'No movements found for this type.'], 404);
            return response()->json([], 200);
        }

        return response()->json($movements, 200);
    }

    /**
     * Créer un nouveau mouvement.
     */
    public function store(Request $request)
    {

        DB::beginTransaction();

        try {
                //code...
                $validated = $request->validate([
                    'movement_type_id' => 'required|exists:movement_types,id',
                    'articles' => 'required|array',
                    'created_by' => 'nullable'
                ]);

                $lastMovement = null;

                foreach ($validated['articles'] as $articleData) {

                    $newUuid = Str::uuid();
                    $article = Article::find($articleData['id']);
    
            
                    if (!$article) {
                        return response()->json([
                            'error' => "L'article avec l'ID {$articleData['id']} n'existe pas."
                        ], 404);
                    }
                    
            
                    // Mise à jour des prix et de la date d'expiration
                    if ($article->purchase_price !== $articleData['purchase_price']) {
                        $article->purchase_price = $articleData['purchase_price'];
                        $article->selling_price = $articleData['selling_price'];
                    }
    
                    if ($article->expiration_date !== $articleData['expirationDate']) {
                        $article->expiration_date = $articleData['expirationDate'];
                    }
    
                    // Création du mouvement
                    $movement = new Movement();
                    $movement->article_id = $article->id;
                    $movement->quantity = $articleData['quantityappro'];
                    $movement->movement_type_id = $validated['movement_type_id'];
                    $movement->reference = "REF-" . $newUuid;
                    $movement->old_article_stock = $article->quantity;
                    $movement->created_by = $validated['created_by'];
                    $movement->save();
            
                    // // Mise à jour du stock selon le type de mouvement
                    $movementType = MovementType::find($validated['movement_type_id']);
    
                    if ($movementType) {
                        if ($movementType->name === 'Entree') {
                            $article->quantity += $articleData['quantityappro'];
                        } elseif ($movementType->name === 'Sortie') {
                            if ($article->quantity < $articleData['quantityappro']) {
                                return response()->json([
                                    'error' => 'La quantité saisie est supérieure au stock disponible.'
                                ], 400);
                            }
                            $article->quantity -= $articleData['quantityappro'];
                        } elseif ($movementType->name === 'Ajustment') {
                            $article->quantity = $articleData['quantityappro'];
                        }
                    }
    
                    $article->save();

                    // Stocker le dernier mouvement
                    $lastMovement = $movement;

                }
                
                DB::commit();

                if ($lastMovement) {
                    $lastMovement->load(['article.placements', 'article.suppliers']);
                    return response()->json($lastMovement, Response::HTTP_CREATED);
                }
        
                return response()->json(['message' => 'Aucun mouvement enregistré.'], 400);

            } catch (ValidationException $e) {
                DB::rollBack();
                // Si une erreur de validation se produit, on renvoie une réponse avec le message d'erreur
                return response()->json([
                    'error' => $e->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY); // Code 422 pour une erreur de validation
            } catch (\Exception $e) {
                DB::rollBack();
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
            'updated_by' => 'nullable'
            // 'reference' => 'nullable|string|max:100',
        ]);

        // Réinitialiser le stock avant mise à jour
        $article = Article::find($movement->article_id);
        $oldQuantity = $movement->quantity;

        // if ($oldMovementType === 1) {
        $article->quantity -= $oldQuantity;
        $article->update();
        // } 

        // Mettre à jour le mouvement
        $movement->update($validated);

        // // Recalculer le stock après mise à jour
        $article = Article::find($movement->article_id);
        $newQuantity = $movement->quantity;
        $newMovementType = $movement->movementType->id;

        // return $movement;

        // if ($newMovementType === 1) {
        $article->quantity += $newQuantity;
        // }       

        $article->save();

        $movement->load(['article.placements', 'article.suppliers']);

        // 'article.placements', 'article.suppliers

        return response()->json( $movement, Response::HTTP_OK);

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
        // $movementType = $movement->movementType->id;

        // if ($movementType === 1) {

        $article->quantity -= $movement->quantity;

        // } elseif ($movementType === 2) {
        //     $article->quantity += $movement->quantity;
        // }

        $article->save();

        $movement->delete();

        return response()->json(['message' => 'Movement deleted successfully.'], 200);
    }
}
