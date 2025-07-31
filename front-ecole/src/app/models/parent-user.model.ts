import { User } from './user.model';
import { Eleve } from './eleve.model';

export interface ParentUser {
  id: number;
  user_id: number;
  profession: string;
  nombre_enfants: number;
  user?: User;
  enfants?: Eleve[];
} 