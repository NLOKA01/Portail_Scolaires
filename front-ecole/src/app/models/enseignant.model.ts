import { User } from './user.model';
import { Matiere } from './matiere.model';

export interface Enseignant {
  id: number;
  user_id: number;
  specialite: string;
  date_embauche: string;
  numero_identifiant: string;
  user?: User;
  matieres?: Matiere[];
  // Pour éviter les erreurs de dépendance circulaire, commenter les relations profondes
  // classes?: Classe[];
  // notes?: Note[];
} 