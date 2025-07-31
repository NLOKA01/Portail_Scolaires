<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Services\ClasseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(
 *     name="Classes",
 *     description="Gestion des classes"
 * )
 */
class ClasseController extends Controller
{
    protected $classeService;

    public function __construct(ClasseService $classeService)
    {
        $this->classeService = $classeService;
    }

    /**
     * @OA\Get(
     *     path="/api/classes",
     *     tags={"Classes"},
     *     summary="Liste des classes",
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
            $classes = $this->classeService->listClasses();
            return response()->json(['status' => 'success', 'data' => $classes], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la récupération des classes', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/classes",
     *     tags={"Classes"},
     *     summary="Créer une nouvelle classe",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom","niveau","capacite"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Classe créée"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin()) {
                return response()->json(['status' => 'error', 'message' => 'Seul un administrateur peut créer une classe'], 403);
            }
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'niveau' => 'required|string|max:50',
                'capacite' => 'required|integer|min:1|max:50',
                'description' => 'nullable|string|max:500',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }
            $classe = $this->classeService->createClasse($request->all());
            return response()->json(['status' => 'success', 'message' => 'Classe créée avec succès', 'data' => $classe], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la création de la classe', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/classes/{id}",
     *     tags={"Classes"},
     *     summary="Afficher une classe spécifique",
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
     *         description="Classe non trouvée"
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $classe = $this->classeService->getClasseById($id);
            if (!$classe) {
                return response()->json(['status' => 'error', 'message' => 'Classe non trouvée'], 404);
            }
            return response()->json(['status' => 'success', 'data' => $classe], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la récupération de la classe', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/classes/{id}",
     *     tags={"Classes"},
     *     summary="Mettre à jour une classe",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom","niveau","capacite"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Classe mise à jour"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin()) {
                return response()->json(['status' => 'error', 'message' => 'Seul un administrateur peut modifier une classe'], 403);
            }
            $validator = Validator::make($request->all(), [
                'nom' => 'sometimes|string|max:255',
                'niveau' => 'sometimes|string|max:50',
                'capacite' => 'sometimes|integer|min:1|max:50',
                'description' => 'nullable|string|max:500',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }
            $classe = $this->classeService->updateClasse($id, $request->all());
            return response()->json(['status' => 'success', 'message' => 'Classe mise à jour avec succès', 'data' => $classe], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la mise à jour de la classe', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/classes/{id}",
     *     tags={"Classes"},
     *     summary="Supprimer une classe",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Classe supprimée"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin()) {
                return response()->json(['status' => 'error', 'message' => 'Seul un administrateur peut supprimer une classe'], 403);
            }
            $this->classeService->deleteClasse($id);
            return response()->json(['status' => 'success', 'message' => 'Classe supprimée avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la suppression de la classe', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }
}
