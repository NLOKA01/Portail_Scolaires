import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  private baseUrl = 'http://localhost:8000/api';
  private currentUserSubject = new BehaviorSubject<any>(this.getUserFromStorage());
  public currentUser$ = this.currentUserSubject.asObservable();

  constructor(private http: HttpClient) { }

  register(user: any): Observable<any> {
    return this.http.post(`${this.baseUrl}/register`, user);
  }

  login(credentials: any): Observable<any> {
    return this.http.post(`${this.baseUrl}/login`, credentials).pipe(
      // Après login, récupérer le profil et le stocker
      // (à adapter selon la structure de la réponse)
      // Ici, on suppose que la réponse contient le token et le user
      // Si ce n'est pas le cas, il faudra appeler getProfile() après
      //
      // next: (response) => { ... }
    );
  }

  logout(): Observable<any> {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    this.currentUserSubject.next(null);
    return this.http.post(`${this.baseUrl}/logout`, {});
  }

  getProfile(): Observable<any> {
    return this.http.get(`${this.baseUrl}/me`).pipe(
      // next: (response) => { ... }
    );
  }

  updateProfile(user: any): Observable<any> {
    return this.http.put(`${this.baseUrl}/profile`, user);
  }

  setUser(user: any) {
    localStorage.setItem('user', JSON.stringify(user));
    this.currentUserSubject.next(user);
  }

  getUserFromStorage() {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  }

  getCurrentUser() {
    return this.currentUserSubject.value;
  }

  getRole(): string | null {
    const user = this.getCurrentUser();
    return user ? user.role : null;
  }
} 