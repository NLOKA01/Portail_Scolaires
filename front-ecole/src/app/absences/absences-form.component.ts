import { Component, Input, Output, EventEmitter, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-absences-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './absences-form.component.html',
})
export class AbsencesFormComponent implements OnInit {
  @Input() initialData: any;
  @Output() formSubmit = new EventEmitter<any>();
  absenceForm: FormGroup;

  constructor(private fb: FormBuilder) {
    this.absenceForm = this.fb.group({
      eleve: ['', Validators.required],
      date: ['', Validators.required],
      motif: ['', Validators.required]
    });
  }

  ngOnInit() {
    if (this.initialData) {
      this.absenceForm.patchValue(this.initialData);
    }
  }

  onSubmit() {
    if (this.absenceForm.valid) {
      this.formSubmit.emit(this.absenceForm.value);
    }
  }
} 