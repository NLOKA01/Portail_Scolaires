import { Classe } from './classe.model';
import { Enseignant } from './enseignant.model';
import { Note } from './note.model';

export interface Matiere {
  id: number;
  nom: string;
  description?: string;
  niveau: string;
  classes?: (Classe & { coefficient: number })[];
  enseignants?: Enseignant[];
  notes?: Note[];
} 