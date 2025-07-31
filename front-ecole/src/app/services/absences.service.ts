import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Absence } from '../models/absence.model';

@Injectable({ providedIn: 'root' })
export class AbsencesService {
  private apiUrl = 'http://localhost:8000/api/absences'; // Adapter l'URL si besoin

  constructor(private http: HttpClient) {}

  getAll(): Observable<Absence[]> {
    return this.http.get<Absence[]>(this.apiUrl);
  }

  getById(id: number): Observable<Absence> {
    return this.http.get<Absence>(`${this.apiUrl}/${id}`);
  }

  create(absence: Absence): Observable<Absence> {
    return this.http.post<Absence>(this.apiUrl, absence);
  }

  update(id: number, absence: Absence): Observable<Absence> {
    return this.http.put<Absence>(`${this.apiUrl}/${id}`, absence);
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/${id}`);
  }

  getByEleve(eleveId: number) {
    return this.http.get<any[]>(`http://localhost:8000/api/eleves/${eleveId}/absences`);
  }
} 