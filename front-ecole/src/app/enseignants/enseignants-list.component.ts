import { Component, Output, EventEmitter, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { EnseignantsService } from '../services/enseignants.service';
import { Enseignant } from '../models/enseignant.model';

@Component({
  selector: 'app-enseignants-list',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './enseignants-list.component.html',
})
export class EnseignantsListComponent implements OnInit {
  @Output() edit = new EventEmitter<Enseignant>();
  @Output() detail = new EventEmitter<Enseignant>();
  enseignants: Enseignant[] = [];
  loading = false;
  error = '';

  constructor(private enseignantsService: EnseignantsService) {}

  ngOnInit() {
    this.loadEnseignants();
  }

  loadEnseignants() {
    this.loading = true;
    this.error = '';
    this.enseignantsService.getAll().subscribe({
      next: (data) => {
        this.enseignants = data;
        this.loading = false;
      },
      error: (err) => {
        this.error = "Erreur lors du chargement des enseignants.";
        this.loading = false;
      }
    });
  }

  onEdit(enseignant: Enseignant) { this.edit.emit(enseignant); }

  onDelete(enseignant: Enseignant) {
    if (confirm(`Supprimer l'enseignant ${enseignant.user?.nom} ${enseignant.user?.prenom} ?`)) {
      this.enseignantsService.delete(enseignant.id).subscribe({
        next: () => this.loadEnseignants(),
        error: () => alert("Erreur lors de la suppression.")
      });
    }
  }

  onDetail(enseignant: Enseignant) { this.detail.emit(enseignant); }
} 