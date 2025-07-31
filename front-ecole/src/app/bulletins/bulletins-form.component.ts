import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-bulletins-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './bulletins-form.component.html'
})
export class BulletinsFormComponent implements OnInit {
  @Input() initialData: any;
  @Output() formSubmit = new EventEmitter<any>();
  bulletinForm: FormGroup;

  constructor(private fb: FormBuilder) {
    this.bulletinForm = this.fb.group({
      eleve: ['', Validators.required],
      classe: ['', Validators.required],
      periode: ['', Validators.required],
      moyenne_generale: ['', Validators.required],
      rang: ['', Validators.required]
    });
  }

  ngOnInit() {
    if (this.initialData) {
      this.bulletinForm.patchValue(this.initialData);
    }
  }

  onSubmit() {
    if (this.bulletinForm.valid) {
      this.formSubmit.emit(this.bulletinForm.value);
    }
  }
} 