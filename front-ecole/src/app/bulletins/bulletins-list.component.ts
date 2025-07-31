import { Component, OnInit, Output, EventEmitter } from '@angular/core';
import { BulletinsService } from '../services/bulletins.service';
import { ClassesService } from '../services/classes.service';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-bulletins-list',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './bulletins-list.component.html'
})
export class BulletinsListComponent implements OnInit {
  @Output() edit = new EventEmitter<any>();
  @Output() detail = new EventEmitter<any>();
  
  bulletins: any[] = [];
  classes: any[] = [];
  loading = true;
  error = '';
  
  // Pour le téléchargement groupé
  selectedClasse: number = 0;
  selectedPeriode: string = '';
  selectedAnnee: string = '';
  
  periodes = [
    { value: 'trimestre_1', label: '1er Trimestre' },
    { value: 'trimestre_2', label: '2ème Trimestre' },
    { value: 'trimestre_3', label: '3ème Trimestre' },
    { value: 'semestre_1', label: '1er Semestre' },
    { value: 'semestre_2', label: '2ème Semestre' }
  ];

  constructor(
    private bulletinsService: BulletinsService,
    private classesService: ClassesService
  ) {}

  ngOnInit(): void {
    this.loadBulletins();
    this.loadClasses();
  }

  loadBulletins() {
    this.loading = true;
    this.bulletinsService.getAll().subscribe({
      next: (data) => {
        this.bulletins = data;
        this.loading = false;
      },
      error: (err) => {
        console.error('Erreur chargement bulletins', err);
        this.error = 'Erreur lors du chargement des bulletins';
        this.loading = false;
      }
    });
  }

  loadClasses() {
    this.classesService.getAll().subscribe({
      next: (data) => {
        this.classes = data;
      },
      error: (err) => {
        console.error('Erreur chargement classes', err);
      }
    });
  }

  onEdit(bulletin: any) {
    this.edit.emit(bulletin);
  }

  onDelete(bulletin: any) {
    if (confirm('Supprimer ce bulletin ?')) {
      this.bulletinsService.delete(bulletin.id).subscribe({
        next: () => this.loadBulletins(),
        error: (err) => {
          console.error('Erreur suppression', err);
          alert('Erreur lors de la suppression');
        }
      });
    }
  }

  onDetail(bulletin: any) {
    this.detail.emit(bulletin);
  }

  downloadPDF(bulletinId: number) {
    this.bulletinsService.downloadPDF(bulletinId).subscribe({
      next: (blob) => {
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `bulletin_${bulletinId}.pdf`;
        link.click();
        window.URL.revokeObjectURL(url);
      },
      error: (err) => {
        console.error('Erreur téléchargement PDF', err);
        alert('Erreur lors du téléchargement');
      }
    });
  }

  downloadClassBulletins() {
    if (!this.selectedClasse || !this.selectedPeriode) {
      alert('Veuillez sélectionner une classe et une période');
      return;
    }

    this.bulletinsService.downloadClassBulletins(
      this.selectedClasse,
      this.selectedPeriode,
      this.selectedAnnee
    ).subscribe({
      next: (blob) => {
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `bulletins_classe_${this.selectedClasse}_${this.selectedPeriode}.zip`;
        link.click();
        window.URL.revokeObjectURL(url);
      },
      error: (err) => {
        console.error('Erreur téléchargement groupé', err);
        alert('Erreur lors du téléchargement groupé');
      }
    });
  }
} 