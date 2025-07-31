import { Component, Input, Output, EventEmitter, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-documents-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './documents-form.component.html',
})
export class DocumentsFormComponent implements OnInit {
  @Input() initialData: any;
  @Output() formSubmit = new EventEmitter<any>();
  documentForm: FormGroup;

  constructor(private fb: FormBuilder) {
    this.documentForm = this.fb.group({
      nom: ['', Validators.required],
      type: ['', Validators.required],
      date: ['', Validators.required]
    });
  }

  ngOnInit() {
    if (this.initialData) {
      this.documentForm.patchValue(this.initialData);
    }
  }

  onSubmit() {
    if (this.documentForm.valid) {
      this.formSubmit.emit(this.documentForm.value);
    }
  }
} 