import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Matiere } from '../models/matiere.model';

@Component({
  selector: 'app-matieres-detail',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './matieres-detail.component.html',
  styleUrls: ['./matieres-detail.component.css']
})
export class MatieresDetailComponent {
  @Input() matiere: Matiere | null = null;
} 