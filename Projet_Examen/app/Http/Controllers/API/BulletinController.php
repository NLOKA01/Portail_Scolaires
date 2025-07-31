<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bulletin;
use App\Models\Eleve;
use App\Services\BulletinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(
 *     name="Bulletins",
 *     description="Gestion des bulletins"
 * )
 */
class BulletinController extends Controller
{
    protected $bulletinService;

    public function __construct(BulletinService $bulletinService)
    {
        $this->bulletinService = $bulletinService;
    }

    /**
     * @OA\Get(
     *     path="/api/bulletins",
     *     tags={"Bulletins"},
     *     summary="Liste des bulletins",
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

            $bulletins = $this->bulletinService->getAllBulletinsWithRelations();

            return response()->json([
                'status' => 'success',
                'data' => $bulletins
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des bulletins',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/bulletins",
     *     tags={"Bulletins"},
     *     summary="Générer un nouveau bulletin",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"eleve_id","periode"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bulletin généré"
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
                    'message' => 'Seul un administrateur ou enseignant peut générer un bulletin'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'eleve_id' => 'required|exists:eleves,id',
                'periode' => 'required|in:trimestre_1,trimestre_2,trimestre_3,semestre_1,semestre_2',
                'annee_scolaire' => 'nullable|string|max:9',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $bulletin = $this->bulletinService->genererBulletin(
                $request->eleve_id,
                $request->periode,
                $request->annee_scolaire
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Bulletin généré avec succès',
                'data' => $bulletin
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la génération du bulletin',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/bulletins/{id}",
     *     tags={"Bulletins"},
     *     summary="Afficher un bulletin spécifique",
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
     *         description="Bulletin non trouvé"
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            $bulletin = $this->bulletinService->getBulletinByIdWithRelations($id);

            if (!$bulletin) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bulletin non trouvé'
                ], 404);
            }

            // Vérifier les permissions
            if (!$user->isAdmin() && !$user->isEnseignant() && 
                ($user->isParent() && $user->parentUser->id !== $bulletin->eleve->parent_id)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'data' => $bulletin
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération du bulletin',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/bulletins/{id}",
     *     tags={"Bulletins"},
     *     summary="Mettre à jour un bulletin",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"periode"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bulletin mis à jour"
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
                    'message' => 'Seul un administrateur peut modifier un bulletin'
                ], 403);
            }

            $bulletin = $this->bulletinService->updateBulletin($id, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Bulletin mis à jour avec succès',
                'data' => $bulletin->load(['eleve.user', 'classe'])
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du bulletin',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/bulletins/{id}",
     *     tags={"Bulletins"},
     *     summary="Supprimer un bulletin",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Bulletin supprimé"
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
                    'message' => 'Seul un administrateur peut supprimer un bulletin'
                ], 403);
            }

            $this->bulletinService->deleteBulletin($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Bulletin supprimé avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression du bulletin',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/eleves/{eleve}/bulletins",
     *     tags={"Bulletins"},
     *     summary="Liste des bulletins d'un élève",
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
    public function getEleveBulletins(string $eleveId)
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

            $bulletins = $this->bulletinService->getBulletinsEleve($eleveId);

            return response()->json([
                'status' => 'success',
                'data' => $bulletins
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des bulletins',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Générer un bulletin pour un élève spécifique
     */
    public function generateBulletin(Request $request, string $eleveId)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user->isAdmin() && !$user->isEnseignant()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Seul un administrateur ou enseignant peut générer un bulletin'
                ], 403);
            }

            $eleve = Eleve::find($eleveId);
            if (!$eleve) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Élève non trouvé'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'periode' => 'required|in:trimestre_1,trimestre_2,trimestre_3,semestre_1,semestre_2',
                'annee_scolaire' => 'nullable|string|max:9',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $bulletin = $this->bulletinService->genererBulletin(
                $eleveId,
                $request->periode,
                $request->annee_scolaire
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Bulletin généré avec succès',
                'data' => $bulletin
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la génération du bulletin',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Télécharger le PDF d'un bulletin
     */
    public function downloadPDF(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            $bulletin = Bulletin::with(['eleve.user', 'classe'])->find($id);
            if (!$bulletin) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bulletin non trouvé'
                ], 404);
            }

            // Vérifier les permissions
            if (!$user->isAdmin() && !$user->isEnseignant() && 
                ($user->isParent() && $user->parentUser->id !== $bulletin->eleve->parent_id)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            if (!$bulletin->pdf_path || !Storage::exists('public/' . $bulletin->pdf_path)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'PDF non disponible'
                ], 404);
            }

            $path = Storage::path('public/' . $bulletin->pdf_path);
            $filename = 'bulletin_' . $bulletin->eleve->user->nom . '_' . $bulletin->periode . '.pdf';

            return response()->download($path, $filename);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors du téléchargement',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    /**
     * Télécharger tous les bulletins d'une classe en ZIP
     */
    public function downloadClassBulletins(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Seul un administrateur peut télécharger les bulletins groupés'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'classe_id' => 'required|exists:classes,id',
                'periode' => 'required|in:trimestre_1,trimestre_2,trimestre_3,semestre_1,semestre_2',
                'annee_scolaire' => 'nullable|string|max:9',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $bulletins = Bulletin::where('classe_id', $request->classe_id)
                ->where('periode', $request->periode)
                ->where('annee_scolaire', $request->annee_scolaire ?? date('Y') . '-' . (date('Y') + 1))
                ->with(['eleve.user'])
                ->get();

            if ($bulletins->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aucun bulletin trouvé pour cette classe et période'
                ], 404);
            }

            // Créer un ZIP temporaire
            $zip = new \ZipArchive();
            $zipName = 'bulletins_classe_' . $request->classe_id . '_' . $request->periode . '.zip';
            $zipPath = storage_path('app/temp/' . $zipName);

            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                foreach ($bulletins as $bulletin) {
                    if ($bulletin->pdf_path && Storage::exists('public/' . $bulletin->pdf_path)) {
                        $pdfPath = Storage::path('public/' . $bulletin->pdf_path);
                        $filename = 'bulletin_' . $bulletin->eleve->user->nom . '_' . $bulletin->eleve->user->prenom . '.pdf';
                        $zip->addFile($pdfPath, $filename);
                    }
                }
                $zip->close();

                return response()->download($zipPath, $zipName)->deleteFileAfterSend();

            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Erreur lors de la création du fichier ZIP'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors du téléchargement groupé',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }
}
