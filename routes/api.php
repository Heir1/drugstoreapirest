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
use App\Http\Controllers\PaymentModeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashJournalController;

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
Route::get('lowstockarticles/{firstrange}/{secondrange}', [ArticleControllerCustomized::class, 'getLowStockArticles']);
Route::get('expirederticles/{firstrange}/{secondrange}', [ArticleControllerCustomized::class, 'getExpiredArticles']);
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
Route::get('movements/type/{type}/{firstrange}/{secondrange}', [MovementController::class, 'getMovementsByType']);

// MovementControllerCustomized
// Route::get('movements', [MovementControllerCustomized::class, 'getAllMovements']);
Route::put('articles/{id}', [ArticleControllerCustomized::class, 'updateArticle']);


// invoices/mode/${paymentModeId}/${firstrange}/${secondrange}

// Routes la création de facture InvoiceController
// Route::get('/invoices/mode/{paymentmodeid}/{firstrange}/{secondrange}', [InvoiceController::class, 'getAllInvoices']);
Route::get('/invoices/mode/{paymentmodeid}/{invoice}/{firstrange}/{secondrange}', [InvoiceController::class, 'getAllInvoices']);
Route::get('/invoicenumber', [InvoiceController::class, 'getInvoiceNumber']);
Route::post('/invoices', [InvoiceController::class, 'createInvoice']);
Route::put('/invoices/{id}', [InvoiceController::class, 'updateInvoice']);
Route::delete('/invoices/{id}', [InvoiceController::class, 'deleteInvoice']);


Route::apiResource('rates', RateController::class);


Route::resource('payment-modes', PaymentModeController::class);


// Connexion et deconnexion
Route::post('/login', [AuthController::class, 'login'])->name('login');
// Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('register', [AuthController::class, 'register']);
Route::get('users', [AuthController::class, 'getAll']);
Route::get('users/{id}', [AuthController::class, 'getById']);
Route::put('users/{id}', [AuthController::class, 'update']);
Route::delete('users/{id}', [AuthController::class, 'delete']);

// journal de caisse

Route::get('cashjournal', [CashJournalController::class, 'index']); // Lister toutes les entrées
Route::post('cashjournal', [CashJournalController::class, 'store']); // Créer une nouvelle entrée
Route::get('cashjournal/{id}', [CashJournalController::class, 'show']); // Afficher une entrée spécifique
Route::put('cashjournal/{id}', [CashJournalController::class, 'update']); // Mettre à jour une entrée
Route::delete('cashjournal/{id}', [CashJournalController::class, 'destroy']); // Supprimer une entrée
Route::get('cashjournal/filter-by-date/{startdate}/{enddate}', [CashJournalController::class, 'filterByDate']); // Filtrer par date
Route::get('cashjournal/detail-filter/{startdate}/{enddate}/{transaction_type}/{created_by}', [CashJournalController::class, 'detailedFilter']);