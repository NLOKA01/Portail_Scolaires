import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Eleve } from '../models/eleve.model';

@Injectable({ providedIn: 'root' })
export class ElevesService {
  private apiUrl = 'http://localhost:8000/api/eleves'; // Adapter l'URL si besoin

  constructor(private http: HttpClient) {}

  getAll(): Observable<Eleve[]> {
    return this.http.get<Eleve[]>(this.apiUrl);
  }

  getById(id: number): Observable<Eleve> {
    return this.http.get<Eleve>(`${this.apiUrl}/${id}`);
  }

  create(eleve: Eleve): Observable<Eleve> {
    return this.http.post<Eleve>(this.apiUrl, eleve);
  }

  update(id: number, eleve: Eleve): Observable<Eleve> {
    return this.http.put<Eleve>(`${this.apiUrl}/${id}`, eleve);
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/${id}`);
  }

  getAbsences(eleveId: number) {
    return this.http.get<any[]>(`http://localhost:8000/api/eleves/${eleveId}/absences`);
  }

  getBulletins(eleveId: number) {
    return this.http.get<any[]>(`http://localhost:8000/api/eleves/${eleveId}/bulletins`);
  }

  getDocuments(eleveId: number) {
    return this.http.get<any[]>(`http://localhost:8000/api/eleves/${eleveId}/documents`);
  }

  getNotes(eleveId: number) {
    return this.http.get<any[]>(`http://localhost:8000/api/eleves/${eleveId}/notes`);
  }
} 