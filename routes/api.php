<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MoleculeController;
use App\Http\Controllers\IndicationController;
use App\Http\Controllers\PlacementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PackagingController;

// Routes pour les Articles
Route::apiResource('articles', ArticleController::class);

// Routes pour les devises (Currencies)
Route::apiResource('currencies', CurrencyController::class);

// Routes pour les Catégories
Route::apiResource('categories', CategoryController::class);

// Routes pour les Molécules
Route::apiResource('molecules', MoleculeController::class);

// Routes pour les Indications
Route::apiResource('indications', IndicationController::class);

// Routes pour les Placements
Route::apiResource('placements', PlacementController::class);

// Routes pour les Fournisseurs (Suppliers)
Route::apiResource('suppliers', SupplierController::class);

// Routes pour les Emballages (Packagings)
Route::apiResource('packagings', PackagingController::class);

// Exemple de route personnalisée pour un article spécifique
Route::get('articles/{id}', [ArticleController::class, 'show']);
Route::put('articles/{id}', [ArticleController::class, 'update']);
Route::delete('articles/{id}', [ArticleController::class, 'destroy']);

// Routes pour attacher des relations Many-to-Many dans Article
Route::post('articles/{article}/molecules', [ArticleController::class, 'addMolecules']);
Route::post('articles/{article}/indications', [ArticleController::class, 'addIndications']);
Route::post('articles/{article}/placements', [ArticleController::class, 'addPlacements']);
Route::post('articles/{article}/suppliers', [ArticleController::class, 'addSuppliers']);

// Routes pour détacher des relations Many-to-Many dans Article
Route::delete('articles/{article}/molecules/{molecule}', [ArticleController::class, 'removeMolecule']);
Route::delete('articles/{article}/indications/{indication}', [ArticleController::class, 'removeIndication']);
Route::delete('articles/{article}/placements/{placement}', [ArticleController::class, 'removePlacement']);
Route::delete('articles/{article}/suppliers/{supplier}', [ArticleController::class, 'removeSupplier']);
