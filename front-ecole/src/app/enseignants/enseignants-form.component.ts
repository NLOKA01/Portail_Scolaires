import { Component, Input, Output, EventEmitter, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-enseignants-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './enseignants-form.component.html',
})
export class EnseignantsFormComponent implements OnInit {
  @Input() initialData: any;
  @Output() formSubmit = new EventEmitter<any>();
  enseignantForm: FormGroup;

  constructor(private fb: FormBuilder) {
    this.enseignantForm = this.fb.group({
      nom: ['', Validators.required],
      prenom: ['', Validators.required],
      matiere: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]]
    });
  }

  ngOnInit() {
    if (this.initialData) {
      this.enseignantForm.patchValue(this.initialData);
    }
  }

  onSubmit() {
    if (this.enseignantForm.valid) {
      this.formSubmit.emit(this.enseignantForm.value);
    }
  }
} 