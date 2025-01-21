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
    
    public function getAllInvoices($mode, $firstrange, $secondrange){

        // $mode, $firstrange, $secondrange

        $firstrange = Carbon::parse($firstrange)->startOfDay();
        $secondrange = Carbon::parse($secondrange)->endOfDay();

        // // Charger les relations many-to-many avec les autres modèles

        // // $invoices = InvoiceLine::with(['invoices', 'articles'])->get();
        

        $invoices = InvoiceLine::whereBetween('created_at', [$firstrange, $secondrange])->with(['invoices', 'articles'])->whereHas('invoices', function ($query) {
            $query->where('paymentmode_id', 1);
        })->get();
        

        // with(['currency', 'category', 'packaging', 'placements', 'molecules', 'suppliers', 'indications'])->get();

        // $articles->each(function ($article) {
        //     $article->placements->makeHidden('pivot');
        //     $article->molecules->makeHidden('pivot');
        //     $article->suppliers->makeHidden('pivot');
        //     $article->indications->makeHidden('pivot');
        // });

        return response()->json($invoices);

    }

    public function createInvoice(Request $request)
    {

        DB::beginTransaction();

        try {
            // Validate request data
            $validated = $request->validate([
                'paymentmode' => 'required|integer',
                'articles' => 'required|array',
                'articles.*.id' => 'required|exists:articles,id',
                'articles.*.quantity1' => 'required|integer|min:1',
            ]);

            // // Generate unique invoice number
            $invoiceNumber = Invoice::generateInvoiceNumber();


            // return $validated['paymentmode'];

            // return $validated;

            // Create the invoice
            $invoice = Invoice::create([
                'invoice_date' => now(),
                'invoice_number' => $invoiceNumber,
                'paymentmode_id' => $validated['paymentmode'],
                'client_name' => "Héritier N'kele"
            ]);

            $totalExclTax = 0;

            
            foreach ($validated['articles'] as $articleData) {
                
                $article = Article::findOrFail($articleData['id']);
                $quantity = $articleData['quantity1'];
                
                // Check stock availability
                if ($article->quantity < $quantity) {
                    throw new \Exception("Insufficient stock for article ID {$article->id}.");
                }
                
                $unitPrice = $article->selling_price;
                $subtotal = $quantity * $unitPrice;
                $newUuid = Str::uuid();
                
                
                // Record movement
                $movement = Movement::create([
                    'article_id' => $article->id,
                    'quantity' => $quantity,
                    'movement_type_id' => 2, // Sale movement
                    'reference' => "REF-" . $newUuid,
                    'old_article_stock' => $article->quantity,
                ]);
                
                // Add invoice line
                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'article_id' => $article->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
                
                // Reduce quantity in stock
                $article->update(['quantity' => $article->quantity - $quantity]);
                
                $totalExclTax += $subtotal;
            }
            // return "Success";

            // Calculate VAT and total incl. tax
            $vat = $totalExclTax * 0.16; // 16% VAT
            $totalInclTax = $totalExclTax + $vat;

            $invoice->update([
                'total_excl_tax' => $totalExclTax,
                'vat' => $vat,
                'total_incl_tax' => $totalInclTax,
                'paymentmode_id' => $validated['paymentmode']
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Invoice successfully created',
                'invoice' => $invoice,
                'invoice_lines' => $invoice->invoiceLines,
            ], 201);
            
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Validation error',
                'details' => $e->errors(),
            ], 422); // Unprocessable Entity
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error for debugging purposes
            Log::error('Error creating invoice', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'An error occurred while creating the invoice.',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }


    public function updateInvoice(Request $request, $id){


        // $validated = $request->validate([
        //     'article_id' => 'required|integer',
        //     'id' => 'required|integer',
        //     'quantity' => 'required|integer'
        // ]);

        // $invoiceLine = InvoiceLine::find($id);

        // $article = Article::find($validated['article_id']);

        // $article->quantity -= $invoiceLine->quantity;

        // $invoiceLine->quantity = $validated['quantity'];
        // $invoiceLine->update();

        // $article->update();

        // $article->quantity += $validated['quantity'];

        // $article->update();

        // return response()->json([
        //     'message' => 'Invoice successfully updated',
        //     'invoice' => $invoice,
        //     'invoice_lines' => $invoice->invoiceLines,
        // ], 201);


        // Validation des données du formulaire
        $validated = $request->validate([
            'article_id' => 'required|integer|exists:articles,id', // Vérifie si l'article existe
            'quantity' => 'required|integer|min:1', // La quantité doit être un entier et >= 1
        ]);

        // Récupérer la ligne de facture (InvoiceLine)
        $invoiceLine = InvoiceLine::find($id);

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

        // Démarrer la transaction pour garantir que les deux mises à jour se font ensemble
        try {
            \DB::beginTransaction();

            // Sauvegarder les modifications
            $invoiceLine->save();
            $article->save();

            // Confirmer la transaction
            \DB::commit();

            return response()->json([
                'message' => 'Invoice successfully updated',
                'invoice_line' => $invoiceLine,
                'article' => $article,
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
        // Supprimer l'article
        $invoice = Invoice::find($id);

        $invoice->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
    
}
