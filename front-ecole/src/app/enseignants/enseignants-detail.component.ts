import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-enseignants-detail',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './enseignants-detail.component.html',
})
export class EnseignantsDetailComponent {
  @Input() enseignant: any;
} 