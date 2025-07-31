<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Enseignant;
use App\Services\EnseignantService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(
 *     name="Enseignants",
 *     description="Gestion des enseignants"
 * )
 */
class EnseignantController extends Controller
{
    protected $enseignantService;
    protected $userService;

    public function __construct(EnseignantService $enseignantService, UserService $userService)
    {
        $this->enseignantService = $enseignantService;
        $this->userService = $userService;
    }

    /**
     * @OA\Get(
     *     path="/api/enseignants",
     *     tags={"Enseignants"},
     *     summary="Liste des enseignants",
     *     @OA\Response(
     *         response=200,
     *         description="Succès"
     *     )
     * )
     */
    public function index()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin()) {
                return response()->json(['status' => 'error', 'message' => 'Accès non autorisé'], 403);
            }
            $enseignants = $this->enseignantService->listEnseignants();
            return response()->json(['status' => 'success', 'data' => $enseignants], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la récupération des enseignants', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/enseignants",
     *     tags={"Enseignants"},
     *     summary="Créer un nouvel enseignant",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom","prenom","adresse","telephone","email","password","specialite","date_embauche","numero_identifiant"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Enseignant créé"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin()) {
                return response()->json(['status' => 'error', 'message' => 'Seul un administrateur peut créer un enseignant'], 403);
            }
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'adresse' => 'required|string|max:255',
                'telephone' => 'required|string|max:20|unique:users,telephone',
                'email' => 'required|string|email|max:255|unique:users,email',
                'specialite' => 'required|string|max:255',
                'date_embauche' => 'required|date',
                'numero_identifiant' => 'required|string|max:50|unique:enseignants,numero_identifiant',
                'classe_ids' => 'nullable|array',
                'classe_ids.*' => 'exists:classes,id',
                'matiere_ids' => 'nullable|array',
                'matiere_ids.*' => 'exists:matieres,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }
            $enseignant = $this->enseignantService->createEnseignant($request->all());
            return response()->json(['status' => 'success', 'message' => 'Enseignant créé avec succès', 'data' => $enseignant], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la création de l\'enseignant', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/enseignants/{id}",
     *     tags={"Enseignants"},
     *     summary="Afficher un enseignant spécifique",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enseignant non trouvé"
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $enseignant = $this->enseignantService->getEnseignantById($id);
            if (!$enseignant) {
                return response()->json(['status' => 'error', 'message' => 'Enseignant non trouvé'], 404);
            }
            return response()->json(['status' => 'success', 'data' => $enseignant], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la récupération de l\'enseignant', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/enseignants/{id}",
     *     tags={"Enseignants"},
     *     summary="Mettre à jour un enseignant",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom","prenom","adresse","telephone","email","specialite","date_embauche","numero_identifiant"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enseignant mis à jour"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin()) {
                return response()->json(['status' => 'error', 'message' => 'Seul un administrateur peut modifier un enseignant'], 403);
            }
            $validator = Validator::make($request->all(), [
                'nom' => 'sometimes|string|max:255',
                'prenom' => 'sometimes|string|max:255',
                'adresse' => 'sometimes|string|max:255',
                'telephone' => 'sometimes|string|max:20|unique:users,telephone',
                'email' => 'sometimes|string|email|max:255|unique:users,email',
                'specialite' => 'sometimes|string|max:255',
                'date_embauche' => 'sometimes|date',
                'numero_identifiant' => 'sometimes|string|max:50|unique:enseignants,numero_identifiant',
                'classe_ids' => 'nullable|array',
                'classe_ids.*' => 'exists:classes,id',
                'matiere_ids' => 'nullable|array',
                'matiere_ids.*' => 'exists:matieres,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }
            $enseignant = $this->enseignantService->updateEnseignant($id, $request->all());
            return response()->json(['status' => 'success', 'message' => 'Enseignant mis à jour avec succès', 'data' => $enseignant], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la mise à jour de l\'enseignant', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/enseignants/{id}",
     *     tags={"Enseignants"},
     *     summary="Supprimer un enseignant",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Enseignant supprimé"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin()) {
                return response()->json(['status' => 'error', 'message' => 'Seul un administrateur peut supprimer un enseignant'], 403);
            }
            $this->enseignantService->deleteEnseignant($id);
            return response()->json(['status' => 'success', 'message' => 'Enseignant supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la suppression de l\'enseignant', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }
}
