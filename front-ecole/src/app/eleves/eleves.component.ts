import { Component, ViewChild, AfterViewInit } from '@angular/core';
import { ElevesListComponent } from './eleves-list.component';
import { ElevesFormComponent } from './eleves-form.component';
import { ElevesDetailComponent } from './eleves-detail.component';
import { ElevesService } from '../services/eleves.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-eleves',
  standalone: true,
  imports: [CommonModule, ElevesListComponent, ElevesFormComponent, ElevesDetailComponent],
  templateUrl: './eleves.component.html',
  styleUrl: './eleves.component.css'
})
export class ElevesComponent implements AfterViewInit {
  @ViewChild('list') listComponent: ElevesListComponent | undefined;
  view: 'list' | 'form' | 'detail' = 'list';
  selectedEleve: any = null;
  message: string = '';
  error: string = '';
  private shouldRefreshList = false;

  constructor(private elevesService: ElevesService) {}

  ngAfterViewInit() {
    if (this.shouldRefreshList && this.listComponent) {
      this.listComponent.loadEleves?.();
      this.shouldRefreshList = false;
    }
  }

  onAdd() {
    this.selectedEleve = null;
    this.view = 'form';
  }
  onEdit(eleve: any) {
    this.selectedEleve = eleve;
    this.view = 'form';
  }
  onDetail(eleve: any) {
    this.selectedEleve = eleve;
    this.view = 'detail';
  }
  onFormSubmit(result: any) {
    if (this.selectedEleve && this.selectedEleve.id) {
      this.elevesService.update(this.selectedEleve.id, result).subscribe({
        next: () => {
          this.showMessage('Élève modifié avec succès');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la modification')
      });
    } else {
      this.elevesService.create(result).subscribe({
        next: () => {
          this.showMessage('Élève ajouté avec succès');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la création')
      });
    }
    this.selectedEleve = null;
  }
  onCancel() {
    this.view = 'list';
    this.selectedEleve = null;
  }
  onDelete(eleve: any) {
    if (confirm('Supprimer cet élève ?')) {
      this.elevesService.delete(eleve.id).subscribe({
        next: () => {
          this.showMessage('Élève supprimé');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la suppression')
      });
    }
  }
  private returnToList() {
    this.view = 'list';
    if (this.listComponent) {
      this.listComponent.loadEleves?.();
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
