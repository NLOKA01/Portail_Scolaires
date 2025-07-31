import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { NotesService } from '../services/notes.service';
import { ClassesService } from '../services/classes.service';
import { MatieresService } from '../services/matieres.service';

@Component({
  selector: 'app-enseignants-dashboard',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './enseignants-dashboard.component.html',
  styleUrl: './enseignants-dashboard.component.css'
})
export class EnseignantsDashboardComponent implements OnInit {
  currentUser: any;
  stats = {
    classes: 0,
    matieres: 0,
    notesSaisies: 0,
    bulletinsGeneres: 0
  };
  recentNotes: any[] = [];
  loading = true;

  constructor(
    private authService: AuthService,
    private notesService: NotesService,
    private classesService: ClassesService,
    private matieresService: MatieresService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.loadUserProfile();
    this.loadDashboardData();
  }

  loadUserProfile() {
    this.authService.getProfile().subscribe({
      next: (res) => {
        this.currentUser = res.user;
      },
      error: (err) => {
        console.error('Erreur chargement profil:', err);
      }
    });
  }

  loadDashboardData() {
    // Charger les statistiques de l'enseignant
    this.notesService.getAll().subscribe({
      next: (data: any[]) => {
        this.stats.notesSaisies = data.length;
        this.recentNotes = data.slice(0, 5); // 5 dernières notes
        this.loading = false;
      },
      error: (err) => {
        console.error('Erreur chargement données:', err);
        this.loading = false;
      }
    });

    // Charger les classes et matières de l'enseignant
    this.classesService.getAll().subscribe({
      next: (data: any[]) => {
        this.stats.classes = data.length;
      }
    });

    this.matieresService.getAll().subscribe({
      next: (data: any[]) => {
        this.stats.matieres = data.length;
      }
    });
  }

  navigateTo(route: string) {
    this.router.navigate([route]);
  }

  logout() {
    this.authService.logout();
    this.router.navigate(['/login']);
  }
} 