<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DocumentEleve;
use App\Models\Eleve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\DocumentService;

/**
 * @OA\Tag(
 *     name="Documents",
 *     description="Gestion des documents"
 * )
 */
class DocumentController extends Controller
{
    protected $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * @OA\Get(
     *     path="/api/documents",
     *     tags={"Documents"},
     *     summary="Liste des documents",
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

            $documents = $this->documentService->getAllDocumentsWithRelations();

            return response()->json([
                'status' => 'success',
                'data' => $documents
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des documents',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/documents",
     *     tags={"Documents"},
     *     summary="Uploader un nouveau document",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"eleve_id","fichier"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Document uploadé"
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
                    'message' => 'Seul un administrateur ou enseignant peut uploader un document'
                ], 403);
            }

            $file = $request->file('document');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents', $filename, 'public');
            $data = $request->all();
            $data['chemin_fichier'] = $path;
            $data['date_depot'] = now();
            $data['est_valide'] = false;
            $document = $this->documentService->createDocument($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Document uploadé avec succès',
                'data' => $document->load(['eleve.user'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'upload du document',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/documents/{id}",
     *     tags={"Documents"},
     *     summary="Afficher un document spécifique",
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
     *         description="Document non trouvé"
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            $document = $this->documentService->getDocumentByIdWithRelations($id);

            if (!$document) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Document non trouvé'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $document
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération du document',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/documents/{id}",
     *     tags={"Documents"},
     *     summary="Mettre à jour un document",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"fichier"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document mis à jour"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Seul un administrateur peut modifier un document'
                ], 403);
            }

            $document = $this->documentService->updateDocument($id, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Document mis à jour avec succès',
                'data' => $document->load(['eleve.user'])
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du document',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/documents/{id}",
     *     tags={"Documents"},
     *     summary="Supprimer un document",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Document supprimé"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Seul un administrateur peut supprimer un document'
                ], 403);
            }

            $this->documentService->deleteDocument($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Document supprimé avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression du document',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/eleves/{eleve}/documents",
     *     tags={"Documents"},
     *     summary="Liste des documents d'un élève",
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
    /**
     * Obtenir les documents d'un élève
     */
    public function getEleveDocuments(string $eleveId)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            $eleve = \App\Models\Eleve::find($eleveId);
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

            $documents = $this->documentService->getDocumentsByEleveIdWithRelations($eleveId);

            return response()->json([
                'status' => 'success',
                'data' => $documents
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des documents',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Uploader un document pour un élève spécifique
     */
    public function uploadDocument(Request $request, string $eleveId)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user->isAdmin() && !$user->isEnseignant()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Seul un administrateur ou enseignant peut uploader un document'
                ], 403);
            }

            $eleve = \App\Models\Eleve::find($eleveId);
            if (!$eleve) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Élève non trouvé'
                ], 404);
            }

            $file = $request->file('document');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents', $filename, 'public');
            $data = $request->all();
            $data['eleve_id'] = $eleveId;
            $data['chemin_fichier'] = $path;
            $data['date_depot'] = now();
            $data['est_valide'] = false;
            $document = $this->documentService->createDocument($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Document uploadé avec succès',
                'data' => $document->load(['eleve.user'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'upload du document',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }
}
