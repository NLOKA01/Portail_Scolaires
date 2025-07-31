import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Note } from '../models/note.model';

@Component({
  selector: 'app-notes-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './notes-form.component.html',
})
export class NotesFormComponent implements OnInit {
  @Input() initialData: Note | null = null;
  @Output() formSubmit = new EventEmitter<Note>();
  noteForm: FormGroup;
  submitted = false;

  constructor(private fb: FormBuilder) {
    this.noteForm = this.fb.group({
      eleve: ['', Validators.required],
      matiere: ['', Validators.required],
      valeur: ['', [Validators.required, Validators.min(0), Validators.max(20)]],
      type_note: ['', Validators.required],
      periode: ['', Validators.required],
      enseignant: ['', Validators.required]
    });
  }

  ngOnInit() {
    if (this.initialData) {
      this.noteForm.patchValue(this.initialData);
    }
  }

  onSubmit() {
    this.submitted = true;
    if (this.noteForm.valid) {
      this.formSubmit.emit(this.noteForm.value);
    }
  }
} 