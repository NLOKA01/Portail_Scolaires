<?php

namespace App\Services;

use App\Models\Bulletin;
use App\Models\Eleve;
use App\Models\ParentUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class NotificationService
{
    /**
     * Envoyer une notification email pour un nouveau bulletin
     */
    public function envoyerNotificationBulletin(Bulletin $bulletin): bool
    {
        try {
            $eleve = $bulletin->eleve;
            $parent = $eleve->parent;
            
            if (!$parent || !$parent->user) {
                return false;
            }

            $data = [
                'eleve_nom' => $eleve->user->nom . ' ' . $eleve->user->prenom,
                'parent_nom' => $parent->user->nom . ' ' . $parent->user->prenom,
                'periode' => $this->formaterPeriode($bulletin->periode),
                'annee_scolaire' => $bulletin->annee_scolaire,
                'moyenne_generale' => $bulletin->moyenne_generale,
                'mention' => $bulletin->mention,
                'rang' => $bulletin->rang,
                'bulletin_id' => $bulletin->id
            ];

            Mail::send('emails.bulletin_disponible', $data, function ($message) use ($parent, $data) {
                $message->to($parent->user->email, $data['parent_nom'])
                        ->subject('Nouveau bulletin disponible - ' . $data['eleve_nom']);
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Erreur envoi notification bulletin: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoyer les identifiants de connexion aux nouveaux utilisateurs
     */
    public function envoyerIdentifiants(string $email, string $nom, string $prenom, string $password, string $role): bool
    {
        try {
            $data = [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'password' => $password,
                'role' => $role
            ];

            Mail::send('emails.credentials', $data, function ($message) use ($email, $nom, $prenom) {
                $message->to($email, $nom . ' ' . $prenom)
                        ->subject('Vos identifiants de connexion - Portail Scolaire');
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Erreur envoi identifiants: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Formater la période pour l'affichage
     */
    private function formaterPeriode(string $periode): string
    {
        $periodes = [
            'trimestre_1' => '1er Trimestre',
            'trimestre_2' => '2ème Trimestre',
            'trimestre_3' => '3ème Trimestre',
            'semestre_1' => '1er Semestre',
            'semestre_2' => '2ème Semestre'
        ];

        return $periodes[$periode] ?? $periode;
    }
} 