import { Component, ViewChild, AfterViewInit } from '@angular/core';
import { ClassesListComponent } from './classes-list.component';
import { ClassesFormComponent } from './classes-form.component';
import { ClassesDetailComponent } from './classes-detail.component';
import { ClassesService } from './classes.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-classes',
  standalone: true,
  imports: [CommonModule, ClassesListComponent, ClassesFormComponent, ClassesDetailComponent],
  templateUrl: './classes.component.html',
  styleUrl: './classes.component.css'
})
export class ClassesComponent implements AfterViewInit {
  @ViewChild('list') listComponent: ClassesListComponent | undefined;
  view: 'list' | 'form' | 'detail' = 'list';
  selectedClasse: any = null;
  message: string = '';
  error: string = '';
  private shouldRefreshList = false;

  constructor(private classesService: ClassesService) {}

  ngAfterViewInit() {
    if (this.shouldRefreshList && this.listComponent) {
      this.listComponent.loadClasses();
      this.shouldRefreshList = false;
    }
  }

  onAdd() {
    this.selectedClasse = null;
    this.view = 'form';
  }
  onEdit(classe: any) {
    this.selectedClasse = classe;
    this.view = 'form';
  }
  onDetail(classe: any) {
    this.selectedClasse = classe;
    this.view = 'detail';
  }
  onFormSubmit(result: any) {
    if (this.selectedClasse && this.selectedClasse.id) {
      this.classesService.update(this.selectedClasse.id, result).subscribe({
        next: () => {
          this.showMessage('Classe modifiée avec succès');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la modification')
      });
    } else {
      this.classesService.create(result).subscribe({
        next: () => {
          this.showMessage('Classe ajoutée avec succès');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la création')
      });
    }
    this.selectedClasse = null;
  }
  onCancel() {
    this.view = 'list';
    this.selectedClasse = null;
  }
  onDelete(classe: any) {
    if (confirm('Supprimer cette classe ?')) {
      this.classesService.delete(classe.id).subscribe({
        next: () => {
          this.showMessage('Classe supprimée');
          this.returnToList();
        },
        error: (err) => this.showError('Erreur lors de la suppression')
      });
    }
  }
  private returnToList() {
    this.view = 'list';
    if (this.listComponent) {
      this.listComponent.loadClasses();
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
