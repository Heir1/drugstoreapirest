<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashJournal;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CashJournalController extends Controller
{
        /**
     * Afficher la liste des entrées du journal de caisse.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Filtrer par date si une date est fournie
        if ($request->has('date')) {
            $date = $request->query('date');
            $cashJournals = CashJournal::whereDate('created_at', $date)
                ->with(['currency', 'createdBy', 'updatedBy'])
                ->get();
        } else {
            $cashJournals = CashJournal::with(['currency', 'createdBy', 'updatedBy'])->get();
        }

        return response()->json($cashJournals, 200);
    }

    /**
     * Créer une nouvelle entrée dans le journal de caisse.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validation des données
            $validator = Validator::make($request->all(), [
                'transaction_type' => 'required|in:income,expense',
                'amount' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'created_by' => 'nullable|integer',
                'currency_id' => 'required|exists:currencies,id',
                'transaction_date' => 'required|date',
            ]);
    
            // Si la validation échoue, retourner les erreurs
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 400);
            }
    
            // Création de l'entrée
            $cashJournal = CashJournal::create([
                'transaction_type' => $request->transaction_type,
                'amount' => $request->amount,
                'description' => $request->description,
                'currency_id' => $request->currency_id,
                'created_by' => $request->created_by, // ID de l'utilisateur connecté (ou null si non connecté)
                'transaction_date' => $request->transaction_date,
            ]);
    
            // Reload the created CashJournal with its relationships
            $cashJournal->load(['currency', 'createdBy', 'updatedBy']);

            return response()->json($cashJournal, 201);
    
        } catch (Exception $e) {
            // Gestion des erreurs inattendues
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création de la transaction',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Afficher les détails d'une entrée spécifique.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $cashJournal = CashJournal::with(['currency', 'createdBy', 'updatedBy'])->find($id);

        if (!$cashJournal) {
            return response()->json(['error' => 'Entry not found'], 404);
        }

        return response()->json($cashJournal, 200);
    }

    /**
     * Mettre à jour une entrée existante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'transaction_type' => 'sometimes|in:income,expense',
            'amount' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'currency_id' => 'sometimes|exists:currencies,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Récupérer l'entrée à mettre à jour
        $cashJournal = CashJournal::find($id);

        if (!$cashJournal) {
            return response()->json(['error' => 'Entry not found'], 404);
        }

        // Mise à jour de l'entrée
        $cashJournal->update([
            'transaction_type' => $request->transaction_type ?? $cashJournal->transaction_type,
            'amount' => $request->amount ?? $cashJournal->amount,
            'description' => $request->description ?? $cashJournal->description,
            'currency_id' => $request->currency_id ?? $cashJournal->currency_id,
            'updated_by' => Auth::check() ? Auth::id() : null // ID de l'utilisateur connecté
        ]);

        $cashJournal = CashJournal::with(['currency', 'createdBy', 'updatedBy'])->find($id);

        return response()->json($cashJournal, 200);
    }

    /**
     * Supprimer une entrée existante.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $cashJournal = CashJournal::find($id);

        if (!$cashJournal) {
            return response()->json(['error' => 'Entry not found'], 404);
        }

        $cashJournal->delete();

        return response()->json(['message' => 'Entry deleted successfully'], 200);
    }

    /**
     * Filtrer les entrées du journal de caisse par date.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterByDate(Request $request, $startdate, $enddate)
    {

        // Construire la requête de base
        $query = CashJournal::with(['currency', 'createdBy', 'updatedBy']);

        $enddateInclusive = date('Y-m-d', strtotime($enddate . ' +1 day'));
        // Appliquer le filtre par plage de dates
        $query->whereBetween('created_at', [$startdate, $enddateInclusive]);

        // Exécuter la requête et récupérer les résultats
        $cashJournals = $query->get();

        // Retourner la réponse JSON
        return response()->json($cashJournals, 200);
    }

    public function detailedFilter(Request $request, $startdate, $enddate, $transaction_type, $created_by)
{
    // Construire la requête de base avec les relations
    $query = CashJournal::with(['currency', 'createdBy', 'updatedBy']);

    // Ajouter 1 jour à la date de fin pour inclure la journée entière
    $enddateInclusive = date('Y-m-d', strtotime($enddate . ' +1 day'));

    // Appliquer le filtre par plage de dates
    $query->whereBetween('created_at', [$startdate, $enddateInclusive]);

    // Filtrer par transaction_type (income, expense, ou all)
    if ($transaction_type !== 'all') {
        $query->where('transaction_type', $transaction_type);
    }

    // Filtrer par created_by (user_id ou all)
    if ($created_by !== 'all') {
        $query->where('created_by', $created_by);
    }

    // Exécuter la requête et récupérer les résultats
    $cashJournals = $query->get();

    // Grouper par created_by et par date
    $groupedCashJournals = $cashJournals->groupBy(['created_by', function ($transaction) {
        return $transaction->created_at->format('Y-m-d'); // Grouper par date au format 'Y-m-d'
    }])->map(function ($transactionsByDate, $createdById) {
        // Récupérer le nom de l'utilisateur (createdBy)
        $userName = $transactionsByDate->first()->first()->createdBy->name ?? 'Inconnu';

        // Structurer les transactions par date
        return $transactionsByDate->map(function ($transactions, $date) use ($userName) {
            return [
                'date' => $date, // Utilisation de '$date'
                'name' => $userName, // Ajout du nom de l'utilisateur ici
                'transactions' => $transactions->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'transaction_type' => $transaction->transaction_type,
                        'amount' => $transaction->amount,
                        'description' => $transaction->description,
                        'currency' => $transaction->currency,
                        'created_at' => $transaction->created_at,
                        'updated_at' => $transaction->updated_at,
                        'updated_by' => $transaction->updatedBy,
                    ];
                }),
            ];
        });
    })->flatten(1); // Aplatir le tableau pour supprimer le premier niveau de crochets

    // Retourner la réponse JSON
    return response()->json($groupedCashJournals, 200);
}

//     public function detailedFilter(Request $request, $startdate, $enddate, $transaction_type, $created_by)
// {
//     // Construire la requête de base avec les relations
//     $query = CashJournal::with(['currency', 'createdBy', 'updatedBy']);

//     // Ajouter 1 jour à la date de fin pour inclure la journée entière
//     $enddateInclusive = date('Y-m-d', strtotime($enddate . ' +1 day'));

//     // Appliquer le filtre par plage de dates
//     $query->whereBetween('created_at', [$startdate, $enddateInclusive]);

//     // Filtrer par transaction_type (income, expense, ou all)
//     if ($transaction_type !== 'all') {
//         $query->where('transaction_type', $transaction_type);
//     }

//     // Filtrer par created_by (user_id ou all)
//     if ($created_by !== 'all') {
//         $query->where('created_by', $created_by);
//     }

//     // Exécuter la requête et récupérer les résultats
//     $cashJournals = $query->get();

//     // Grouper par created_by et par date
//     $groupedCashJournals = $cashJournals->groupBy(['created_by', function ($transaction) {
//         return $transaction->created_at->format('Y-m-d'); // Grouper par date au format 'Y-m-d'
//     }])->map(function ($transactionsByDate, $createdById) {
//         // Récupérer le nom de l'utilisateur (createdBy)
//         $userName = $transactionsByDate->first()->first()->createdBy->name ?? 'Inconnu';

//         // Structurer les transactions par date
//         $transactionsGroupedByDate = $transactionsByDate->map(function ($transactions, $date) use ($userName) {
//             return [
//                 '$date' => $date, // Utilisation de '$date'
//                 '$name' => $userName, // Ajout du nom de l'utilisateur ici
//                 'transactions' => $transactions->map(function ($transaction) {
//                     return [
//                         'id' => $transaction->id,
//                         'transaction_type' => $transaction->transaction_type,
//                         'amount' => $transaction->amount,
//                         'description' => $transaction->description,
//                         'currency' => $transaction->currency,
//                         'created_at' => $transaction->created_at,
//                         'updated_at' => $transaction->updated_at,
//                         'updated_by' => $transaction->updatedBy,
//                     ];
//                 }),
//             ];
//         })->values(); // Convertir en tableau indexé

//         // Retourner un tableau structuré
//         return $transactionsGroupedByDate;
//     })->values(); // Convertir en tableau indexé

//     // Retourner la réponse JSON
//     return response()->json($groupedCashJournals, 200);
// }

//     public function detailedFilter(Request $request, $startdate, $enddate, $transaction_type, $created_by)
// {
//     // Construire la requête de base avec les relations
//     $query = CashJournal::with(['currency', 'createdBy', 'updatedBy']);

//     // Ajouter 1 jour à la date de fin pour inclure la journée entière
//     $enddateInclusive = date('Y-m-d', strtotime($enddate . ' +1 day'));

//     // Appliquer le filtre par plage de dates
//     $query->whereBetween('created_at', [$startdate, $enddateInclusive]);

//     // Filtrer par transaction_type (income, expense, ou all)
//     if ($transaction_type !== 'all') {
//         $query->where('transaction_type', $transaction_type);
//     }

//     // Filtrer par created_by (user_id ou all)
//     if ($created_by !== 'all') {
//         $query->where('created_by', $created_by);
//     }

//     // Exécuter la requête et récupérer les résultats
//     $cashJournals = $query->get();

//     // Grouper par created_by et par date
//     $groupedCashJournals = $cashJournals->groupBy(['created_by', function ($transaction) {
//         return $transaction->created_at->format('Y-m-d'); // Grouper par date au format 'Y-m-d'
//     }])->map(function ($transactionsByDate, $createdById) {
//         // Récupérer le nom de l'utilisateur (createdBy)
        
//         $userName = $transactionsByDate->first()->first()->createdBy->name ?? 'Inconnu';
//         // Structurer les transactions par date
//         $transactionsGroupedByDate = $transactionsByDate->map(function ($transactions, $date) {
//             return [
//                 '$date' => $date, // Utilisation de '$date'
//                 '$name' => $userName, // Ajout du nom de l'utilisateur ici
//                 'transactions' => $transactions->map(function ($transaction) {
//                     return [
//                         'id' => $transaction->id,
//                         'transaction_type' => $transaction->transaction_type,
//                         'amount' => $transaction->amount,
//                         'description' => $transaction->description,
//                         'currency' => $transaction->currency,
//                         'created_at' => $transaction->created_at,
//                         'updated_at' => $transaction->updated_at,
//                         'updated_by' => $transaction->updatedBy,
//                     ];
//                 }),
//             ];
//         })->values(); // Convertir en tableau indexé

//         // Retourner un tableau structuré
//         return [
//             'created_by_id' => $createdById,
//             'created_by_name' => $userName,
//             'transactions_by_date' => $transactionsGroupedByDate,
//         ];
//     })->values(); // Convertir en tableau indexé

//     // Retourner la réponse JSON
//     return response()->json($groupedCashJournals, 200);
// }


    // public function detailedFilter(Request $request, $startdate, $enddate, $transaction_type, $created_by)
    // {

    //     // Construire la requête de base avec les relations
    //     $query = CashJournal::with(['currency', 'createdBy', 'updatedBy']);

    //     // Ajouter 1 jour à la date de fin pour inclure la journée entière
    //     $enddateInclusive = date('Y-m-d', strtotime($enddate . ' +1 day'));

    //     // Appliquer le filtre par plage de dates
    //     $query->whereBetween('created_at', [$startdate, $enddateInclusive]);

    //     // Filtrer par transaction_type (income, expense, ou all)
    //     if ($transaction_type !== 'all') {
    //         $query->where('transaction_type', $transaction_type);
    //     }

    //     // Filtrer par created_by (user_id ou all)
    //     if ($created_by !== 'all') {
    //         $query->where('created_by', $created_by);
    //     }

    //     // Exécuter la requête et récupérer les résultats
    //     $cashJournals = $query->get();

    //     $groupedCashJournals = $cashJournals->groupBy('created_by')->map(function ($transactions, $createdById) {
    //         // Récupérer le nom de l'utilisateur (createdBy)
    //         $userName = $transactions->first()->createdBy->name ?? 'Inconnu';
    
    //         // Retourner un tableau structuré
    //         return [
    //             'created_by_id' => $createdById,
    //             'created_by_name' => $userName,
    //             'transactions' => $transactions->map(function ($transaction) {
    //                 return [
    //                     'id' => $transaction->id,
    //                     'transaction_type' => $transaction->transaction_type,
    //                     'amount' => $transaction->amount,
    //                     'description' => $transaction->description,
    //                     'currency' => $transaction->currency,
    //                     'created_at' => $transaction->created_at,
    //                     'updated_at' => $transaction->updated_at,
    //                     'updated_by' => $transaction->updatedBy,
    //                 ];
    //             }),
    //         ];
    //     })->values(); // Convertir en tableau indexé

    //     // Retourner la réponse JSON
    //     return response()->json($groupedCashJournals, 200);

    // }

}
