import { Routes } from '@angular/router';
import { LoginComponent } from './login/login.component';
import { RegisterComponent } from './register/register.component';
import { DashboardComponent } from './dashboard/dashboard.component';
import { AuthGuard } from './auth.guard';
import { ClassesComponent } from './classes/classes.component';
import { ElevesComponent } from './eleves/eleves.component';
import { EnseignantsComponent } from './enseignants/enseignants.component';
import { MatieresComponent } from './matieres/matieres.component';
import { NotesComponent } from './notes/notes.component';
import { BulletinsComponent } from './bulletins/bulletins.component';
import { AbsencesComponent } from './absences/absences.component';
import { DocumentsComponent } from './documents/documents.component';
import { EnseignantsDashboardComponent } from './enseignants/enseignants-dashboard.component';
import { ParentsDashboardComponent } from './parents/parents-dashboard.component';
import { ElevesDashboardComponent } from './eleves/eleves-dashboard.component';

export const routes: Routes = [
  { path: 'login', component: LoginComponent },
  { path: 'register', component: RegisterComponent, canActivate: [AuthGuard], data: { roles: ['admin'] } },
  {
    path: '',
    canActivate: [AuthGuard],
    children: [
      { path: 'dashboard', component: DashboardComponent },
      { path: 'enseignants/dashboard', component: EnseignantsDashboardComponent },
      { path: 'parents/dashboard', component: ParentsDashboardComponent },
      { path: 'eleves/dashboard', component: ElevesDashboardComponent },
      { path: 'classes', component: ClassesComponent },
      { path: 'eleves', component: ElevesComponent },
      { path: 'enseignants', component: EnseignantsComponent },
      { path: 'matieres', component: MatieresComponent },
      { path: 'notes', component: NotesComponent },
      { path: 'bulletins', component: BulletinsComponent },
      { path: 'absences', component: AbsencesComponent },
      { path: 'documents', component: DocumentsComponent },
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' }
    ]
  },
  { path: '**', redirectTo: '' }
];
