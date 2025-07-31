import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-bulletins-detail',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './bulletins-detail.component.html'
})
export class BulletinsDetailComponent {
  @Input() bulletin: any;
} 