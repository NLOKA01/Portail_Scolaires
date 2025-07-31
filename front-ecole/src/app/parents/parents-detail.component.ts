import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ParentUser } from '../models/parent-user.model';

@Component({
  selector: 'app-parents-detail',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './parents-detail.component.html',
  styleUrls: ['./parents-detail.component.css']
})
export class ParentsDetailComponent {
  @Input() parent: ParentUser | null = null;
} 