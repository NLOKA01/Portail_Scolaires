import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { BulletinsService } from '../services/bulletins.service';
import { NotesService } from '../services/notes.service';

@Component({
  selector: 'app-eleves-dashboard',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './eleves-dashboard.component.html',
  styleUrl: './eleves-dashboard.component.css'
})
export class ElevesDashboardComponent implements OnInit {
  currentUser: any;
  eleve: any;
  bulletins: any[] = [];
  recentNotes: any[] = [];
  loading = true;

  constructor(
    private authService: AuthService,
    private bulletinsService: BulletinsService,
    private notesService: NotesService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.loadUserProfile();
  }

  loadUserProfile() {
    this.authService.getProfile().subscribe({
      next: (res) => {
        this.currentUser = res.user;
        this.loadEleveData();
      },
      error: (err) => {
        console.error('Erreur chargement profil:', err);
      }
    });
  }

  loadEleveData() {
    // Charger les données de l'élève connecté
    this.notesService.getAll().subscribe({
      next: (data: any[]) => {
        // Filtrer les notes de l'élève connecté
        this.recentNotes = data.filter(note => note.eleve?.user_id === this.currentUser?.id).slice(0, 10);
        
        // Charger les bulletins de l'élève
        this.loadBulletins();
        this.loading = false;
      },
      error: (err) => {
        console.error('Erreur chargement données:', err);
        this.loading = false;
      }
    });
  }

  loadBulletins() {
    // Charger les bulletins de l'élève connecté
    this.bulletinsService.getAll().subscribe({
      next: (data: any[]) => {
        this.bulletins = data.filter(bulletin => bulletin.eleve?.user_id === this.currentUser?.id);
      }
    });
  }

  downloadBulletin(bulletinId: number) {
    // Télécharger le PDF du bulletin
    window.open(`http://localhost:8000/api/bulletins/${bulletinId}/download`, '_blank');
  }

  logout() {
    this.authService.logout();
    this.router.navigate(['/login']);
  }
} 