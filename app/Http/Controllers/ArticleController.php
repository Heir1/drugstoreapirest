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
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    /**
     * Afficher la liste des articles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
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

    /**
     * Afficher un article spécifique.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Article $article)
    {
        // Charger les relations many-to-many avec les autres modèles
        $article->load(['currency', 'category', 'packaging', 'placements', 'molecules', 'suppliers', 'indications']);
        return response()->json($article);
    }

    /**
     * Enregistrer un nouvel article.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    
     public function store(Request $request)
     {
         DB::beginTransaction(); // Démarrer la transaction
         try {
             // Validation des données d'entrée
             $validated = $request->validate([
                 'barcode' => 'required|string',
                 'description' => 'required|string',
                 'quantity' => 'required|integer',
                 'purchase_price' => 'required|numeric',
                 'selling_price' => 'required|numeric',
                 'currency_id' => 'nullable|exists:currencies,id',
                 'category_id' => 'nullable',
                 'packaging_id' => 'nullable',
                 'alert' => 'nullable|integer',
                 'expiration_date' => 'nullable|date',
                 'comment' => 'nullable|string',
                 'placements' => 'nullable',
                 'molecules' => 'nullable',
                 'suppliers' => 'nullable',
                 'indications' => 'nullable'
             ]);
     
             // Vérification de l'existence d'un article avec le même barcode ou description
             if (Article::where('barcode', $validated['barcode'])->exists() ||
                 Article::where('description', $validated['description'])->exists()) {
                 return response()->json([
                     'message' => 'Ce code-barre ou cette description existe déjà.'
                 ], Response::HTTP_CONFLICT);
             }
     
             // Création ou récupération de la catégorie
             $category_id = is_numeric($validated['category_id'])
                 ? $validated['category_id']
                 : Category::create(['name' => $validated['category_id'], 'row_id' => Str::uuid(), 'created_by' => auth()->id()])->id;
     
             // Création ou récupération du packaging
             $packaging_id = is_numeric($validated['packaging_id'])
                 ? $validated['packaging_id']
                 : Packaging::create(['name' => $validated['packaging_id'], 'row_id' => Str::uuid(), 'created_by' => auth()->id()])->id;
     
             // Création de l'article
             $article = Article::create(array_merge($validated, [
                 'category_id' => $category_id,
                 'packaging_id' => $packaging_id,
                 'row_id' => Str::uuid(),
                 'created_by' => auth()->id(),
             ]));
     
             // Enregistrement du mouvement si la quantité est > 0
             if ($validated['quantity'] > 0) {
                 Movement::create([
                     'article_id' => $article->id,
                     'quantity' => $validated['quantity'],
                     'movement_type_id' => 1,
                     'movement_date' => now(),
                     'reference' => Str::uuid(),
                     'old_article_stock' => 0,
                 ]);
             }
     
                // Traitement des relations many-to-many (placements, molecules, suppliers, indications)
                $relations = ['placements', 'molecules', 'suppliers', 'indications'];
                foreach ($relations as $relation) {
                    if (!empty($validated[$relation])) {
                        if (is_array($validated[$relation])) {
                            $article->$relation()->attach($validated[$relation]);
                        } elseif (is_string($validated[$relation]) || is_numeric($validated[$relation])) {
                            // Suppression du "s" en passant le nom en singulier
                            $modelClass = "App\\Models\\" . ucfirst(Str::singular($relation));

                            $newEntry = app($modelClass)::create([
                                'name' => $validated[$relation],
                                'row_id' => Str::uuid(),
                                'created_by' => auth()->id(),
                            ]);

                            $article->$relation()->attach($newEntry->id);
                        } else {
                            return back()->withErrors([$relation => "Le format de $relation est invalide."]);
                        }
                    }
                }

     
             // Charger les relations
             $article->load(['currency', 'category', 'packaging', 'placements', 'molecules', 'suppliers', 'indications']);
     
             DB::commit(); // Valider la transaction
             return response()->json($article, Response::HTTP_CREATED);
         } catch (ValidationException $e) {
             DB::rollBack(); // Annuler la transaction en cas d'erreur
             return response()->json(['error' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
         } catch (\Exception $e) {
             DB::rollBack(); // Annuler la transaction en cas d'erreur
             return response()->json([
                 'error' => 'Une erreur interne est survenue.',
                 'message' => $e->getMessage()
             ], Response::HTTP_INTERNAL_SERVER_ERROR);
         }
     }
     

    /**
     * Mettre à jour un article.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Article $article)
    {

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


            // Vérification de l'existence d'un article avec le même barcode
            // if (Article::where('barcode', $validated['barcode'])->exists()) {
            //     return response()->json([
            //         'error' => 'Un article avec ce code-barres existe déjà.'
            //     ], Response::HTTP_CONFLICT); // Code 409 pour conflit
            // }

            // Vérification de l'existence d'un article avec la même description
            // if (Article::where('description', $validated['description'])->exists()) {
            //     return response()->json([
            //         'error' => 'Un article avec cette description existe déjà.'
            //     ], Response::HTTP_CONFLICT); // Code 409 pour conflit
            // }

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

    /**
     * Supprimer un article.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Article $article)
    {
        // Supprimer l'article
        $article->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
