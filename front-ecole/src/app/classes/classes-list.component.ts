import { Component, OnInit, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Classe } from '../models/classe.model';
import { ClassesService } from '../services/classes.service';

@Component({
  selector: 'app-classes-list',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './classes-list.component.html',
})
export class ClassesListComponent implements OnInit {
  @Output() edit = new EventEmitter<Classe>();
  @Output() detail = new EventEmitter<Classe>();
  classes: Classe[] = [];
  loading = false;
  error = '';

  constructor(private classesService: ClassesService) {}

  ngOnInit(): void {
    this.loadClasses();
  }

  loadClasses() {
    this.loading = true;
    this.error = '';
    this.classesService.getAll().subscribe({
      next: (data) => {
        this.classes = data;
        this.loading = false;
      },
      error: (err) => {
        this.error = "Erreur lors du chargement des classes.";
        this.loading = false;
      }
    });
  }

  onEdit(classe: Classe) { this.edit.emit(classe); }

  onDelete(classe: Classe) {
    if (confirm(`Supprimer la classe ${classe.nom} (${classe.niveau}) ?`)) {
      this.classesService.delete(classe.id).subscribe({
        next: () => this.loadClasses(),
        error: () => alert("Erreur lors de la suppression.")
      });
    }
  }

  onDetail(classe: Classe) { this.detail.emit(classe); }
} 