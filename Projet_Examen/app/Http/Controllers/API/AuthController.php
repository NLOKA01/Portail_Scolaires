<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use App\Services\UserService;
use App\Services\EleveService;

/**
 * @OA\Tag(
 *     name="Authentification",
 *     description="Gestion de l'authentification"
 * )
 */
class AuthController extends Controller
{
    protected $eleveService;

    public function __construct(EleveService $eleveService)
    {
        $this->eleveService = $eleveService;
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentification"},
     *     summary="Inscription d'un nouvel utilisateur",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom","prenom","adresse","telephone","email","password","role"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé"
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'telephone' => 'required|string|max:20|unique:users,telephone',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:' . implode(',', [
                    User::ROLE_ADMIN,
                    User::ROLE_ENSEIGNANT,
                    User::ROLE_PARENT,
                    User::ROLE_ELEVE,
                ]),
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',

            // Champs supplémentaires selon le rôle
            'classe_id' => 'required_if:role,' . User::ROLE_ELEVE,
            'parent_email' => 'required_if:role,' . User::ROLE_ELEVE,
            'parent_nom' => 'required_if:role,' . User::ROLE_ELEVE,
            'parent_prenom' => 'required_if:role,' . User::ROLE_ELEVE,
            'parent_telephone' => 'required_if:role,' . User::ROLE_ELEVE,
            'parent_adresse' => 'required_if:role,' . User::ROLE_ELEVE,
            'classe_ids' => 'required_if:role,' . User::ROLE_ENSEIGNANT, // tableau d'ID
            'matiere_ids' => 'required_if:role,' . User::ROLE_ENSEIGNANT, // tableau d'ID
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Traitement selon le rôle
            switch ($request->role) {
                case User::ROLE_ELEVE:
                    $eleveData = [
                        'nom' => $request->nom,
                        'prenom' => $request->prenom,
                        'adresse' => $request->adresse,
                        'telephone' => $request->telephone,
                        'email' => $request->email,
                        'date_naissance' => $request->date_naissance ?? null,
                        'lieu_naissance' => $request->lieu_naissance ?? null,
                        'sexe' => $request->sexe ?? null,
                        'classe_id' => $request->classe_id,
                    ];
                    $parentData = [
                        'nom' => $request->parent_nom,
                        'prenom' => $request->parent_prenom,
                        'adresse' => $request->parent_adresse,
                        'telephone' => $request->parent_telephone,
                        'email' => $request->parent_email,
                        'profession' => $request->parent_profession ?? null,
                    ];
                    $eleve = $this->eleveService->inscrireEleve($eleveData, $parentData);
                    $user = $eleve->user;
                    break;
                case User::ROLE_ENSEIGNANT:
                    $user = UserService::createEnseignant($request->all());
                    break;
                case User::ROLE_PARENT:
                    $user = UserService::createParent($request->all());
                    break;
                case User::ROLE_ADMIN:
                    $user = User::create([
                        'nom' => $request->nom,
                        'prenom' => $request->prenom,
                        'adresse' => $request->adresse,
                        'telephone' => $request->telephone,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'role' => User::ROLE_ADMIN,
                        'est_actif' => true,
                    ]);
                    break;
                default:
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Rôle invalide.'
                    ], 422);
            }

            // Générer le token pour l’utilisateur créé
            $token = JWTAuth::fromUser($user);
            $expiresIn = config('jwt.ttl', 60) * 60;

            return response()->json([
                'status' => 'success',
                'message' => 'Utilisateur créé avec succès',
                'data' => [
                    'user' => $user->only(['id', 'nom', 'prenom', 'email', 'role', 'telephone', 'adresse', 'est_actif']),
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => $expiresIn
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création de l\'utilisateur',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentification"},
     *     summary="Connexion d'un utilisateur",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Identifiants incorrects'
                ], 401);
            }

            $user = JWTAuth::user();

            if (!$user->isActive()) {
                // Invalider le token si l'utilisateur n'est pas actif
                JWTAuth::invalidate($token);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Compte désactivé. Contactez l\'administrateur.'
                ], 403);
            }

            $expiresIn = config('jwt.ttl', 60) * 60; // Convertir en secondes

            return response()->json([
                'status' => 'success',
                'message' => 'Connexion réussie',
                'data' => [
                    'user' => $user->only(['id', 'nom', 'prenom', 'email', 'role', 'telephone', 'adresse', 'est_actif']),
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => $expiresIn
                ]
            ], 200);

        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la génération du token',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la connexion',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Authentification"},
     *     summary="Déconnexion de l'utilisateur connecté",
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie"
     *     )
     * )
     */
    public function logout()
    {
        try {
            // Récupérer le token et l'invalider
            $token = JWTAuth::getToken();
            JWTAuth::invalidate($token);

            return response()->json([
                'status' => 'success',
                'message' => 'Déconnexion réussie'
            ], 200);

        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token expiré'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token invalide'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la déconnexion',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/me",
     *     tags={"Authentification"},
     *     summary="Obtenir les informations de l'utilisateur connecté",
     *     @OA\Response(
     *         response=200,
     *         description="Informations utilisateur"
     *     )
     * )
     */
    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => $user->only(['id', 'nom', 'prenom', 'email', 'role', 'telephone', 'adresse', 'est_actif', 'image']),
                    'permissions' => [
                        'is_admin' => $user->isAdmin(),
                        'is_enseignant' => $user->isEnseignant(),
                        'is_parent' => $user->isParent(),
                        'is_eleve' => $user->isEleve(),
                    ]
                ]
            ], 200);

        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token expiré'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token invalide'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token manquant'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération du profil',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/profile",
     *     tags={"Authentification"},
     *     summary="Mettre à jour le profil de l'utilisateur connecté",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom","prenom","adresse","telephone","email"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profil mis à jour"
     *     )
     * )
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $validator = Validator::make($request->all(), [
                'nom' => 'sometimes|string|max:255',
                'prenom' => 'sometimes|string|max:255',
                'adresse' => 'sometimes|string|max:255',
                'telephone' => 'sometimes|string|max:20|unique:users,telephone,' . $user->id,
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'sometimes|string|min:8|confirmed',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = $request->only(['nom', 'prenom', 'adresse', 'telephone', 'email']);

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('image')) {
                if ($user->image) {
                    Storage::disk('public')->delete($user->image);
                }
                $imagePath = $request->file('image')->store('users', 'public');
                $updateData['image'] = $imagePath;
            }

            $user->update($updateData);

            return response()->json([
                'status' => 'success',
                'message' => 'Profil mis à jour avec succès',
                'data' => [
                    'user' => $user->fresh()->only(['id', 'nom', 'prenom', 'email', 'role', 'telephone', 'adresse', 'est_actif', 'image'])
                ]
            ], 200);

        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token expiré'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token invalide'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token manquant'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du profil',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/users/{userId}/toggle-status",
     *     tags={"Authentification"},
     *     summary="Activer ou désactiver un utilisateur (admin)",
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statut utilisateur mis à jour"
     *     )
     * )
     */
    public function toggleUserStatus(Request $request, $userId)
    {
        try {
            $authUser = JWTAuth::parseToken()->authenticate();

            if (!$authUser->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $user = User::findOrFail($userId);

            if ($user->est_actif) {
                $user->desactiver();
            } else {
                $user->activer();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Statut utilisateur mis à jour',
                'data' => [
                    'user' => $user->only(['id', 'nom', 'email', 'est_actif'])
                ]
            ], 200);

        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token expiré'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token invalide'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token manquant'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du statut',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/invalidate-all-tokens",
     *     tags={"Authentification"},
     *     summary="Invalider tous les tokens de l'utilisateur connecté",
     *     @OA\Response(
     *         response=200,
     *         description="Tous les tokens ont été invalidés"
     *     )
     * )
     */
    public function invalidateAllTokens()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            // Invalider tous les tokens de l'utilisateur
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'status' => 'success',
                'message' => 'Tous les tokens ont été invalidés'
            ], 200);

        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token expiré'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token invalide'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token manquant'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'invalidation des tokens',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }
}
