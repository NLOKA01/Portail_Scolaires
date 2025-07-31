import { Component, ViewChild, AfterViewInit } from '@angular/core';
import { ParentsListComponent } from './parents-list.component';
import { ParentsFormComponent } from './parents-form.component';
import { ParentsDetailComponent } from './parents-detail.component';
import { ParentsService } from './parents.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-parents',
  standalone: true,
  imports: [CommonModule, ParentsListComponent, ParentsFormComponent, ParentsDetailComponent],
  templateUrl: './parents.component.html',
  styleUrl: './parents.component.css'
})
export class ParentsComponent implements AfterViewInit {
  @ViewChild('list') listComponent: ParentsListComponent | undefined;
  view: 'list' | 'form' | 'detail' = 'list';
  selectedParent: any = null;
  message: string = '';
  error: string = '';
  private shouldRefreshList = false;

  constructor(private parentsService: ParentsService) {}

  ngAfterViewInit() {
    if (this.shouldRefreshList && this.listComponent) {
      this.listComponent.loadParents?.();
      this.shouldRefreshList = false;
    }
  }

  onAdd() {
    this.selectedParent = null;
    this.view = 'form';
  }
  onEdit(parent: any) {
    this.selectedParent = parent;
    this.view = 'form';
  }
  onDetail(parent: any) {
    this.selectedParent = parent;
    this.view = 'detail';
  }
  onFormSubmit(result: any) {
    if (this.selectedParent && this.selectedParent.id) {
      this.parentsService.update(this.selectedParent.id, result).subscribe({
        next: () => {
          this.showMessage('Parent modifié avec succès');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la modification')
      });
    } else {
      this.parentsService.create(result).subscribe({
        next: () => {
          this.showMessage('Parent ajouté avec succès');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la création')
      });
    }
    this.selectedParent = null;
  }
  onCancel() {
    this.view = 'list';
    this.selectedParent = null;
  }
  onDelete(parent: any) {
    if (confirm('Supprimer ce parent ?')) {
      this.parentsService.delete(parent.id).subscribe({
        next: () => {
          this.showMessage('Parent supprimé');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la suppression')
      });
    }
  }
  private returnToList() {
    this.view = 'list';
    if (this.listComponent) {
      this.listComponent.loadParents?.();
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