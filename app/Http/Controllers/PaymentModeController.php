<?php

namespace App\Http\Controllers;

use App\Models\PaymentMode;
use Illuminate\Http\Request;

class PaymentModeController extends Controller
{
        /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Récupère tous les modes de paiement
        $paymentModes = PaymentMode::all();
        return response()->json($paymentModes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Valide les données entrantes
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Crée un nouveau mode de paiement
        $paymentMode = PaymentMode::create($validatedData);

        return response()->json(['message' => 'Payment mode created successfully!', 'data' => $paymentMode], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentMode $paymentMode)
    {
        // Affiche un mode de paiement spécifique
        return response()->json($paymentMode);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentMode $paymentMode)
    {
        // Valide les données entrantes
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Met à jour le mode de paiement
        $paymentMode->update($validatedData);

        return response()->json(['message' => 'Payment mode updated successfully!', 'data' => $paymentMode]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMode $paymentMode)
    {
        // Supprime le mode de paiement
        $paymentMode->delete();

        return response()->json(['message' => 'Payment mode deleted successfully!']);
    }
}
