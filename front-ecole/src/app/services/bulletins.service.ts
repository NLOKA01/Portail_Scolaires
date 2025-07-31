import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Bulletin } from '../models/bulletin.model';

@Injectable({ providedIn: 'root' })
export class BulletinsService {
  private apiUrl = 'http://localhost:8000/api/bulletins'; // Adapter l'URL si besoin

  constructor(private http: HttpClient) {}

  getAll(): Observable<Bulletin[]> {
    return this.http.get<Bulletin[]>(this.apiUrl);
  }

  getById(id: number): Observable<Bulletin> {
    return this.http.get<Bulletin>(`${this.apiUrl}/${id}`);
  }

  create(bulletin: Bulletin): Observable<Bulletin> {
    return this.http.post<Bulletin>(this.apiUrl, bulletin);
  }

  update(id: number, bulletin: Bulletin): Observable<Bulletin> {
    return this.http.put<Bulletin>(`${this.apiUrl}/${id}`, bulletin);
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/${id}`);
  }

  generateForEleve(eleveId: number, periode: string, anneeScolaire?: string) {
    return this.http.post<any>(`http://localhost:8000/api/bulletins`, { eleve_id: eleveId, periode, annee_scolaire: anneeScolaire });
  }

  getByEleve(eleveId: number) {
    return this.http.get<any[]>(`http://localhost:8000/api/eleves/${eleveId}/bulletins`);
  }

  downloadPDF(bulletinId: number) {
    return this.http.get(`http://localhost:8000/api/bulletins/${bulletinId}/download`, { responseType: 'blob' });
  }

  downloadClassBulletins(classeId: number, periode: string, anneeScolaire?: string) {
    return this.http.post(`http://localhost:8000/api/bulletins/download-class`, {
      classe_id: classeId,
      periode: periode,
      annee_scolaire: anneeScolaire
    }, { responseType: 'blob' });
  }
} 