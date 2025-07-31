import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { BulletinsService } from '../services/bulletins.service';
import { ElevesService } from '../services/eleves.service';

@Component({
  selector: 'app-parents-dashboard',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './parents-dashboard.component.html',
  styleUrl: './parents-dashboard.component.css'
})
export class ParentsDashboardComponent implements OnInit {
  currentUser: any;
  enfants: any[] = [];
  bulletins: any[] = [];
  loading = true;

  constructor(
    private authService: AuthService,
    private bulletinsService: BulletinsService,
    private elevesService: ElevesService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.loadUserProfile();
    this.loadEnfantsData();
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

  loadEnfantsData() {
    // Charger les enfants du parent connecté
    this.elevesService.getAll().subscribe({
      next: (data: any[]) => {
        // Filtrer les élèves qui appartiennent à ce parent
        this.enfants = data.filter(eleve => eleve.parent_id === this.currentUser?.parentUser?.id);
        
        // Charger les bulletins pour chaque enfant
        this.loadBulletins();
        this.loading = false;
      },
      error: (err) => {
        console.error('Erreur chargement enfants:', err);
        this.loading = false;
      }
    });
  }

  loadBulletins() {
    // Charger les bulletins pour tous les enfants
    this.enfants.forEach(enfant => {
      this.bulletinsService.getByEleve(enfant.id).subscribe({
        next: (data: any[]) => {
          this.bulletins = [...this.bulletins, ...data];
        }
      });
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