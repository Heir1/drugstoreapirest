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

class InvoiceController extends Controller
{
    public function getAllInvoices(){
                // Charger les relations many-to-many avec les autres modèles
                $invoices = InvoiceLine::with(['invoices', 'articles'])->get();
                

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
                'articles' => 'required|array',
                'articles.*.id' => 'required|exists:articles,id',
                'articles.*.quantity1' => 'required|integer|min:1',
            ]);

            // Generate unique invoice number
            $invoiceNumber = Invoice::generateInvoiceNumber();

            // Create the invoice
            $invoice = Invoice::create([
                'invoice_date' => now(),
                'invoice_number' => $invoiceNumber,
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

    public function deleteInvoice($id)
    {
        // Supprimer l'article
        $invoice = Invoice::find($id);

        $invoice->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
    
}
