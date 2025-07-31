import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Note } from '../models/note.model';

@Component({
  selector: 'app-notes-detail',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './notes-detail.component.html',
  styleUrls: ['./notes-detail.component.css']
})
export class NotesDetailComponent {
  @Input() note: Note | null = null;
} 