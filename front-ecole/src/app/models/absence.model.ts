import { Eleve } from './eleve.model';

export interface Absence {
  id: number;
  eleve_id: number;
  date_absence: string;
  periode: PeriodeAbsence;
  motif: string;
  est_justifiee: boolean;
  document_justificatif?: string;
  commentaire?: string;
  eleve?: Eleve;
}

export enum PeriodeAbsence {
  MATIN = 'matin',
  APRES_MIDI = 'apres_midi',
  JOURNEE = 'journee',
} 