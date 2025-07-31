import { Component, OnInit, Output, EventEmitter } from '@angular/core';
import { MatieresService } from '../services/matieres.service';
import { CommonModule } from '@angular/common';
import { Matiere } from '../models/matiere.model';

@Component({
  selector: 'app-matieres-list',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './matieres-list.component.html',
  styleUrls: ['./matieres-list.component.css']
})
export class MatieresListComponent implements OnInit {
  @Output() edit = new EventEmitter<Matiere>();
  @Output() detail = new EventEmitter<Matiere>();
  matieres: Matiere[] = [];
  loading = false;
  error = '';

  constructor(private matieresService: MatieresService) {}

  ngOnInit(): void {
    this.loadMatieres();
  }

  loadMatieres() {
    this.loading = true;
    this.error = '';
    this.matieresService.getAll().subscribe({
      next: (data) => {
        this.matieres = data;
        this.loading = false;
      },
      error: (err) => {
        this.error = "Erreur lors du chargement des matières.";
        this.loading = false;
      }
    });
  }

  onEdit(matiere: Matiere) {
    this.edit.emit(matiere);
  }
  onDelete(matiere: Matiere) {
    if (confirm('Supprimer cette matière ?')) {
      this.matieresService.delete(matiere.id).subscribe({
        next: () => this.loadMatieres(),
        error: () => alert('Erreur lors de la suppression.')
      });
    }
  }
  onDetail(matiere: Matiere) {
    this.detail.emit(matiere);
  }
} 