<?php

namespace App\Services;

use App\Models\User;
use App\Models\ParentUser;
use App\Models\Eleve;
use App\Models\Enseignant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendCredentialsMail;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Crée un enseignant, lui attribue une classe et une matière, puis envoie ses identifiants
     */
    public static function createEnseignant(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Générer un mot de passe aléatoire
            $plainPassword = Str::random(10);
            $user = User::create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'adresse' => $data['adresse'],
                'telephone' => $data['telephone'],
                'email' => $data['email'],
                'password' => Hash::make($plainPassword),
                'role' => User::ROLE_ENSEIGNANT,
                'est_actif' => true,
            ]);

            $enseignant = Enseignant::create([
                'user_id' => $user->id,
                'specialite' => $data['specialite'] ?? 'Général',
                'date_embauche' => $data['date_embauche'] ?? now(),
                'numero_identifiant' => $data['numero_identifiant'] ?? 'ENS' . time(),
            ]);

            // Attribuer classes et matières (si passées en tableau)
            if (!empty($data['classe_ids'])) {
                $enseignant->classes()->sync($data['classe_ids']);
            }

            if (!empty($data['matiere_ids'])) {
                $enseignant->matieres()->sync($data['matiere_ids']);
            }

            // Envoyer les informations de connexion
            Mail::to($user->email)->send(new SendCredentialsMail($user, $plainPassword));

            return $user;
        });
    }

    /**
     * Crée un utilisateur parent
     */
    public static function createParent(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Générer un mot de passe aléatoire
            $plainPassword = Str::random(10);
            $user = User::create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'adresse' => $data['adresse'],
                'telephone' => $data['telephone'],
                'email' => $data['email'],
                'password' => Hash::make($plainPassword),
                'role' => User::ROLE_PARENT,
                'est_actif' => true,
            ]);

            ParentUser::create([
                'user_id' => $user->id,
                'profession' => $data['profession'] ?? null,
                'nombre_enfants' => 0,
            ]);

            Mail::to($user->email)->send(new SendCredentialsMail($user, $plainPassword));

            return $user;
        });
    }
}
