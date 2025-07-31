import { Component, OnInit, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NotesService } from '../services/notes.service';
import { Note } from '../models/note.model';

@Component({
  selector: 'app-notes-list',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './notes-list.component.html',
  styleUrls: ['./notes-list.component.css']
})
export class NotesListComponent implements OnInit {
  @Output() edit = new EventEmitter<Note>();
  @Output() detail = new EventEmitter<Note>();
  notes: Note[] = [];
  loading = false;
  error = '';

  constructor(private notesService: NotesService) {}

  ngOnInit(): void {
    this.loadNotes();
  }

  loadNotes() {
    this.loading = true;
    this.error = '';
    this.notesService.getAll().subscribe({
      next: (data) => {
        this.notes = data;
        this.loading = false;
      },
      error: (err) => {
        this.error = "Erreur lors du chargement des notes.";
        this.loading = false;
      }
    });
  }

  onEdit(note: Note) { this.edit.emit(note); }

  onDelete(note: Note) {
    if (confirm('Supprimer cette note ?')) {
      this.notesService.delete(note.id).subscribe({
        next: () => this.loadNotes(),
        error: () => alert('Erreur lors de la suppression.')
      });
    }
  }

  onDetail(note: Note) { this.detail.emit(note); }
} 