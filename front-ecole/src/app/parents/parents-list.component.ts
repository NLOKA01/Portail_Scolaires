import { Component, Output, EventEmitter, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ParentsService } from '../services/parents.service';
import { ParentUser } from '../models/parent-user.model';

@Component({
  selector: 'app-parents-list',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './parents-list.component.html',
  styleUrls: ['./parents-list.component.css']
})
export class ParentsListComponent implements OnInit {
  @Output() edit = new EventEmitter<ParentUser>();
  @Output() detail = new EventEmitter<ParentUser>();
  parents: ParentUser[] = [];
  loading = false;
  error = '';

  constructor(private parentsService: ParentsService) {}

  ngOnInit(): void {
    this.loadParents();
  }

  loadParents() {
    this.loading = true;
    this.error = '';
    this.parentsService.getAll().subscribe({
      next: (data) => {
        this.parents = data;
        this.loading = false;
      },
      error: (err) => {
        this.error = "Erreur lors du chargement des parents.";
        this.loading = false;
      }
    });
  }

  onEdit(parent: ParentUser) { this.edit.emit(parent); }

  onDelete(parent: ParentUser) {
    if (confirm(`Supprimer le parent ${parent.user?.nom} ${parent.user?.prenom} ?`)) {
      this.parentsService.delete(parent.id).subscribe({
        next: () => this.loadParents(),
        error: () => alert("Erreur lors de la suppression.")
      });
    }
  }

  onDetail(parent: ParentUser) { this.detail.emit(parent); }
} 