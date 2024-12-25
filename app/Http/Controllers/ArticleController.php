<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Currency;
use App\Models\Category;
use App\Models\Molecule;
use App\Models\Indication;
use App\Models\Placement;
use App\Models\Supplier;
use App\Models\Packaging;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    // Affiche la liste des articles
    public function index()
    {
        $articles = Article::with([
            'currency', 
            'category', 
            'molecules', 
            'indications', 
            'placements', 
            'suppliers', 
            'packaging'
        ])->get(); // Inclure toutes les relations nécessaires avec `with()`
        
        return response()->json($articles);
    }

    // Crée un nouvel article
    public function store(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'alert' => 'nullable|string|max:255',
            'expiration_date' => 'nullable|date',
            'quantity' => 'required|integer',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'is_active' => 'required|boolean',
            'comment' => 'nullable|string',
            'currency_id' => 'required|exists:currencies,id',
            'category_id' => 'required|exists:categories,id',
            'packaging_id' => 'required|exists:packagings,id',
            'molecule_ids' => 'required|array',
            'molecule_ids.*' => 'exists:molecules,id',
            'indication_ids' => 'required|array',
            'indication_ids.*' => 'exists:indications,id',
            'placement_ids' => 'required|array',
            'placement_ids.*' => 'exists:placements,id',
            'supplier_ids' => 'required|array',
            'supplier_ids.*' => 'exists:suppliers,id',
        ]);

        // Créer l'article
        $article = Article::create([
            'barcode' => $request->barcode,
            'description' => $request->description,
            'alert' => $request->alert,
            'expiration_date' => $request->expiration_date,
            'quantity' => $request->quantity,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'is_active' => $request->is_active,
            'comment' => $request->comment,
            'currency_id' => $request->currency_id,
            'category_id' => $request->category_id,
            'packaging_id' => $request->packaging_id,
        ]);

        // Attacher les relations Many-to-Many
        $article->molecules()->attach($request->molecule_ids);
        $article->indications()->attach($request->indication_ids);
        $article->placements()->attach($request->placement_ids);
        $article->suppliers()->attach($request->supplier_ids);

        return response()->json($article, 201);
    }

    // Affiche les détails d'un article
    public function show($id)
    {
        $article = Article::with([
            'currency', 
            'category', 
            'molecules', 
            'indications', 
            'placements', 
            'suppliers', 
            'packaging'
        ])->findOrFail($id); // Inclure les relations

        return response()->json($article);
    }

    // Met à jour un article
    public function update(Request $request, $id)
    {
        $request->validate([
            'barcode' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'alert' => 'nullable|string|max:255',
            'expiration_date' => 'nullable|date',
            'quantity' => 'required|integer',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'is_active' => 'required|boolean',
            'comment' => 'nullable|string',
            'currency_id' => 'required|exists:currencies,id',
            'category_id' => 'required|exists:categories,id',
            'packaging_id' => 'required|exists:packagings,id',
            'molecule_ids' => 'required|array',
            'molecule_ids.*' => 'exists:molecules,id',
            'indication_ids' => 'required|array',
            'indication_ids.*' => 'exists:indications,id',
            'placement_ids' => 'required|array',
            'placement_ids.*' => 'exists:placements,id',
            'supplier_ids' => 'required|array',
            'supplier_ids.*' => 'exists:suppliers,id',
        ]);

        // Trouver et mettre à jour l'article
        $article = Article::findOrFail($id);
        $article->update([
            'barcode' => $request->barcode,
            'description' => $request->description,
            'alert' => $request->alert,
            'expiration_date' => $request->expiration_date,
            'quantity' => $request->quantity,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'is_active' => $request->is_active,
            'comment' => $request->comment,
            'currency_id' => $request->currency_id,
            'category_id' => $request->category_id,
            'packaging_id' => $request->packaging_id,
        ]);

        // Mettre à jour les relations Many-to-Many
        $article->molecules()->sync($request->molecule_ids);
        $article->indications()->sync($request->indication_ids);
        $article->placements()->sync($request->placement_ids);
        $article->suppliers()->sync($request->supplier_ids);

        return response()->json($article);
    }

    // Supprime un article
    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return response()->json(['message' => 'Article supprimé avec succès']);
    }
}
