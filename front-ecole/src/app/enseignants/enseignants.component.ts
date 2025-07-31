import { Component, ViewChild, AfterViewInit } from '@angular/core';
import { EnseignantsListComponent } from './enseignants-list.component';
import { EnseignantsFormComponent } from './enseignants-form.component';
import { EnseignantsDetailComponent } from './enseignants-detail.component';
import { EnseignantsService } from '../services/enseignants.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-enseignants',
  standalone: true,
  imports: [CommonModule, EnseignantsListComponent, EnseignantsFormComponent, EnseignantsDetailComponent],
  templateUrl: './enseignants.component.html',
  styleUrl: './enseignants.component.css'
})
export class EnseignantsComponent implements AfterViewInit {
  @ViewChild('list') listComponent: EnseignantsListComponent | undefined;
  view: 'list' | 'form' | 'detail' = 'list';
  selectedEnseignant: any = null;
  message: string = '';
  error: string = '';
  private shouldRefreshList = false;

  constructor(private enseignantsService: EnseignantsService) {}

  ngAfterViewInit() {
    if (this.shouldRefreshList && this.listComponent) {
      this.listComponent.loadEnseignants?.();
      this.shouldRefreshList = false;
    }
  }

  onAdd() {
    this.selectedEnseignant = null;
    this.view = 'form';
  }
  onEdit(enseignant: any) {
    this.selectedEnseignant = enseignant;
    this.view = 'form';
  }
  onDetail(enseignant: any) {
    this.selectedEnseignant = enseignant;
    this.view = 'detail';
  }
  onFormSubmit(result: any) {
    if (this.selectedEnseignant && this.selectedEnseignant.id) {
      this.enseignantsService.update(this.selectedEnseignant.id, result).subscribe({
        next: () => {
          this.showMessage('Enseignant modifié avec succès');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la modification')
      });
    } else {
      this.enseignantsService.create(result).subscribe({
        next: () => {
          this.showMessage('Enseignant ajouté avec succès');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la création')
      });
    }
    this.selectedEnseignant = null;
  }
  onCancel() {
    this.view = 'list';
    this.selectedEnseignant = null;
  }
  onDelete(enseignant: any) {
    if (confirm('Supprimer cet enseignant ?')) {
      this.enseignantsService.delete(enseignant.id).subscribe({
        next: () => {
          this.showMessage('Enseignant supprimé');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la suppression')
      });
    }
  }
  private returnToList() {
    this.view = 'list';
    if (this.listComponent) {
      this.listComponent.loadEnseignants?.();
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
