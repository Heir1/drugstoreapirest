<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Article;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;
use Carbon\Carbon;

class InvoiceController extends Controller


{

    public function getAllInvoices($mode, $invoice, $firstrange, $secondrange)
    {
        // Validation et conversion des dates pour éviter les erreurs
        try {
            $firstrange = Carbon::parse($firstrange)->startOfDay();
            $secondrange = Carbon::parse($secondrange)->endOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format'], 400);
        }
    
        // Déterminer la valeur de is_proforma selon $invoice
        $isProforma = ($invoice == 2); // 1 = Proforma (true), 2 = Facture (false)

        // return $isProforma;
    
        // Requête optimisée
        $invoices = InvoiceLine::whereBetween('created_at', [$firstrange, $secondrange])
            ->with(['invoices', 'articles'])
            ->whereHas('invoices', function ($query) use ($mode, $isProforma) {
                $query->where('paymentmode_id', $mode)
                      ->where('is_proforma', $isProforma);
            })->get();
    
        return response()->json($invoices);
    }


    public function createInvoice(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validation des données
            $validated = $request->validate([
                'client_name'  => 'required|string',
                'invoice' => 'required|integer|in:1,2', // 1 = Facture, 2 = Pro forma
                'paymentmode' => 'required|integer',
                'articles' => 'required|array',
                'articles.*.id' => 'required|exists:articles,id',
                'articles.*.quantity1' => 'required|integer|min:1',
                'created_by' => 'nullable'
            ]);
            // Générer un numéro de facture unique
            $invoiceNumber = Invoice::generateInvoiceNumber();

            // Déterminer s'il s'agit d'une facture ou d'une pro forma
            $isProforma;

            $isProforma = $validated['invoice'] == 2;

            // Création de la facture/pro forma
            $invoice = Invoice::create([
                'invoice_date' => now(),
                'invoice_number' => $invoiceNumber,
                'paymentmode_id' => $validated['paymentmode'],
                'client_name' => $validated['client_name'],
                'created_by' => $validated['created_by'],
                'is_proforma' => $isProforma, // Nouveau champ pour différencier facture/pro forma
            ]);

            $totalExclTax = 0;

            foreach ($validated['articles'] as $articleData) {
                $article = Article::findOrFail($articleData['id']);
                $quantity = $articleData['quantity1'];

                // Vérification du stock (uniquement pour une facture réelle)
                if (!$isProforma && $article->quantity < $quantity) {
                    throw new \Exception("Insufficient stock for article ID {$article->id}.");
                }

                $unitPrice = $article->selling_price;
                $subtotal = $quantity * $unitPrice;

                // Création de la ligne de facture
                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'article_id' => $article->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                // Si ce n'est pas une pro forma, enregistrer le mouvement et mettre à jour le stock
                if (!$isProforma) {
                    $newUuid = Str::uuid();

                    // Enregistrement du mouvement de stock
                    Movement::create([
                        'article_id' => $article->id,
                        'quantity' => $quantity,
                        'movement_type_id' => 2, // Vente
                        'reference' => "REF-" . $newUuid,
                        'old_article_stock' => $article->quantity,
                    ]);

                    // Mise à jour du stock
                    $article->update(['quantity' => $article->quantity - $quantity]);
                }

                $totalExclTax += $subtotal;
            }

            // Calcul de la TVA et du total TTC
            $vat = $totalExclTax * 0.16; // TVA de 16%
            $totalInclTax = $totalExclTax + $vat;

            // Mise à jour des montants totaux
            $invoice->update([
                'total_excl_tax' => $totalExclTax,
                'vat' => $vat,
                'total_incl_tax' => $totalInclTax,
            ]);

            DB::commit();

            return response()->json([
                'message' => $isProforma ? 'Pro forma successfully created' : 'Invoice successfully created',
                'invoice' => $invoice,
                'invoice_lines' => $invoice->invoiceLines,
            ], 201);
            
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Validation error',
                'details' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating invoice', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'An error occurred while creating the invoice.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function updateInvoice(Request $request, $id)
    {
        // Validation des données du formulaire
        $validated = $request->validate([
            'article_id' => 'required|integer|exists:articles,id', // Vérifie si l'article existe
            'quantity' => 'required|integer|min:1', // La quantité doit être un entier et >= 1
            'updated_by' => 'nullable'
        ]);

        
        // Récupérer la ligne de facture (InvoiceLine)
        $invoiceLine = InvoiceLine::with(['invoices', 'articles'])->find($id);
        $invoice = Invoice::where('id',$invoiceLine->invoice_id)->first();

        $invoice->updated_by = $validated['updated_by'];
        // Vérifier si la ligne de facture existe
        if (!$invoiceLine) {
            return response()->json([
                'error' => 'Invoice line not found',
            ], 404);
        }
    
        // Récupérer l'article à partir de l'ID
        $article = Article::find($validated['article_id']);
    
        // Vérifier si l'article existe
        if (!$article) {
            return response()->json([
                'error' => 'Article not found',
            ], 404);
        }
    
        // Gestion de l'inventaire de l'article : décrémente la quantité précédente
        $article->quantity += $invoiceLine->quantity; // Restaure la quantité d'avant
        $article->quantity -= $validated['quantity']; // Décrémente de la nouvelle quantité
    
        // Mise à jour de la ligne de facture avec la nouvelle quantité
        $invoiceLine->quantity = $validated['quantity'];
        $invoiceLine->subtotal = $validated['quantity'] * $invoiceLine->unit_price;
    
        // Démarrer la transaction pour garantir que les deux mises à jour se font ensemble
        try {
            \DB::beginTransaction();
    
            // Sauvegarder les modifications
            $invoiceLine->save();
            $article->save();
    
            // Recharger les relations pour inclure les dernières modifications
            $invoiceLine->load(['invoices', 'articles']);
    
            // Confirmer la transaction
            \DB::commit();
    
            return response()->json([
                'message' => 'Invoice successfully updated',
                ...$invoiceLine->toArray(),
            ], 200);
    
        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            \DB::rollBack();
    
            // Log l'erreur pour le débogage
            \Log::error('Error updating invoice: ' . $e->getMessage());
    
            return response()->json([
                'error' => 'There was an error while updating the invoice. Please try again later.',
            ], 500);
        }
    }
    

    public function deleteInvoice($id)
    {
        
        DB::beginTransaction();

        try {
            // Récupérer la ligne de facture
            $invoiceLine = InvoiceLine::find($id);

            $invoice = Invoice::find($invoiceLine->invoice_id);

            // Vérifier si la ligne de facture existe
            if (!$invoiceLine) {
                return response()->json(['error' => 'Invoice line not found'], Response::HTTP_NOT_FOUND);
            }

            // Récupérer l'article associé
            $article = Article::find($invoiceLine->article_id);

            // Vérifier si l'article existe
            if (!$article) {
                return response()->json(['error' => 'Article not found'], Response::HTTP_NOT_FOUND);
            }

            if(!$invoice->is_proforma){
                // Restaurer la quantité de l'article
                $article->quantity += $invoiceLine->quantity;
                $article->save();
            }


            // Supprimer la ligne de facture
            $invoiceLine->delete();

            // Commit de la transaction
            DB::commit();

            // Retourner une réponse HTTP 204 (No Content)
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            // Rollback de la transaction en cas d'erreur
            DB::rollBack();

            // Retourner une réponse avec une erreur
            return response()->json([
                'error' => 'An error occurred while deleting the invoice line',
                'details' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function getInvoiceNumber()
    {
        try {
            // Générer un numéro de facture unique
            $invoiceNumber = Invoice::generateInvoiceNumber();
    
            // Retourner une réponse JSON standardisée
            return response()->json([
                'success' => true,
                'message' => 'Invoice number generated successfully.',
                'data' => $invoiceNumber,
            ], 200);
    
        } catch (\Exception $e) {
            // Gérer les erreurs en retournant une réponse JSON d'erreur
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoice number.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    
}
