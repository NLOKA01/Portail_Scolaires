import { Injectable } from '@angular/core';
import { CanActivate, Router, UrlTree } from '@angular/router';
import { AuthService } from './services/auth.service';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class AuthGuard implements CanActivate {
  constructor(private authService: AuthService, private router: Router) {}

  canActivate(): boolean | UrlTree | Observable<boolean | UrlTree> | Promise<boolean | UrlTree> {
    if (this.authService.isAuthenticated()) {
      // Récupérer le profil utilisateur pour router selon le rôle
      this.authService.getProfile().subscribe({
        next: (res) => {
          const role = res.user.role;
          if (role === 'admin') {
            this.router.navigate(['/dashboard']);
          } else if (role === 'enseignant') {
            this.router.navigate(['/enseignants']);
          } else if (role === 'parent') {
            this.router.navigate(['/parents']);
          } else if (role === 'eleve') {
            this.router.navigate(['/eleves']);
          }
        },
        error: () => {
          this.router.navigate(['/login']);
        }
      });
      return true;
    } else {
      this.router.navigate(['/login']);
      return false;
    }
  }
} 