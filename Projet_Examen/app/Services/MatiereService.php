<?php

namespace App\Services;

use App\Models\Matiere;
use App\Models\Classe;
use Illuminate\Support\Facades\Validator;
use Exception;

class MatiereService
{
    /**
     * Créer une nouvelle matière
     */
    public function createMatiere(array $data): Matiere
    {
        $validator = Validator::make($data, [
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'niveau' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        return Matiere::create($validator->validated());
    }

    /**
     * Mettre à jour une matière existante
     */
    public function updateMatiere(Matiere $matiere, array $data): Matiere
    {
        $validator = Validator::make($data, [
            'nom' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'niveau' => 'sometimes|required|string|max:50',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        $matiere->update($validator->validated());
        return $matiere;
    }

    /**
     * Affecter une matière à une classe avec un coefficient
     */
    public function affecterMatiereClasse($matiereId, $classeId, $coefficient)
    {
        $validator = Validator::make([
            'matiere_id' => $matiereId,
            'classe_id' => $classeId,
            'coefficient' => $coefficient,
        ], [
            'matiere_id' => 'required|exists:matieres,id',
            'classe_id' => 'required|exists:classes,id',
            'coefficient' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        $classe = Classe::find($classeId);
        $classe->matieres()->attach($matiereId, ['coefficient' => $coefficient]);

        return $classe->matieres()->where('matiere_id', $matiereId)->first();
    }

    /**
     * Récupérer les matières affectées à une classe
     */
    public function getMatieresClasse($classeId)
    {
        $classe = Classe::find($classeId);
        return $classe ? $classe->matieres()->withPivot('coefficient')->get() : collect();
    }

    public function listMatieres()
    {
        return \App\Models\Matiere::with(['classes', 'enseignants'])->get();
    }
    public function createMatiere(array $data)
    {
        return \DB::transaction(function () use ($data) {
            $matiere = \App\Models\Matiere::create($data);
            return $matiere->load(['classes', 'enseignants']);
        });
    }
    public function getMatiereById($id)
    {
        return \App\Models\Matiere::with(['classes', 'enseignants'])->findOrFail($id);
    }
    public function updateMatiere($id, array $data)
    {
        return \DB::transaction(function () use ($id, $data) {
            $matiere = \App\Models\Matiere::findOrFail($id);
            $matiere->update($data);
            return $matiere->load(['classes', 'enseignants']);
        });
    }
    public function deleteMatiere($id)
    {
        return \DB::transaction(function () use ($id) {
            $matiere = \App\Models\Matiere::findOrFail($id);
            $matiere->delete();
            return true;
        });
    }
    public function affecterEnseignants($matiereId, $enseignantIds)
    {
        $matiere = \App\Models\Matiere::findOrFail($matiereId);
        $matiere->enseignants()->sync($enseignantIds);
        return $matiere->load('enseignants');
    }
    public function affecterClasses($matiereId, $classeIds)
    {
        $matiere = \App\Models\Matiere::findOrFail($matiereId);
        $matiere->classes()->sync($classeIds);
        return $matiere->load('classes');
    }
}
