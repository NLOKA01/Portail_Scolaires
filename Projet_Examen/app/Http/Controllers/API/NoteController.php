<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Eleve;
use App\Services\NoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(
 *     name="Notes",
 *     description="Gestion des notes"
 * )
 */
class NoteController extends Controller
{
    protected $noteService;

    public function __construct(NoteService $noteService)
    {
        $this->noteService = $noteService;
    }

    /**
     * @OA\Get(
     *     path="/api/notes",
     *     tags={"Notes"},
     *     summary="Liste des notes",
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

            $notes = $this->noteService->getAllNotesWithRelations();

            return response()->json([
                'status' => 'success',
                'data' => $notes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des notes',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/notes",
     *     tags={"Notes"},
     *     summary="Créer une nouvelle note",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"eleve_id","matiere_id","enseignant_id","valeur"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Note créée"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user->isEnseignant()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Seul un enseignant peut saisir une note'
                ], 403);
            }

            $data = $request->all();
            $data['enseignant_id'] = $user->enseignant->id;

            $note = $this->noteService->saisirNote($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Note saisie avec succès',
                'data' => $note->load(['eleve.user', 'matiere', 'enseignant.user'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la saisie de la note',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/notes/{id}",
     *     tags={"Notes"},
     *     summary="Afficher une note spécifique",
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
     *         description="Note non trouvée"
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            $note = $this->noteService->getNoteByIdWithRelations($id);

            if (!$note) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Note non trouvée'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $note
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération de la note',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/notes/{id}",
     *     tags={"Notes"},
     *     summary="Mettre à jour une note",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"valeur"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Note mise à jour"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user->isEnseignant()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Seul un enseignant peut modifier une note'
                ], 403);
            }

            $note = $this->noteService->updateNote($id, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Note mise à jour avec succès',
                'data' => $note->load(['eleve.user', 'matiere', 'enseignant.user'])
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour de la note',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/notes/{id}",
     *     tags={"Notes"},
     *     summary="Supprimer une note",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Note supprimée"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user->isEnseignant()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Seul un enseignant peut supprimer une note'
                ], 403);
            }

            $this->noteService->deleteNote($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Note supprimée avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression de la note',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/eleves/{eleve}/notes",
     *     tags={"Notes"},
     *     summary="Liste des notes d'un élève",
     *     @OA\Parameter(
     *         name="eleve",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès"
     *     )
     * )
     */
    public function getEleveNotes(string $eleveId)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            $eleve = Eleve::find($eleveId);
            if (!$eleve) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Élève non trouvé'
                ], 404);
            }

            // Vérifier les permissions
            if (!$user->isAdmin() && !$user->isEnseignant() && 
                ($user->isParent() && $user->parentUser->id !== $eleve->parent_id)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            $notes = $this->noteService->getNotesByEleveIdWithRelations($eleveId);

            return response()->json([
                'status' => 'success',
                'data' => $notes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des notes',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Saisir une note pour un élève spécifique
     */
    public function storeEleveNote(Request $request, string $eleveId)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user->isEnseignant()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Seul un enseignant peut saisir une note'
                ], 403);
            }

            $eleve = Eleve::find($eleveId);
            if (!$eleve) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Élève non trouvé'
                ], 404);
            }

            $data = $request->all();
            $data['eleve_id'] = $eleveId;
            $data['enseignant_id'] = $user->enseignant->id;

            $note = $this->noteService->saisirNote($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Note saisie avec succès',
                'data' => $note->load(['eleve.user', 'matiere', 'enseignant.user'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la saisie de la note',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }
}
