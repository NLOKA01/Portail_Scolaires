<?php

namespace App\Services;

use App\Models\DocumentEleve;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;

class DocumentService
{
    public function getAllDocumentsWithRelations()
    {
        return DocumentEleve::with(['eleve.user'])->get();
    }

    public function getDocumentByIdWithRelations($id)
    {
        return DocumentEleve::with(['eleve.user'])->find($id);
    }

    public function createDocument(array $data)
    {
        $validator = Validator::make($data, [
            'eleve_id' => 'required|exists:eleves,id',
            'type_document' => 'required|in:extrait_naissance,certificat_scolarite,photo,certificat_medical',
            'chemin_fichier' => 'required|string',
            'date_depot' => 'required|date',
            'est_valide' => 'boolean',
        ]);
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }
        return DocumentEleve::create($validator->validated());
    }

    public function updateDocument($id, array $data)
    {
        $document = DocumentEleve::find($id);
        if (!$document) {
            throw new Exception('Document non trouvé');
        }
        $validator = Validator::make($data, [
            'est_valide' => 'sometimes|boolean',
            'type_document' => 'sometimes|in:extrait_naissance,certificat_scolarite,photo,certificat_medical',
        ]);
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }
        $document->update($validator->validated());
        return $document;
    }

    public function deleteDocument($id)
    {
        $document = DocumentEleve::find($id);
        if (!$document) {
            throw new Exception('Document non trouvé');
        }
        if ($document->chemin_fichier && Storage::disk('public')->exists($document->chemin_fichier)) {
            Storage::disk('public')->delete($document->chemin_fichier);
        }
        $document->delete();
    }

    public function getDocumentsByEleveIdWithRelations($eleveId)
    {
        return DocumentEleve::where('eleve_id', $eleveId)
            ->with(['eleve.user'])
            ->get();
    }
} 