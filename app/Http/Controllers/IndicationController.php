<?php

namespace App\Http\Controllers;

use App\Models\Indication;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class IndicationController extends Controller
{
    /**
     * Display a listing of the indications.
     */
    public function index()
    {
        try {
            // Récupérer toutes les indications
            $indications = Indication::with(['articles'])->get();
            return response()->json($indications, 200); // Retourner les indications avec un code 200 OK
        } catch (\Exception $e) {
            Log::error('Error retrieving indications: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error retrieving indications',
                'message' => $e->getMessage(),
            ], 500); // Erreur serveur interne
        }
    }

    /**
     * Store a newly created indication in the database.
     */
    public function store(Request $request)
    {
        try {
            // Validation des données d'entrée
            $request->validate([
                'name' => 'required|string|max:255', // Validation du champ 'name'
            ]);

            // Générer un UUID pour 'row_id'
            $row_id = (string) Str::uuid();  // Générer un UUID pour le champ 'row_id'

            // Création de la nouvelle indication
            $indication = Indication::create([
                'name' => $request->name,
                'row_id' => $row_id,  // UUID généré pour 'row_id'
                'created_by' => auth()->id() ?? null, // Utilisateur qui a créé l'indication
            ]);

            return response()->json($indication, 201); // Retourner l'indication créée avec un code 201 Created
        } catch (ValidationException $e) {
            Log::error('Validation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors(),
            ], 422); // Erreur de validation
        } catch (\Exception $e) {
            Log::error('Error storing indication: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error storing indication',
                'message' => $e->getMessage(),
            ], 500); // Erreur serveur interne
        }
    }

    /**
     * Display the specified indication.
     */
    public function show($id)
    {
        try {
            // Trouver l'indication par son ID
            $indication = Indication::findOrFail($id);
            return response()->json($indication, 200); // Retourner l'indication trouvée avec un code 200 OK
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Indication not found: ' . $e->getMessage());
            return response()->json([
                'error' => 'Indication not found',
                'message' => 'The indication with the given ID does not exist.',
            ], 404); // Retourner un code 404 Not Found
        } catch (\Exception $e) {
            Log::error('Error retrieving indication: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error retrieving indication',
                'message' => $e->getMessage(),
            ], 500); // Erreur serveur interne
        }
    }

    /**
     * Update the specified indication in the database.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validation des données d'entrée
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Trouver l'indication par son ID
            $indication = Indication::findOrFail($id);

            // Mise à jour de l'indication
            $indication->update([
                'name' => $request->name,
                'updated_by' => auth()->id() ?? null, // Utilisateur ayant mis à jour l'indication
            ]);

            return response()->json($indication, 200); // Retourner l'indication mise à jour avec un code 200 OK
        } catch (ValidationException $e) {
            Log::error('Validation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors(),
            ], 422); // Erreur de validation
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Indication not found: ' . $e->getMessage());
            return response()->json([
                'error' => 'Indication not found',
                'message' => 'The indication with the given ID does not exist.',
            ], 404); // Retourner un code 404 Not Found
        } catch (\Exception $e) {
            Log::error('Error updating indication: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error updating indication',
                'message' => $e->getMessage(),
            ], 500); // Erreur serveur interne
        }
    }

    /**
     * Remove the specified indication from the database.
     */
    public function destroy($id)
    {
        try {
            // Trouver l'indication par son ID
            $indication = Indication::findOrFail($id);

            // Supprimer l'indication
            $indication->delete();

            return response()->json([
                'message' => 'Indication deleted successfully',
            ], 200); // Retourner un message de succès avec un code 200 OK
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Indication not found: ' . $e->getMessage());
            return response()->json([
                'error' => 'Indication not found',
                'message' => 'The indication with the given ID does not exist.',
            ], 404); // Retourner un code 404 Not Found
        } catch (\Exception $e) {
            Log::error('Error deleting indication: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error deleting indication',
                'message' => $e->getMessage(),
            ], 500); // Erreur serveur interne
        }
    }
}