<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Eleve;
use App\Models\User;
use App\Services\EleveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(
 *     name="Élèves",
 *     description="Gestion des élèves"
 * )
 */
class EleveController extends Controller
{
    protected $eleveService;

    public function __construct(EleveService $eleveService)
    {
        $this->eleveService = $eleveService;
    }

    /**
     * @OA\Get(
     *     path="/api/eleves",
     *     tags={"Élèves"},
     *     summary="Liste des élèves",
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
            $eleves = $this->eleveService->listEleves();
            return response()->json(['status' => 'success', 'data' => $eleves], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la récupération des élèves', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/eleves",
     *     tags={"Élèves"},
     *     summary="Créer un nouvel élève",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom","prenom","adresse","telephone","email","date_naissance","lieu_naissance","sexe","classe_id","parent_nom","parent_prenom","parent_adresse","parent_telephone","parent_email"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Élève créé"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin()) {
                return response()->json(['status' => 'error', 'message' => 'Seul un administrateur peut créer un élève'], 403);
            }
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'adresse' => 'required|string|max:255',
                'telephone' => 'required|string|max:20|unique:users,telephone',
                'email' => 'required|string|email|max:255|unique:users,email',
                'date_naissance' => 'required|date',
                'lieu_naissance' => 'required|string|max:255',
                'sexe' => 'required|in:M,F',
                'classe_id' => 'required|exists:classes,id',
                'parent_nom' => 'required|string|max:255',
                'parent_prenom' => 'required|string|max:255',
                'parent_adresse' => 'required|string|max:255',
                'parent_telephone' => 'required|string|max:20',
                'parent_email' => 'required|string|email|max:255',
                'parent_profession' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }
            $eleveData = $request->only(['nom','prenom','adresse','telephone','email','date_naissance','lieu_naissance','sexe','classe_id']);
            $parentData = $request->only(['parent_nom','parent_prenom','parent_adresse','parent_telephone','parent_email','parent_profession']);
            $parentData = [
                'nom' => $parentData['parent_nom'],
                'prenom' => $parentData['parent_prenom'],
                'adresse' => $parentData['parent_adresse'],
                'telephone' => $parentData['parent_telephone'],
                'email' => $parentData['parent_email'],
                'profession' => $parentData['parent_profession'] ?? null,
            ];
            $eleve = $this->eleveService->inscrireEleve($eleveData, $parentData);
            return response()->json(['status' => 'success', 'message' => 'Élève créé avec succès', 'data' => $eleve->load(['user', 'classe', 'parent.user'])], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la création de l\'élève', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/eleves/{id}",
     *     tags={"Élèves"},
     *     summary="Afficher un élève spécifique",
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
     *         description="Élève non trouvé"
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $eleve = $this->eleveService->getEleveById($id);
            if (!$user->isAdmin() && !$user->isEnseignant() && ($user->isParent() && $user->parentUser->id !== $eleve->parent_id)) {
                return response()->json(['status' => 'error', 'message' => 'Accès non autorisé'], 403);
            }
            return response()->json(['status' => 'success', 'data' => $eleve], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la récupération de l\'élève', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/eleves/{id}",
     *     tags={"Élèves"},
     *     summary="Mettre à jour un élève",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom","prenom","adresse","telephone","email","date_naissance","lieu_naissance","sexe","classe_id"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Élève mis à jour"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin()) {
                return response()->json(['status' => 'error', 'message' => 'Seul un administrateur peut modifier un élève'], 403);
            }
            $validator = Validator::make($request->all(), [
                'nom' => 'sometimes|string|max:255',
                'prenom' => 'sometimes|string|max:255',
                'adresse' => 'sometimes|string|max:255',
                'telephone' => 'sometimes|string|max:20|unique:users,telephone',
                'email' => 'sometimes|string|email|max:255|unique:users,email',
                'date_naissance' => 'sometimes|date',
                'lieu_naissance' => 'sometimes|string|max:255',
                'sexe' => 'sometimes|in:M,F',
                'classe_id' => 'sometimes|exists:classes,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
            }
            $eleve = $this->eleveService->updateEleve($id, $request->all());
            return response()->json(['status' => 'success', 'message' => 'Élève mis à jour avec succès', 'data' => $eleve], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la mise à jour de l\'élève', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/eleves/{id}",
     *     tags={"Élèves"},
     *     summary="Supprimer un élève",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Élève supprimé"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user->isAdmin()) {
                return response()->json(['status' => 'error', 'message' => 'Seul un administrateur peut supprimer un élève'], 403);
            }
            $this->eleveService->deleteEleve($id);
            return response()->json(['status' => 'success', 'message' => 'Élève supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la suppression de l\'élève', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    
    public function getEleveNotes(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $eleve = $this->eleveService->getEleveById($id);
            if (!$user->isAdmin() && !$user->isEnseignant() && ($user->isParent() && $user->parentUser->id !== $eleve->parent_id)) {
                return response()->json(['status' => 'error', 'message' => 'Accès non autorisé'], 403);
            }
            $notes = $this->eleveService->getNotesForEleve($id);
            return response()->json(['status' => 'success', 'data' => $notes], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la récupération des notes de l\'élève', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    public function getEleveBulletins(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $eleve = $this->eleveService->getEleveById($id);
            if (!$user->isAdmin() && !$user->isEnseignant() && ($user->isParent() && $user->parentUser->id !== $eleve->parent_id)) {
                return response()->json(['status' => 'error', 'message' => 'Accès non autorisé'], 403);
            }
            $bulletins = $this->eleveService->getBulletinsForEleve($id);
            return response()->json(['status' => 'success', 'data' => $bulletins], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la récupération des bulletins', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    public function getEleveDocuments(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $eleve = $this->eleveService->getEleveById($id);
            if (!$user->isAdmin() && !$user->isEnseignant() && ($user->isParent() && $user->parentUser->id !== $eleve->parent_id)) {
                return response()->json(['status' => 'error', 'message' => 'Accès non autorisé'], 403);
            }
            $documents = $this->eleveService->getDocumentsForEleve($id);
            return response()->json(['status' => 'success', 'data' => $documents], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la récupération des documents', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }

    public function getEleveAbsences(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $eleve = $this->eleveService->getEleveById($id);
            if (!$user->isAdmin() && !$user->isEnseignant() && ($user->isParent() && $user->parentUser->id !== $eleve->parent_id)) {
                return response()->json(['status' => 'error', 'message' => 'Accès non autorisé'], 403);
            }
            $absences = $this->eleveService->getAbsencesForEleve($id);
            return response()->json(['status' => 'success', 'data' => $absences], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de la récupération des absences', 'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'], 500);
        }
    }
}
