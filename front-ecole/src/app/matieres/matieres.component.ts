import { Component, ViewChild, AfterViewInit } from '@angular/core';
import { MatieresListComponent } from './matieres-list.component';
import { MatieresFormComponent } from './matieres-form.component';
import { MatieresDetailComponent } from './matieres-detail.component';
import { MatieresService } from './matieres.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-matieres',
  standalone: true,
  imports: [CommonModule, MatieresListComponent, MatieresFormComponent, MatieresDetailComponent],
  templateUrl: './matieres.component.html',
  styleUrl: './matieres.component.css'
})
export class MatieresComponent implements AfterViewInit {
  @ViewChild('list') listComponent: MatieresListComponent | undefined;
  view: 'list' | 'form' | 'detail' = 'list';
  selectedMatiere: any = null;
  snackbarMessage = '';
  private shouldRefreshList = false;

  constructor(private matieresService: MatieresService) {}

  ngAfterViewInit() {
    if (this.shouldRefreshList && this.listComponent) {
      this.listComponent.loadMatieres();
      this.shouldRefreshList = false;
    }
  }

  onAdd() {
    this.selectedMatiere = null;
    this.view = 'form';
  }
  onEdit(matiere: any) {
    this.selectedMatiere = matiere;
    this.view = 'form';
  }
  onDetail(matiere: any) {
    this.selectedMatiere = matiere;
    this.view = 'detail';
  }
  onFormSubmit(result: any) {
    if (this.selectedMatiere && this.selectedMatiere.id) {
      this.matieresService.update(this.selectedMatiere.id, result).subscribe({
        next: () => {
          this.showSnackbar('Matière modifiée avec succès');
          this.returnToList();
        },
        error: (err) => {
          this.showSnackbar('Erreur modification');
          console.error('Erreur modification', err);
        }
      });
    } else {
      this.matieresService.create(result).subscribe({
        next: () => {
          this.showSnackbar('Matière ajoutée avec succès');
          this.returnToList();
        },
        error: (err) => {
          this.showSnackbar('Erreur création');
          console.error('Erreur création', err);
        }
      });
    }
    this.selectedMatiere = null;
  }
  onCancel() {
    this.view = 'list';
    this.selectedMatiere = null;
  }
  private returnToList() {
    this.view = 'list';
    if (this.listComponent) {
      this.listComponent.loadMatieres();
    } else {
      this.shouldRefreshList = true;
    }
  }
  private showSnackbar(message: string) {
    this.snackbarMessage = message;
    setTimeout(() => this.snackbarMessage = '', 2500);
  }
}
