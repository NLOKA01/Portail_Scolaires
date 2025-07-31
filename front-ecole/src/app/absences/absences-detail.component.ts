import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-absences-detail',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './absences-detail.component.html',
})
export class AbsencesDetailComponent {
  @Input() absence: any;
} 