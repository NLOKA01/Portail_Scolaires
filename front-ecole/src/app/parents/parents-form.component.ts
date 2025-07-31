import { Component, Input, Output, EventEmitter, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { ParentUser } from '../models/parent-user.model';

@Component({
  selector: 'app-parents-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './parents-form.component.html',
  styleUrls: ['./parents-form.component.css']
})
export class ParentsFormComponent implements OnInit {
  @Input() initialData: ParentUser | null = null;
  @Output() formSubmit = new EventEmitter<ParentUser>();
  parentForm: FormGroup;
  submitted = false;

  constructor(private fb: FormBuilder) {
    this.parentForm = this.fb.group({
      nom: ['', Validators.required],
      prenom: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      telephone: ['', Validators.required],
      adresse: ['', Validators.required],
      profession: ['']
    });
  }

  ngOnInit() {
    if (this.initialData) {
      this.parentForm.patchValue(this.initialData);
    }
  }

  onSubmit() {
    this.submitted = true;
    if (this.parentForm.valid) {
      this.formSubmit.emit(this.parentForm.value);
    }
  }
} 