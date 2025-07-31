import { Component, Output, EventEmitter, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ElevesService } from '../services/eleves.service';
import { Eleve } from '../models/eleve.model';

@Component({
  selector: 'app-eleves-list',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './eleves-list.component.html',
})
export class ElevesListComponent implements OnInit {
  @Output() edit = new EventEmitter<Eleve>();
  @Output() detail = new EventEmitter<Eleve>();
  eleves: Eleve[] = [];
  loading = false;
  error = '';

  constructor(private elevesService: ElevesService) {}

  ngOnInit() {
    this.loadEleves();
  }

  loadEleves() {
    this.loading = true;
    this.error = '';
    this.elevesService.getAll().subscribe({
      next: (data) => {
        this.eleves = data;
        this.loading = false;
      },
      error: (err) => {
        this.error = "Erreur lors du chargement des élèves.";
        this.loading = false;
      }
    });
  }

  onEdit(eleve: Eleve) { this.edit.emit(eleve); }

  onDelete(eleve: Eleve) {
    if (confirm(`Supprimer l'élève ${eleve.user?.nom ?? ''} ${eleve.user?.prenom ?? ''} ?`)) {
      this.elevesService.delete(eleve.id).subscribe({
        next: () => this.loadEleves(),
        error: () => alert("Erreur lors de la suppression.")
      });
    }
  }

  onDetail(eleve: Eleve) { this.detail.emit(eleve); }
} 