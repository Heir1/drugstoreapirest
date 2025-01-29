<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Connexion d'un utilisateur avec vérification du rôle.
     */
    public function login(Request $request)
    {
        // Validation des champs
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Tentative de connexion
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            // Vérification du rôle
            if ($user->role === 'admin') {
                return response()->json([
                    'message' => 'Connexion réussie',
                    'user' => $user,
                    // 'token' => $user->createToken('admin-token')->plainTextToken // Si API
                ], 200);
            } elseif ($user->role === 'agent') {
                return response()->json([
                    'message' => 'Bienvenue, agent',
                    'user' => $user,
                    // 'token' => $user->createToken('agent-token')->plainTextToken
                ], 200);
            } elseif ($user->role === 'etudiant') {
                return response()->json([
                    'message' => 'Bienvenue, étudiant',
                    'user' => $user,
                    // 'token' => $user->createToken('etudiant-token')->plainTextToken
                ], 200);
            } else {
                Auth::logout();
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }
        }

        // Si échec de connexion
        return response()->json(['message' => 'Mauvais identifiants'], 401);
    }

    /**
     * Déconnexion de l'utilisateur.
     */
    public function logout()
    {
        // $user = Auth::user();
        // if ($user) {
            // $user->tokens()->delete(); // Si API avec Sanctum
            Auth::logout();
            return response()->json(['message' => 'Déconnexion réussie']);
        // }

        // return response()->json(['message' => 'Utilisateur non connecté'], 401);
    }


    public function register(Request $request)
    {
        try {
            // Définition des rôles autorisés
            $rolesAutorises = [User::ROLE_ADMIN, User::ROLE_USER];
    
            // Validation des données
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                'role' => ['required', Rule::in($rolesAutorises)],
            ]);


            return $validatedData;
    
            // Normalisation des entrées
            // $validatedData['email'] = strtolower(trim($validatedData['email']));
            // $validatedData['password'] = bcrypt($validatedData['password']);
    
            // // Création de l'utilisateur
            // $user = User::create($validatedData);
    
            // // Retour de la réponse (sans password ni updated_at)
            // return response()->json([
            //     'message' => 'Utilisateur créé avec succès.',
            //     'user' => $user->only(['id', 'name', 'email', 'role', 'created_at']),
            // ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de l’inscription.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // **2. Get all users** (Récupérer tous les utilisateurs)
    public function getAll()
    {
        $users = User::all();
        // return response()->json([
        //     'users' => $users
        // ], 200);

        return response()->json($users);
    }
    
    // **3. Get user by ID** (Récupérer un utilisateur par son ID)
    public function getById($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non trouvé.',
            ], 404);
        }

        return response()->json([
            'user' => $user
        ], 200);
    }
    
    // **4. Update user** (Mettre à jour un utilisateur)
    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'message' => 'Utilisateur non trouvé.',
                ], 404);
            }

            // Validation des données
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'password' => 'nullable|string|min:6|confirmed',
                'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            ]);

            // Mise à jour des données
            $user->update(array_filter($validatedData)); // Utilisation de `array_filter` pour ne pas modifier des champs vides comme le password si non renseigné

            if (isset($validatedData['password'])) {
                $user->password = bcrypt($validatedData['password']);
            }

            return response()->json([
                'message' => 'Utilisateur mis à jour avec succès.',
                'user' => $user->only(['id', 'name', 'email', 'role', 'updated_at']),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la mise à jour.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    // **5. Delete user** (Supprimer un utilisateur)
    public function delete($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non trouvé.',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès.',
        ], 200);
    }
    

}
