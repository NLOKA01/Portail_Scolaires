<?php
namespace App\Services;

use App\Models\Enseignant;
use App\Models\Matiere;
use App\Models\Classe;

class EnseignantService
{
    /**
     * Affecter des matières à un enseignant
     */
    public function affecterMatieres(Enseignant $enseignant, array $matiereIds)
    {
        // Vérifier que les matières existent
        $matieres = Matiere::whereIn('id', $matiereIds)->get();

        if ($matieres->count() !== count($matiereIds)) {
            throw new \Exception('Certaines matières n\'existent pas');
        }

        // Affecter les matières
        $enseignant->matieres()->sync($matiereIds);

        return $enseignant->load('matieres');
    }

    /**
     * Affecter des classes à un enseignant
     */
    public function affecterClasses(Enseignant $enseignant, array $classeIds)
    {
        // Vérifier que les classes existent
        $classes = Classe::whereIn('id', $classeIds)->get();

        if ($classes->count() !== count($classeIds)) {
            throw new \Exception('Certaines classes n\'existent pas');
        }

        // Affecter les classes
        $enseignant->classes()->sync($classeIds);

        return $enseignant->load('classes');
    }

    /**
     * Affectation automatique basée sur la spécialité
     */
    public function affectationAutomatique(Enseignant $enseignant)
    {
        // Logique d'affectation basée sur la spécialité
        $specialite = strtolower($enseignant->specialite);

        $matiereIds = [];

        switch ($specialite) {
            case 'mathematiques':
                $matiereIds = Matiere::where('nom', 'LIKE', '%mathématiques%')->pluck('id');
                break;
            case 'français':
                $matiereIds = Matiere::where('nom', 'LIKE', '%français%')->pluck('id');
                break;
            case 'sciences':
                $matiereIds = Matiere::whereIn('nom', ['Sciences Physiques', 'Chimie', 'Physique', 'SVT'])
                    ->pluck('id');
                break;
            case 'langues':
                $matiereIds = Matiere::where('nom', 'LIKE', '%anglais%')->pluck('id');
                break;
        }

        if ($matiereIds->isNotEmpty()) {
            $this->affecterMatieres($enseignant, $matiereIds->toArray());
        }

        return $enseignant;
    }

    public function listEnseignants()
    {
        return \App\Models\Enseignant::with(['user', 'matieres', 'classes'])->get();
    }
    public function createEnseignant(array $data)
    {
        return \DB::transaction(function () use ($data) {
            // Utilise UserService pour la création de l'utilisateur et de l'enseignant
            $userService = app(\App\Services\UserService::class);
            $enseignant = $userService->createEnseignant($data);
            return $enseignant->load(['user', 'matieres', 'classes']);
        });
    }
    public function getEnseignantById($id)
    {
        return \App\Models\Enseignant::with(['user', 'matieres', 'classes'])->findOrFail($id);
    }
    public function updateEnseignant($id, array $data)
    {
        return \DB::transaction(function () use ($id, $data) {
            $enseignant = \App\Models\Enseignant::findOrFail($id);
            $enseignant->user->update([
                'nom' => $data['nom'] ?? $enseignant->user->nom,
                'prenom' => $data['prenom'] ?? $enseignant->user->prenom,
                'adresse' => $data['adresse'] ?? $enseignant->user->adresse,
                'telephone' => $data['telephone'] ?? $enseignant->user->telephone,
                'email' => $data['email'] ?? $enseignant->user->email,
            ]);
            $enseignant->update([
                'specialite' => $data['specialite'] ?? $enseignant->specialite,
                'date_embauche' => $data['date_embauche'] ?? $enseignant->date_embauche,
                'numero_identifiant' => $data['numero_identifiant'] ?? $enseignant->numero_identifiant,
            ]);
            if (!empty($data['classe_ids'])) {
                $enseignant->classes()->sync($data['classe_ids']);
            }
            if (!empty($data['matiere_ids'])) {
                $enseignant->matieres()->sync($data['matiere_ids']);
            }
            return $enseignant->load(['user', 'matieres', 'classes']);
        });
    }
    public function deleteEnseignant($id)
    {
        return \DB::transaction(function () use ($id) {
            $enseignant = \App\Models\Enseignant::findOrFail($id);
            $enseignant->user->delete();
            $enseignant->delete();
            return true;
        });
    }
    public function affecterMatieres($enseignantId, $matiereIds)
    {
        $enseignant = \App\Models\Enseignant::findOrFail($enseignantId);
        $enseignant->matieres()->sync($matiereIds);
        return $enseignant->load('matieres');
    }
    public function affecterClasses($enseignantId, $classeIds)
    {
        $enseignant = \App\Models\Enseignant::findOrFail($enseignantId);
        $enseignant->classes()->sync($classeIds);
        return $enseignant->load('classes');
    }
}
