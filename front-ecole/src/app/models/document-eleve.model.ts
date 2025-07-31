import { Eleve } from './eleve.model';

export interface DocumentEleve {
  id: number;
  eleve_id: number;
  type_document: string;
  chemin_fichier: string;
  date_depot: string;
  est_valide: boolean;
  eleve?: Eleve;
} 