import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Classe } from '../models/classe.model';

@Component({
  selector: 'app-classes-detail',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './classes-detail.component.html',
})
export class ClassesDetailComponent {
  @Input() classe: Classe | null = null;
} 