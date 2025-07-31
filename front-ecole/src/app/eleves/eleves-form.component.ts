import { Component, Input, Output, EventEmitter, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-eleves-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './eleves-form.component.html',
})
export class ElevesFormComponent implements OnInit {
  @Input() initialData: any;
  @Output() formSubmit = new EventEmitter<any>();
  eleveForm: FormGroup;

  constructor(private fb: FormBuilder) {
    this.eleveForm = this.fb.group({
      nom: ['', Validators.required],
      prenom: ['', Validators.required],
      classe: ['', Validators.required],
      date_naissance: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]]
    });
  }

  ngOnInit() {
    if (this.initialData) {
      this.eleveForm.patchValue(this.initialData);
    }
  }

  onSubmit() {
    if (this.eleveForm.valid) {
      this.formSubmit.emit(this.eleveForm.value);
    }
  }
} 