import { Component, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-documents-list',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './documents-list.component.html',
})
export class DocumentsListComponent {
  @Output() edit = new EventEmitter<any>();
  @Output() detail = new EventEmitter<any>();
  documents: any[] = [];

  // TODO: Charger la liste depuis l'API
  loadDocuments() {}

  onEdit(document: any) { this.edit.emit(document); }
  onDelete(document: any) {}
  onDetail(document: any) { this.detail.emit(document); }
} 