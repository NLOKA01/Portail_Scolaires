import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Matiere } from '../models/matiere.model';

@Component({
  selector: 'app-matieres-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './matieres-form.component.html',
  styleUrls: ['./matieres-form.component.css']
})
export class MatieresFormComponent implements OnInit {
  @Input() initialData: Matiere | null = null;
  @Output() formSubmit = new EventEmitter<Matiere>();
  matiereForm: FormGroup;
  submitted = false;

  constructor(private fb: FormBuilder) {
    this.matiereForm = this.fb.group({
      nom: ['', Validators.required],
      niveau: ['', Validators.required],
      description: ['']
    });
  }

  ngOnInit() {
    if (this.initialData) {
      this.matiereForm.patchValue(this.initialData);
    }
  }

  onSubmit() {
    this.submitted = true;
    if (this.matiereForm && this.matiereForm.valid) {
      this.formSubmit.emit(this.matiereForm.value);
    }
  }
} 