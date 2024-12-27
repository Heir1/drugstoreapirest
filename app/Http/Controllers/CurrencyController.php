<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;  // Import for generating UUID
use Illuminate\Validation\ValidationException;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the currencies.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $currencies = Currency::all(); // Get all currencies
            return response()->json($currencies, 200); // Return currencies as JSON
        } catch (\Exception $e) {
            Log::error('Error fetching currencies: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error fetching currencies',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Store a newly created currency in the database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Valider les données d'entrée
            $request->validate([
                'name' => 'required|string|max:255',
                'value' => 'required|numeric',  // On suppose que 'value' doit être un nombre
                'symbol' => 'required|string|max:10',
            ]);
    
            // Générer un UUID pour 'id' et 'row_id' manuellement
            // $id = (string) Str::uuid();  // Générer un UUID pour 'id' (clé primaire)
            $row_id = (string) Str::uuid();  // Générer un UUID pour 'row_id'
    
            // Créer un nouvel enregistrement de devise
            $currency = Currency::create([
                // 'id' => $id,               // Fournir le UUID généré pour 'id'
                'name' => $request->name,
                'value' => $request->value,
                'symbol' => $request->symbol,
                'row_id' => $row_id,       // UUID généré pour row_id
                'created_by' => auth()->id() ?? null, // L'ID de l'utilisateur qui a créé la devise (si vous utilisez l'authentification)
            ]);
    
            return response()->json($currency, 201); // Code 201 pour "Created"
        } catch (ValidationException $e) {
            // Gestion des erreurs de validation
            Log::error('Validation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Échec de la validation',
                'message' => $e->errors(),
            ], 422); // Code 422 pour "Validation error"
        } catch (\Exception $e) {
            // Gestion des erreurs générales
            Log::error('Erreur lors de l\'enregistrement de la devise: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur lors de l\'enregistrement de la devise',
                'message' => $e->getMessage(),
            ], 500); // Code 500 pour "Internal Server Error"
        }
    }

    /**
     * Display the specified currency.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $currency = Currency::findOrFail($id); // Find currency by ID

            return response()->json($currency, 200); // Return the currency
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Currency not found',
                'message' => 'The currency with the provided ID was not found.',
            ], 404); // Not Found
        } catch (\Exception $e) {
            Log::error('Error fetching currency: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error fetching currency',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Update the specified currency in the database.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        try {
            // Validate incoming data
            $request->validate([
                'name' => 'required|string|max:255',
                'value' => 'required|numeric',
                'symbol' => 'required|string|max:10',
            ]);

            // Generate a UUID for row_id if it's not passed
            $row_id = $request->has('row_id') ? $request->row_id : (string) Str::uuid();

            // Find and update the currency
            $currency = Currency::findOrFail($id);
            $currency->update([
                'name' => $request->name,
                'value' => $request->value,
                'symbol' => $request->symbol,
                'row_id' => $row_id,  // Generated UUID for row_id
                'updated_by' => auth()->id() ?? null, // Assuming you are using authentication
            ]);

            return response()->json($currency, 200); // Updated
        } catch (ValidationException $e) {
            Log::error('Validation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors(),
            ], 422); // Validation error
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Currency not found',
                'message' => 'The currency with the provided ID was not found.',
            ], 404); // Not Found
        } catch (\Exception $e) {
            Log::error('Error updating currency: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error updating currency',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Remove the specified currency from the database.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $currency = Currency::findOrFail($id); // Find currency by ID
            $currency->delete(); // Delete the currency

            return response()->json([
                'message' => 'Currency deleted successfully',
            ], 200); // Success
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Currency not found',
                'message' => 'The currency with the provided ID was not found.',
            ], 404); // Not Found
        } catch (\Exception $e) {
            Log::error('Error deleting currency: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error deleting currency',
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }
}
