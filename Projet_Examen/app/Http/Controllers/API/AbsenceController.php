<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Eleve;
use App\Services\AbsenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(
 *     name="Absences",
 *     description="Gestion des absences"
 * )
 */
class AbsenceController extends Controller
{
    protected $absenceService;

    public function __construct(AbsenceService $absenceService)
    {
        $this->absenceService = $absenceService;
    }

    /**
     * @OA\Get(
     *     path="/api/absences",
     *     tags={"Absences"},
     *     summary="Liste des absences",
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
                return response()->json([
                    'status' => 'error',
                    'message' => 'Accès non autorisé'
                ], 403);
            }
            $absences = $this->absenceService->getAllAbsencesWithRelations();
            return response()->json([
                'status' => 'success',
                'data' => $absences
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des absences',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/absences",
     *     tags={"Absences"},
     *     summary="Créer une nouvelle absence",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"eleve_id","date","motif"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Absence créée"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin() && !$user->isEnseignant()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Seul un administrateur ou enseignant peut créer une absence'
                ], 403);
            }
            $absence = $this->absenceService->enregistrerAbsence($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Absence créée avec succès',
                'data' => $absence->load(['eleve.user'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création de l\'absence',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/absences/{id}",
     *     tags={"Absences"},
     *     summary="Afficher une absence spécifique",
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
     *         description="Absence non trouvée"
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin() && !$user->isEnseignant()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Accès non autorisé'
                ], 403);
            }
            $absence = $this->absenceService->getAbsenceByIdWithRelations($id);
            if (!$absence) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Absence non trouvée'
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'data' => $absence
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération de l\'absence',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/absences/{id}",
     *     tags={"Absences"},
     *     summary="Mettre à jour une absence",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"date","motif"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Absence mise à jour"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin() && !$user->isEnseignant()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Seul un administrateur ou enseignant peut modifier une absence'
                ], 403);
            }
            $absence = $this->absenceService->updateAbsence($id, $request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Absence modifiée avec succès',
                'data' => $absence->load(['eleve.user'])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la modification de l\'absence',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/absences/{id}",
     *     tags={"Absences"},
     *     summary="Supprimer une absence",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Absence supprimée"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin() && !$user->isEnseignant()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Seul un administrateur ou enseignant peut supprimer une absence'
                ], 403);
            }
            $this->absenceService->deleteAbsence($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Absence supprimée avec succès'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression de l\'absence',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Obtenir les absences d'un élève
     */
    public function getEleveAbsences(string $eleveId)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin() && !$user->isEnseignant() && (!$user->isParent() || $user->parentUser->id != Eleve::find($eleveId)->parent_id)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Accès non autorisé'
                ], 403);
            }
            $absences = $this->absenceService->getAbsencesByEleveIdWithRelations($eleveId);
            return response()->json([
                'status' => 'success',
                'data' => $absences
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des absences de l\'élève',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }
} 