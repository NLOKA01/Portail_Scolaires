<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Matiere;
use App\Services\MatiereService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(
 *     name="Matières",
 *     description="Gestion des matières"
 * )
 */
class MatiereController extends Controller
{
    protected $matiereService;

    public function __construct(MatiereService $matiereService)
    {
        $this->matiereService = $matiereService;
    }

    /**
     * @OA\Get(
     *     path="/api/matieres",
     *     tags={"Matières"},
     *     summary="Liste des matières",
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
            if (!$user->isAdmin() && !$user->isEnseignant()) {
                return response()->json(['status' => 'error', 'message' => 'Accès non autorisé'], 403);
            }
            $matieres = $this->matiereService->listMatieres();
            return response()->json(['status' => 'success', 'data' => $matieres], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la récupération des matières', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/matieres",
     *     tags={"Matières"},
     *     summary="Créer une nouvelle matière",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom","niveau"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Matière créée"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin()) {
                return response()->json(['status' => 'error', 'message' => 'Seul un administrateur peut créer une matière'], 403);
            }
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'description' => 'nullable|string',
                'niveau' => 'required|in:college,lycee,tous',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }
            $matiere = $this->matiereService->createMatiere($request->all());
            return response()->json(['status' => 'success', 'message' => 'Matière créée avec succès', 'data' => $matiere], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la création de la matière', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/matieres/{id}",
     *     tags={"Matières"},
     *     summary="Afficher une matière spécifique",
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
     *         description="Matière non trouvée"
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $matiere = $this->matiereService->getMatiereById($id);
            if (!$matiere) {
                return response()->json(['status' => 'error', 'message' => 'Matière non trouvée'], 404);
            }
            return response()->json(['status' => 'success', 'data' => $matiere], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la récupération de la matière', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/matieres/{id}",
     *     tags={"Matières"},
     *     summary="Mettre à jour une matière",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom","niveau"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Matière mise à jour"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin()) {
                return response()->json(['status' => 'error', 'message' => 'Seul un administrateur peut modifier une matière'], 403);
            }
            $validator = Validator::make($request->all(), [
                'nom' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'niveau' => 'sometimes|in:college,lycee,tous',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }
            $matiere = $this->matiereService->updateMatiere($id, $request->all());
            return response()->json(['status' => 'success', 'message' => 'Matière mise à jour avec succès', 'data' => $matiere], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la mise à jour de la matière', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/matieres/{id}",
     *     tags={"Matières"},
     *     summary="Supprimer une matière",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Matière supprimée"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin()) {
                return response()->json(['status' => 'error', 'message' => 'Seul un administrateur peut supprimer une matière'], 403);
            }
            $this->matiereService->deleteMatiere($id);
            return response()->json(['status' => 'success', 'message' => 'Matière supprimée avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la suppression de la matière', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }
}
