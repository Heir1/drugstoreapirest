<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;

class MovementControllerCustomized extends Controller
{

    // getAllMovements

    public function getAllMovements()
    {
        $movements = Movement::with(['movementType', 'article.placements', 'article.suppliers'])->get();

        return response()->json($movements, 200);
    }


    public function createInvoice(Request $request)
    {

        try {
                //code...
                $validated = $request->validate([
                    'article_id' => 'required|exists:articles,id',
                    'quantity' => 'required|integer',
                    'movement_type_id' => 'required|exists:movement_types,id',
                    'reference' => 'nullable|string|max:100',
                    'purchase_price' => 'required|integer',
                    'selling_price' => 'required|integer',
                    'expiration_date' => 'required|string',
                    'articles' => 'nullable|array',
                    'items.*.id' => 'nullable|exists:articles,id',
                    'articles.*.quantity' => 'nullable|integer|min:1',
                ]);
        
                // $movement = Movement::create($validated);
        
                $article = Article::find($validated['article_id']);
        
                if($article->purchase_price !==  $validated['purchase_price']){
                    $article->purchase_price = $validated['purchase_price'];
                    $article->selling_price = $validated['selling_price']; 
                }

                if($article->expiration_date !==  $validated['expiration_date']){
                    $article->expiration_date = $validated['expiration_date'];
                }
        
                $movement = new Movement();
                $movement->article_id = $validated['article_id'];
                $movement->quantity = $validated['quantity'];
                $movement->movement_type_id = $validated['movement_type_id'];
                $movement->reference = $validated['reference'];
                $movement->old_article_stock = $article->quantity;
        
                $movement->save();
        
                // Mettre à jour la quantité en stock
                $movementType = $movement->movementType->name;
        
                if ($movementType === 'Entree') {
                    $article->quantity += $validated['quantity'];
                } elseif ($movementType === 'Sortie') {

                    // start of creation invoice

                    // end of invoice creation

                    if ($article->quantity < $validated['quantity']) {



                        return response()->json([
                            'error' => 'La quantité est saisie est supérieure au stock disponible .'
                        ], 400);

                    }

                    $article->quantity -= $validated['quantity'];
                    
                } elseif ($movementType === 'Ajustment') {
                    $article->quantity = $validated['quantity'];
                }
        
                $article->save();
        
                $movement->load(['article.placements', 'article.suppliers']);
        
                return response()->json($movement, Response::HTTP_CREATED);

        } catch (ValidationException $e) {
            // Si une erreur de validation se produit, on renvoie une réponse avec le message d'erreur
            return response()->json([
                'error' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // Code 422 pour une erreur de validation
        } catch (\Exception $e) {
            // Gérer les erreurs générales et renvoyer une réponse appropriée
            return response()->json([
                'error' => 'Une erreur interne est survenue. Veuillez réessayer plus tard.',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR); // Code 500 pour une erreur serveur interne
        }

        // // Validate request data

        // $request->validate([
        //     'items' => 'required|array',
        //     'items.*.id' => 'required|exists:items,id',
        //     'items.*.quantity' => 'required|integer|min:1',
        // ]);

        // // Generate unique invoice number
        // $invoiceNumber = Invoice::generateInvoiceNumber();

        // // Create the invoice
        // $invoice = Invoice::create([
        //     'invoice_date' => now(),
        //     'invoice_number' => $invoiceNumber,
        // ]);

        // $totalExclTax = 0;

        // foreach ($request->items as $itemData) {
        //     $item = Item::findOrFail($itemData['id']);
        //     $quantity = $itemData['quantity'];
        //     $unitPrice = $item->unit_price;
        //     $subtotal = $quantity * $unitPrice;

        //     // Add invoice line
        //     InvoiceLine::create([
        //         'invoice_id' => $invoice->id,
        //         'item_id' => $item->id,
        //         'quantity' => $quantity,
        //         'unit_price' => $unitPrice,
        //         'subtotal' => $subtotal,
        //     ]);

        //     // Reduce stock
        //     $item->update(['stock' => $item->stock - $quantity]);

        //     $totalExclTax += $subtotal;
        // }

        // // Calculate VAT and total incl. tax
        // $vat = $totalExclTax * 0.16; // 16% VAT
        // $totalInclTax = $totalExclTax + $vat;

        // $invoice->update([
        //     'total_excl_tax' => $totalExclTax,
        //     'vat' => $vat,
        //     'total_incl_tax' => $totalInclTax,
        // ]);

        // return response()->json([
        //     'message' => 'Invoice successfully created',
        //     'invoice' => $invoice,
        //     'invoice_lines' => $invoice->invoiceLines,
        // ], 201);
    }


}
