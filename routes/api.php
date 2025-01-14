<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleControllerCustomized;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MoleculeController;
use App\Http\Controllers\IndicationController;
use App\Http\Controllers\PlacementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PackagingController;
use App\Http\Controllers\MovementTypeController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\RateController;

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

// getAllArticles ArticleControllerCustomized
Route::get('articles', [ArticleControllerCustomized::class, 'getAllArticles']);
Route::put('articles/{id}', [ArticleControllerCustomized::class, 'updateArticle']);

// Exemple de route personnalisée pour un article spécifique
Route::get('articles/{id}', [ArticleController::class, 'show']);
// Route::put('articles/{id}', [ArticleController::class, 'update']);
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

// Routes pour les types de movements (movements)
Route::apiResource('movement-types', MovementTypeController::class);

// Routes pour les movements (movements)
Route::apiResource('movements', MovementController::class);
Route::get('movements/type/{type}', [MovementController::class, 'getMovementsByType']);

// MovementControllerCustomized
// Route::get('movements', [MovementControllerCustomized::class, 'getAllMovements']);
Route::put('articles/{id}', [ArticleControllerCustomized::class, 'updateArticle']);


// Routes la création de facture InvoiceController
Route::get('/invoices', [InvoiceController::class, 'getAllInvoices']);
Route::post('/invoices', [InvoiceController::class, 'createInvoice']);
Route::delete('/invoices/{id}', [InvoiceController::class, 'deleteInvoice']);



Route::apiResource('rates', RateController::class);
