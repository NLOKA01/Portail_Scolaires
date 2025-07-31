import { Component, ViewChild, AfterViewInit } from '@angular/core';
import { AbsencesListComponent } from './absences-list.component';
import { AbsencesFormComponent } from './absences-form.component';
import { AbsencesDetailComponent } from './absences-detail.component';
import { AbsencesService } from './absences.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-absences',
  standalone: true,
  imports: [CommonModule, AbsencesListComponent, AbsencesFormComponent, AbsencesDetailComponent],
  templateUrl: './absences.component.html',
  styleUrl: './absences.component.css'
})
export class AbsencesComponent implements AfterViewInit {
  @ViewChild('list') listComponent: AbsencesListComponent | undefined;
  view: 'list' | 'form' | 'detail' = 'list';
  selectedAbsence: any = null;
  message: string = '';
  error: string = '';
  private shouldRefreshList = false;

  constructor(private absencesService: AbsencesService) {}

  ngAfterViewInit() {
    if (this.shouldRefreshList && this.listComponent) {
      this.listComponent.loadAbsences();
      this.shouldRefreshList = false;
    }
  }

  onAdd() {
    this.selectedAbsence = null;
    this.view = 'form';
  }
  onEdit(absence: any) {
    this.selectedAbsence = absence;
    this.view = 'form';
  }
  onDetail(absence: any) {
    this.selectedAbsence = absence;
    this.view = 'detail';
  }
  onFormSubmit(result: any) {
    if (this.selectedAbsence && this.selectedAbsence.id) {
      this.absencesService.update(this.selectedAbsence.id, result).subscribe({
        next: () => {
          this.showMessage('Absence modifiée avec succès');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la modification')
      });
    } else {
      this.absencesService.create(result).subscribe({
        next: () => {
          this.showMessage('Absence ajoutée avec succès');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la création')
      });
    }
    this.selectedAbsence = null;
  }
  onCancel() {
    this.view = 'list';
    this.selectedAbsence = null;
  }
  onDelete(absence: any) {
    if (confirm('Supprimer cette absence ?')) {
      this.absencesService.delete(absence.id).subscribe({
        next: () => {
          this.showMessage('Absence supprimée');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la suppression')
      });
    }
  }
  private returnToList() {
    this.view = 'list';
    if (this.listComponent) {
      this.listComponent.loadAbsences();
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
