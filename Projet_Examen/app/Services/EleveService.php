<?php
namespace App\Services;

use App\Models\User;
use App\Models\Eleve;
use App\Models\ParentUser;
use App\Models\Classe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendCredentialsMail;

class EleveService
{
    /**
     * Inscrire un élève avec affectation automatique du parent
     */
    public function inscrireEleve(array $eleveData, array $parentData = null)
    {
        return DB::transaction(function () use ($eleveData, $parentData) {
            // 1. Chercher ou créer le parent
            $parent = $this->getOrCreateParent($parentData);

            // 2. Générer un mot de passe aléatoire pour l'élève
            $plainPassword = Str::random(10);

            // 3. Créer l'utilisateur élève
            $userEleve = User::create([
                'nom' => $eleveData['nom'],
                'prenom' => $eleveData['prenom'],
                'adresse' => $eleveData['adresse'],
                'telephone' => $eleveData['telephone'],
                'email' => $eleveData['email'],
                'password' => Hash::make($plainPassword),
                'role' => 'eleve',
                'est_actif' => true,
            ]);

            // 4. Générer matricule
            $matricule = $this->genererMatricule($eleveData['classe_id']);

            // 5. Créer l'élève
            $eleve = Eleve::create([
                'user_id' => $userEleve->id,
                'date_naissance' => $eleveData['date_naissance'],
                'lieu_naissance' => $eleveData['lieu_naissance'],
                'sexe' => $eleveData['sexe'],
                'numero_matricule' => $matricule,
                'classe_id' => $eleveData['classe_id'],
                'parent_id' => $parent->id,
            ]);

            // 6. Mettre à jour le nombre d'enfants du parent
            $parent->increment('nombre_enfants');

            // 7. Envoyer l'email au parent avec les identifiants de l'élève
            Mail::to($parent->user->email)->send(new SendCredentialsMail($userEleve, $plainPassword));

            return $eleve;
        });
    }

    /**
     * Chercher ou créer un parent
     */
    private function getOrCreateParent(array $parentData = null)
    {
        if (!$parentData) {
            throw new \Exception('Données parent obligatoires');
        }

        // Chercher par email
        $userParent = User::where('email', $parentData['email'])->first();

        if ($userParent) {
            return $userParent->parentUser;
        }

        // Créer nouveau parent
        $userParent = User::create([
            'nom' => $parentData['nom'],
            'prenom' => $parentData['prenom'],
            'adresse' => $parentData['adresse'],
            'telephone' => $parentData['telephone'],
            'email' => $parentData['email'],
            'password' => Hash::make($parentData['password'] ?? 'password123'),
            'role' => 'parent',
            'est_actif' => true,
        ]);

        return ParentUser::create([
            'user_id' => $userParent->id,
            'profession' => $parentData['profession'] ?? null,
            'nombre_enfants' => 0,
        ]);
    }

    /**
     * Générer matricule unique
     */
    private function genererMatricule($classeId)
    {
        $classe = Classe::find($classeId);
        $annee = date('Y');
        $niveau = substr($classe->niveau, 0, 1);
        $section = $classe->nom;

        // Format: 2024-6A-001
        $prefix = "{$annee}-{$niveau}{$section}";
        $dernierNumero = Eleve::where('numero_matricule', 'LIKE', "{$prefix}%")
                ->count() + 1;

        return $prefix . '-' . str_pad($dernierNumero, 3, '0', STR_PAD_LEFT);
    }

    public function updateEleve($id, array $data)
    {
        return \DB::transaction(function () use ($id, $data) {
            $eleve = Eleve::findOrFail($id);
            $eleve->user->update([
                'nom' => $data['nom'] ?? $eleve->user->nom,
                'prenom' => $data['prenom'] ?? $eleve->user->prenom,
                'adresse' => $data['adresse'] ?? $eleve->user->adresse,
                'telephone' => $data['telephone'] ?? $eleve->user->telephone,
                'email' => $data['email'] ?? $eleve->user->email,
            ]);
            $eleve->update([
                'date_naissance' => $data['date_naissance'] ?? $eleve->date_naissance,
                'lieu_naissance' => $data['lieu_naissance'] ?? $eleve->lieu_naissance,
                'sexe' => $data['sexe'] ?? $eleve->sexe,
                'classe_id' => $data['classe_id'] ?? $eleve->classe_id,
            ]);
            return $eleve->load(['user', 'classe', 'parent.user']);
        });
    }
    public function deleteEleve($id)
    {
        return \DB::transaction(function () use ($id) {
            $eleve = Eleve::findOrFail($id);
            $eleve->user->delete();
            $eleve->delete();
            return true;
        });
    }
    public function getEleveById($id)
    {
        return Eleve::with(['user', 'classe', 'parent.user', 'notes.matiere'])->findOrFail($id);
    }
    public function getNotesForEleve($id)
    {
        $eleve = Eleve::findOrFail($id);
        return $eleve->notes()->with(['matiere', 'enseignant.user'])->get();
    }
    public function getBulletinsForEleve($id)
    {
        $eleve = Eleve::findOrFail($id);
        return $eleve->bulletins()->with(['classe'])->get();
    }
    public function getDocumentsForEleve($id)
    {
        $eleve = Eleve::findOrFail($id);
        return $eleve->documents()->get();
    }
    public function getAbsencesForEleve($id)
    {
        $eleve = Eleve::findOrFail($id);
        return $eleve->absences()->get();
    }
}
