import { Component, Output, EventEmitter, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AbsencesService } from './absences.service';

@Component({
  selector: 'app-absences-list',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './absences-list.component.html',
})
export class AbsencesListComponent implements OnInit {
  @Output() edit = new EventEmitter<any>();
  @Output() detail = new EventEmitter<any>();
  @Output() delete = new EventEmitter<any>();
  absences: any[] = [];

  constructor(private absencesService: AbsencesService) {}

  ngOnInit() {
    this.loadAbsences();
  }

  loadAbsences() {
    this.absencesService.getAll().subscribe({
      next: (data) => this.absences = data,
      error: () => this.absences = []
    });
  }

  onEdit(absence: any) { this.edit.emit(absence); }
  onDelete(absence: any) { this.delete.emit(absence); }
  onDetail(absence: any) { this.detail.emit(absence); }
} 