import { Eleve } from './eleve.model';
import { Matiere } from './matiere.model';
import { Enseignant } from './enseignant.model';

export interface Note {
  id: number;
  eleve_id: number;
  matiere_id: number;
  enseignant_id: number;
  valeur: number;
  type_note: TypeNote;
  periode: string;
  commentaire?: string;
  eleve?: Eleve;
  matiere?: Matiere;
  enseignant?: Enseignant;
}

export enum TypeNote {
  CONTROLE = 'controle',
  EXAMEN = 'examen',
  DEVOIR = 'devoir',
} 