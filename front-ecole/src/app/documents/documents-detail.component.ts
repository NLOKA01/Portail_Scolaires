import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-documents-detail',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './documents-detail.component.html',
})
export class DocumentsDetailComponent {
  @Input() document: any;
} 