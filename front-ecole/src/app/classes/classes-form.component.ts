import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Classe } from '../models/classe.model';

@Component({
  selector: 'app-classes-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './classes-form.component.html',
})
export class ClassesFormComponent implements OnInit {
  @Input() initialData: Classe | null = null;
  @Output() formSubmit = new EventEmitter<Classe>();
  classeForm: FormGroup;
  submitted = false;

  constructor(private fb: FormBuilder) {
    this.classeForm = this.fb.group({
      niveau: ['', Validators.required],
      nom: ['', Validators.required],
      capacite: ['', [Validators.required, Validators.min(1)]],
      annee_scolaire: ['', Validators.required],
      description: ['']
    });
  }

  ngOnInit() {
    if (this.initialData) {
      this.classeForm.patchValue(this.initialData);
    }
  }

  onSubmit() {
    this.submitted = true;
    if (this.classeForm.valid) {
      this.formSubmit.emit(this.classeForm.value);
    }
  }
} 