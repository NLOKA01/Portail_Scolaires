export enum UserRole {
  ADMIN = 'admin',
  ENSEIGNANT = 'enseignant',
  PARENT = 'parent',
  ELEVE = 'eleve',
}

export interface User {
  id: number;
  nom: string;
  prenom: string;
  adresse: string;
  telephone: string;
  email: string;
  password?: string;
  role: UserRole;
  image?: string;
  est_actif: boolean;
  email_verified_at?: string;
  remember_token?: string;
} 