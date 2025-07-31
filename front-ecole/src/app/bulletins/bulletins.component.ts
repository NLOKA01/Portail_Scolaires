import { Component, ViewChild, AfterViewInit } from '@angular/core';
import { BulletinsListComponent } from './bulletins-list.component';
import { BulletinsFormComponent } from './bulletins-form.component';
import { BulletinsDetailComponent } from './bulletins-detail.component';
import { BulletinsService } from './bulletins.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-bulletins',
  standalone: true,
  imports: [CommonModule, BulletinsListComponent, BulletinsFormComponent, BulletinsDetailComponent],
  templateUrl: './bulletins.component.html',
  styleUrl: './bulletins.component.css'
})
export class BulletinsComponent {
  @ViewChild('list') listComponent: BulletinsListComponent | undefined;
  view: 'list' | 'form' | 'detail' = 'list';
  selectedBulletin: any = null;
  snackbarMessage = '';

  constructor(private bulletinsService: BulletinsService) {}

  onAdd() {
    this.selectedBulletin = null;
    this.view = 'form';
  }
  onEdit(bulletin: any) {
    this.selectedBulletin = bulletin;
    this.view = 'form';
  }
  onDetail(bulletin: any) {
    this.selectedBulletin = bulletin;
    this.view = 'detail';
  }
  onFormSubmit(result: any) {
    if (this.selectedBulletin && this.selectedBulletin.id) {
      this.bulletinsService.update(this.selectedBulletin.id, result).subscribe({
        next: () => {
          this.showSnackbar('Bulletin modifié avec succès');
          this.returnToList();
        },
        error: (err) => this.showSnackbar('Erreur modification')
      });
    } else {
      this.bulletinsService.create(result).subscribe({
        next: () => {
          this.showSnackbar('Bulletin ajouté avec succès');
          this.returnToList();
        },
        error: (err) => this.showSnackbar('Erreur création')
      });
    }
    this.selectedBulletin = null;
  }
  onCancel() {
    this.view = 'list';
    this.selectedBulletin = null;
  }
  private returnToList() {
    this.view = 'list';
    setTimeout(() => this.listComponent?.loadBulletins(), 100);
  }
  private showSnackbar(message: string) {
    this.snackbarMessage = message;
    setTimeout(() => this.snackbarMessage = '', 2500);
  }
}
