import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-eleves-detail',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './eleves-detail.component.html',
})
export class ElevesDetailComponent {
  @Input() eleve: any;
} 