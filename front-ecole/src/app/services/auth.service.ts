import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { User } from '../models/user.model';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private apiUrl = 'http://localhost:8000/api'; // Adapter l'URL si besoin
  private tokenKey = 'auth_token';

  constructor(private http: HttpClient) {}

  login(credentials: { email: string; password: string }): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/login`, credentials).pipe(
      tap(res => {
        if (res && res.token) {
          this.setToken(res.token);
        }
      })
    );
  }

  register(data: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/register`, data);
  }

  logout(): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/logout`, {}).pipe(
      tap(() => this.removeToken())
    );
  }

  getProfile(): Observable<{ user: User; permissions: any }> {
    return this.http.get<{ user: User; permissions: any }>(`${this.apiUrl}/me`);
  }

  toggleUserStatus(userId: number): Observable<any> {
    return this.http.put<any>(`${this.apiUrl}/users/${userId}/toggle-status`, {});
  }

  invalidateAllTokens(): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/invalidate-all-tokens`, {}).pipe(
      tap(() => this.removeToken())
    );
  }

  // Gestion du token JWT
  setToken(token: string) {
    localStorage.setItem(this.tokenKey, token);
  }

  getToken(): string | null {
    return localStorage.getItem(this.tokenKey);
  }

  removeToken() {
    localStorage.removeItem(this.tokenKey);
  }

  isAuthenticated(): boolean {
    return !!this.getToken();
  }
} 