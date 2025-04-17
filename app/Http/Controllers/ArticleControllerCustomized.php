<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Currency;
use App\Models\Category;
use App\Models\Packaging;
use App\Models\Placement;
use App\Models\Molecule;
use App\Models\Supplier;
use App\Models\Movement;
use App\Models\Indication;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Carbon\Carbon;

class ArticleControllerCustomized extends Controller
{
    //
    public function getAllArticles()
    {
        // Charger les relations many-to-many avec les autres modèles
        $articles = Article::with(['currency', 'category', 'packaging', 'placements', 'molecules', 'suppliers', 'indications'])->get();

        $articles->each(function ($article) {
            $article->placements->makeHidden('pivot');
            $article->molecules->makeHidden('pivot');
            $article->suppliers->makeHidden('pivot');
            $article->indications->makeHidden('pivot');
        });

        return response()->json($articles);
    }

    public function updateArticle(Request $request, $id){

        return "Still true";
        
        try {

            // Validation des données d'entrée
            $validated = $request->validate([
                'barcode' => 'required|string',
                'description' => 'required|string',
                'quantity' => 'required|integer',
                'purchase_price' => 'required|numeric',
                'selling_price' => 'required|numeric',
                'currency_id' => 'nullable|exists:currencies,id',
                'category_id' => 'nullable|exists:categories,id',
                'packaging_id' => 'nullable|exists:packagings,id',
                'alert' => 'nullable|integer',
                'is_active' => 'nullable|boolean',
                'expiration_date' => 'nullable|date',
                'comment' => 'nullable|string',
                'placements' => 'nullable|array',
                'molecules' => 'nullable|array',
                'suppliers' => 'nullable|array',
                'indications' => 'nullable|array',
            ]);

            $article = Article::find($id);

            // Mettre à jour l'article
            $article->update([
                'barcode' => $validated['barcode'],
                'description' => $validated['description'],
                'quantity' => $validated['quantity'],
                'purchase_price' => $validated['purchase_price'],
                'selling_price' => $validated['selling_price'],
                'currency_id' => $validated['currency_id'],
                'category_id' => $validated['category_id'],
                'packaging_id' => $validated['packaging_id'],
                'alert' => $validated['alert'],
                'is_active' =>  $validated['is_active'],
                'expiration_date' => $validated['expiration_date'],
                'comment' => $validated['comment'],
                'updated_by' => auth()->user()->id ?? null,
            ]);

            return "Still true";

            // Synchroniser les placements (relation many-to-many)
            if (isset($validated['placements'])) {
                $article->placements()->sync($validated['placements']);
            }

            // Synchroniser les molécules (relation many-to-many)
            if (isset($validated['molecules'])) {
                $article->molecules()->sync($validated['molecules']);
            }

            // Synchroniser les suppliers (relation many-to-many)
            if (isset($validated['suppliers'])) {
                $article->suppliers()->sync($validated['suppliers']);
            }

            // Synchroniser les indications (relation many-to-many)
            if (isset($validated['indications'])) {
                $article->indications()->sync($validated['indications']);
            }

            // Charger les relations many-to-many avec les autres modèles
            $article->load(['currency', 'category', 'packaging', 'placements', 'molecules', 'suppliers', 'indications']);

            return response()->json($article, Response::HTTP_OK);

        } catch (ValidationException $e) {
            // Si une erreur de validation se produit, on renvoie une réponse avec le message d'erreur
            return response()->json([
                'error' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // Code 422 pour une erreur de validation
        } catch (\Exception $e) {
            // Gérer les erreurs générales et renvoyer une réponse appropriée
            return response()->json([
                'error' => 'Une erreur interne est survenue. Veuillez réessayer plus tard.',
                'message' => $e->getMessage(),
                'article' => $article
            ], Response::HTTP_INTERNAL_SERVER_ERROR); // Code 500 pour une erreur serveur interne
        }
    }


    public function getLowStockArticles($firstrange, $secondrange)
    {
        // Vérifier que les dates sont valides
        if (!$firstrange || !$secondrange) {
            return response()->json(['message' => 'Les dates de la plage sont invalides'], Response::HTTP_BAD_REQUEST);
        }

        // Récupérer les articles avec un stock inférieur ou égal au seuil d'alerte
        // et dont la date de mise à jour est dans la plage spécifiée (inclusive)
        $lowStockArticles = Article::whereColumn('quantity', '<=', 'alert')
            ->whereBetween('updated_at', [$firstrange, $secondrange]) // Inclus $firstrange et $secondrange
            ->with(['currency', 'category', 'packaging', 'placements', 'molecules', 'suppliers', 'indications'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Vérifier si des articles ont été trouvés
        if ($lowStockArticles->isEmpty()) {
            return response()->json([], Response::HTTP_OK);
        }

        return response()->json($lowStockArticles, Response::HTTP_OK);
    }

    public function getExpiredArticles($firstrange, $secondrange)
    {
        // Vérifier que les dates sont valides
        if (!$firstrange || !$secondrange) {
            return response()->json(['message' => 'Les dates de la plage sont invalides'], Response::HTTP_BAD_REQUEST);
        }

        // Récupérer les articles dont la date d'expiration est dans la plage spécifiée (inclusive)
        $expiredArticles = Article::whereBetween('expiration_date', [$firstrange, $secondrange])
            ->with(['currency', 'category', 'packaging', 'placements', 'molecules', 'suppliers', 'indications'])
            ->orderBy('expiration_date', 'asc')
            ->get();

        // Vérifier si des articles ont été trouvés
        if ($expiredArticles->isEmpty()) {
            return response()->json([], Response::HTTP_OK);
        }

        return response()->json($expiredArticles, Response::HTTP_OK);
    }

}