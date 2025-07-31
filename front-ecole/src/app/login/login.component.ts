import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [ReactiveFormsModule, CommonModule],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent {
  loginForm: FormGroup;
  errorMessage: string = '';

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', Validators.required]
    });
  }

  onSubmit(): void {
    if (this.loginForm.valid) {
      this.authService.login(this.loginForm.value).subscribe({
        next: () => {
          this.authService.getProfile().subscribe({
            next: (res) => {
              const role = res.user.role;
              if (role === 'admin') {
                this.router.navigate(['/dashboard']);
              } else if (role === 'enseignant') {
                this.router.navigate(['/enseignants/dashboard']);
              } else if (role === 'parent') {
                this.router.navigate(['/parents/dashboard']);
              } else if (role === 'eleve') {
                this.router.navigate(['/eleves/dashboard']);
              } else {
                this.router.navigate(['/']);
              }
            },
            error: () => {
              this.router.navigate(['/']);
            }
          });
        },
        error: (error) => {
          this.errorMessage = 'Identifiants incorrects. Veuillez réessayer.';
          console.error('Login error', error);
        }
      });
    }
  }
}
