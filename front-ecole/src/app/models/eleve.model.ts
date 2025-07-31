import { User } from './user.model';
import { Classe } from './classe.model';
import { ParentUser } from './parent-user.model';

export interface Eleve {
  id: number;
  user_id: number;
  date_naissance: string;
  lieu_naissance: string;
  sexe: Sexe;
  numero_matricule: string;
  classe_id: number;
  parent_id: number;
  user?: User;
  classe?: Classe;
  parent?: ParentUser;
  // Pour éviter les erreurs de dépendance circulaire, commenter les relations profondes
  // bulletins?: Bulletin[];
  // documents?: DocumentEleve[];
  // absences?: Absence[];
  // notes?: Note[];
}

export enum Sexe {
  MASCULIN = 'M',
  FEMININ = 'F',
} 