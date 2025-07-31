import { Eleve } from './eleve.model';
import { Matiere } from './matiere.model';
import { Enseignant } from './enseignant.model';
import { Bulletin } from './bulletin.model';

export interface Classe {
  id: number;
  niveau: string;
  nom: string;
  capacite: number;
  annee_scolaire: string;
  description?: string;
  eleves?: Eleve[];
  matieres?: (Matiere & { coefficient: number })[];
  enseignants?: Enseignant[];
  bulletins?: Bulletin[];
} 