import { Eleve } from './eleve.model';
import { Classe } from './classe.model';

export interface Bulletin {
  id: number;
  eleve_id: number;
  classe_id: number;
  annee_scolaire: string;
  periode: string;
  moyenne_generale: number;
  rang: number;
  mention: string;
  appreciation?: string;
  pdf_path?: string;
  date_edition: string;
  eleve?: Eleve;
  classe?: Classe;
} 