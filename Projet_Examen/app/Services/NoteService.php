<?php

namespace App\Services;

use App\Models\Note;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Models\Enseignant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class NoteService
{
    /**
     * Saisir une note pour un élève dans une matière spécifique
     */
    public function saisirNote(array $data): Note
    {
        return DB::transaction(function () use ($data) {
            $validator = Validator::make($data, [
                'eleve_id' => 'required|exists:eleves,id',
                'matiere_id' => 'required|exists:matieres,id',
                'enseignant_id' => 'required|exists:enseignants,id',
                'valeur' => 'required|numeric|between:0,20',
                'type_note' => 'required|in:devoir,composition,interrogation,oral',
                'periode' => 'required|in:trimestre_1,trimestre_2,trimestre_3,semestre_1,semestre_2',
                'commentaire' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            $validatedData = $validator->validated();

            // Vérifier que l'enseignant est autorisé
            $enseignant = Enseignant::findOrFail($validatedData['enseignant_id']);
            $eleve = Eleve::findOrFail($validatedData['eleve_id']);
            $matiere = Matiere::findOrFail($validatedData['matiere_id']);

            if (!$enseignant->matieres()->where('matiere_id', $matiere->id)->exists() ||
                !$enseignant->classes()->where('classe_id', $eleve->classe_id)->exists()) {
                throw new Exception('L\'enseignant n\'est pas autorisé à saisir cette note.');
            }

            return Note::create($validatedData);
        });
    }

    /**
     * Récupérer les notes d'un élève pour une période donnée
     */
    public function getNotesElevePeriode($eleveId, $periode)
    {
        return Note::where('eleve_id', $eleveId)
            ->where('periode', $periode)
            ->with(['matiere', 'enseignant.user'])
            ->get();
    }

    /**
     * Calculer la moyenne d'un élève dans une matière pour une période donnée
     */
    public function calculerMoyenneMatiere($eleveId, $matiereId, $periode)
    {
        $notes = Note::where('eleve_id', $eleveId)
            ->where('matiere_id', $matiereId)
            ->where('periode', $periode)
            ->get();

        if ($notes->isEmpty()) {
            return null;
        }

        $total = $notes->sum('valeur');
        return $total / $notes->count();
    }

    /**
     * Récupérer toutes les notes avec les relations nécessaires
     */
    public function getAllNotesWithRelations()
    {
        return Note::with(['eleve.user', 'matiere', 'enseignant.user'])->get();
    }

    /**
     * Récupérer une note par son ID avec les relations nécessaires
     */
    public function getNoteByIdWithRelations($id)
    {
        return Note::with(['eleve.user', 'matiere', 'enseignant.user'])->find($id);
    }

    /**
     * Mettre à jour une note
     */
    public function updateNote($id, array $data)
    {
        $note = Note::find($id);
        if (!$note) {
            throw new Exception('Note non trouvée');
        }
        $validator = Validator::make($data, [
            'valeur' => 'sometimes|numeric|between:0,20',
            'type_note' => 'sometimes|in:devoir,composition,interrogation,oral',
            'periode' => 'sometimes|in:trimestre_1,trimestre_2,trimestre_3,semestre_1,semestre_2',
            'commentaire' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }
        $note->update($validator->validated());
        return $note;
    }

    /**
     * Supprimer une note
     */
    public function deleteNote($id)
    {
        $note = Note::find($id);
        if (!$note) {
            throw new Exception('Note non trouvée');
        }
        $note->delete();
    }

    /**
     * Récupérer les notes d'un élève avec les relations nécessaires
     */
    public function getNotesByEleveIdWithRelations($eleveId)
    {
        return Note::where('eleve_id', $eleveId)
            ->with(['matiere', 'enseignant.user'])
            ->get();
    }
}
