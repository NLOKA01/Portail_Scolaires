import { Component, ViewChild, AfterViewInit } from '@angular/core';
import { NotesListComponent } from './notes-list.component';
import { NotesFormComponent } from './notes-form.component';
import { NotesDetailComponent } from './notes-detail.component';
import { NotesService } from './notes.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-notes',
  standalone: true,
  imports: [CommonModule, NotesListComponent, NotesFormComponent, NotesDetailComponent],
  templateUrl: './notes.component.html',
  styleUrl: './notes.component.css'
})
export class NotesComponent implements AfterViewInit {
  @ViewChild('list') listComponent: NotesListComponent | undefined;
  view: 'list' | 'form' | 'detail' = 'list';
  selectedNote: any = null;
  message: string = '';
  error: string = '';
  private shouldRefreshList = false;

  constructor(private notesService: NotesService) {}

  ngAfterViewInit() {
    if (this.shouldRefreshList && this.listComponent) {
      this.listComponent.loadNotes?.();
      this.shouldRefreshList = false;
    }
  }

  onAdd() {
    this.selectedNote = null;
    this.view = 'form';
  }
  onEdit(note: any) {
    this.selectedNote = note;
    this.view = 'form';
  }
  onDetail(note: any) {
    this.selectedNote = note;
    this.view = 'detail';
  }
  onFormSubmit(result: any) {
    if (this.selectedNote && this.selectedNote.id) {
      this.notesService.update(this.selectedNote.id, result).subscribe({
        next: () => {
          this.showMessage('Note modifiée avec succès');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la modification')
      });
    } else {
      this.notesService.create(result).subscribe({
        next: () => {
          this.showMessage('Note ajoutée avec succès');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la création')
      });
    }
    this.selectedNote = null;
  }
  onCancel() {
    this.view = 'list';
    this.selectedNote = null;
  }
  onDelete(note: any) {
    if (confirm('Supprimer cette note ?')) {
      this.notesService.delete(note.id).subscribe({
        next: () => {
          this.showMessage('Note supprimée');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la suppression')
      });
    }
  }
  private returnToList() {
    this.view = 'list';
    if (this.listComponent) {
      this.listComponent.loadNotes?.();
    } else {
      this.shouldRefreshList = true;
    }
  }
  private showMessage(msg: string) {
    this.message = msg;
    setTimeout(() => this.message = '', 2500);
  }
  private showError(msg: string) {
    this.error = msg;
    setTimeout(() => this.error = '', 3000);
  }
}
