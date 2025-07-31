<?php
namespace App\Services;

use App\Models\Classe;
use App\Models\Matiere;

class ClasseService
{
    /**
     * Affecter des matières à une classe avec coefficients
     */
    public function affecterMatieres(Classe $classe, array $matieresAvecCoefficients)
    {
        // Format: [['matiere_id' => 1, 'coefficient' => 3], ...]
        $pivotData = [];

        foreach ($matieresAvecCoefficients as $data) {
            $pivotData[$data['matiere_id']] = ['coefficient' => $data['coefficient']];
        }

        $classe->matieres()->sync($pivotData);

        return $classe->load('matieres');
    }

    /**
     * Affectation automatique des matières selon le niveau
     */
    public function affectationAutomatiqueMatieres(Classe $classe)
    {
        $niveau = $classe->niveau;
        $isCollege = in_array($niveau, ['6ème', '5ème', '4ème', '3ème']);
        $isLycee = in_array($niveau, ['2nde', '1ère', 'Terminale']);

        $matieres = collect();

        if ($isCollege) {
            $matieres = Matiere::whereIn('niveau', ['college', 'tous'])->get();
        } elseif ($isLycee) {
            $matieres = Matiere::whereIn('niveau', ['lycee', 'tous'])->get();
        }

        $matieresAvecCoefficients = [];

        foreach ($matieres as $matiere) {
            $coefficient = $this->getCoefficient($matiere->nom, $niveau);
            $matieresAvecCoefficients[] = [
                'matiere_id' => $matiere->id,
                'coefficient' => $coefficient
            ];
        }

        return $this->affecterMatieres($classe, $matieresAvecCoefficients);
    }

    /**
     * Déterminer le coefficient selon la matière et le niveau
     */
    private function getCoefficient($nomMatiere, $niveau)
    {
        $coefficients = [
            'Mathématiques' => 4,
            'Français' => 4,
            'Anglais' => 3,
            'Histoire-Géographie' => 3,
            'Sciences Physiques' => 3,
            'SVT' => 2,
            'EPS' => 1,
            'Philosophie' => 4,
            'Chimie' => 3,
            'Physique' => 3,
            'Économie' => 3,
        ];

        return $coefficients[$nomMatiere] ?? 2;
    }

    public function listClasses()
    {
        return \App\Models\Classe::with(['matieres', 'eleves.user', 'enseignants.user'])->get();
    }
    public function createClasse(array $data)
    {
        return \DB::transaction(function () use ($data) {
            $classe = \App\Models\Classe::create($data);
            // Optionnel : affectation automatique des matières
            if (!empty($data['affectation_automatique'])) {
                $this->affectationAutomatiqueMatieres($classe);
            }
            return $classe->load(['matieres', 'eleves.user', 'enseignants.user']);
        });
    }
    public function getClasseById($id)
    {
        return \App\Models\Classe::with(['matieres', 'eleves.user', 'enseignants.user'])->findOrFail($id);
    }
    public function updateClasse($id, array $data)
    {
        return \DB::transaction(function () use ($id, $data) {
            $classe = \App\Models\Classe::findOrFail($id);
            $classe->update($data);
            return $classe->load(['matieres', 'eleves.user', 'enseignants.user']);
        });
    }
    public function deleteClasse($id)
    {
        return \DB::transaction(function () use ($id) {
            $classe = \App\Models\Classe::findOrFail($id);
            $classe->delete();
            return true;
        });
    }
    public function affectationAutomatiqueMatieres($classe)
    {
        // Logique d'affectation automatique des matières à la classe
        // À adapter selon la logique métier
    }
    public function affecterEleves($classeId, $eleveIds)
    {
        $classe = \App\Models\Classe::findOrFail($classeId);
        $classe->eleves()->sync($eleveIds);
        return $classe->load('eleves.user');
    }
    public function affecterEnseignants($classeId, $enseignantIds)
    {
        $classe = \App\Models\Classe::findOrFail($classeId);
        $classe->enseignants()->sync($enseignantIds);
        return $classe->load('enseignants.user');
    }
}
