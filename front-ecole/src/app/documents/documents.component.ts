import { Component, ViewChild, AfterViewInit } from '@angular/core';
import { DocumentsListComponent } from './documents-list.component';
import { DocumentsFormComponent } from './documents-form.component';
import { DocumentsDetailComponent } from './documents-detail.component';
import { DocumentsService } from './documents.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-documents',
  standalone: true,
  imports: [CommonModule, DocumentsListComponent, DocumentsFormComponent, DocumentsDetailComponent],
  templateUrl: './documents.component.html',
  styleUrl: './documents.component.css'
})
export class DocumentsComponent implements AfterViewInit {
  @ViewChild('list') listComponent: DocumentsListComponent | undefined;
  view: 'list' | 'form' | 'detail' = 'list';
  selectedDocument: any = null;
  message: string = '';
  error: string = '';
  private shouldRefreshList = false;

  constructor(private documentsService: DocumentsService) {}

  ngAfterViewInit() {
    if (this.shouldRefreshList && this.listComponent) {
      this.listComponent.loadDocuments();
      this.shouldRefreshList = false;
    }
  }

  onAdd() {
    this.selectedDocument = null;
    this.view = 'form';
  }
  onEdit(document: any) {
    this.selectedDocument = document;
    this.view = 'form';
  }
  onDetail(document: any) {
    this.selectedDocument = document;
    this.view = 'detail';
  }
  onFormSubmit(result: any) {
    if (this.selectedDocument && this.selectedDocument.id) {
      this.documentsService.update(this.selectedDocument.id, result).subscribe({
        next: () => {
          this.showMessage('Document modifié avec succès');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la modification')
      });
    } else {
      this.documentsService.create(result).subscribe({
        next: () => {
          this.showMessage('Document ajouté avec succès');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la création')
      });
    }
    this.selectedDocument = null;
  }
  onCancel() {
    this.view = 'list';
    this.selectedDocument = null;
  }
  onDelete(document: any) {
    if (confirm('Supprimer ce document ?')) {
      this.documentsService.delete(document.id).subscribe({
        next: () => {
          this.showMessage('Document supprimé');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la suppression')
      });
    }
  }
  private returnToList() {
    this.view = 'list';
    if (this.listComponent) {
      this.listComponent.loadDocuments();
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
