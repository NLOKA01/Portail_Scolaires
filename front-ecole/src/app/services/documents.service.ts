import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { DocumentEleve } from '../models/document-eleve.model';

@Injectable({ providedIn: 'root' })
export class DocumentsService {
  private apiUrl = 'http://localhost:8000/api/documents'; // Adapter l'URL si besoin

  constructor(private http: HttpClient) {}

  getAll(): Observable<DocumentEleve[]> {
    return this.http.get<DocumentEleve[]>(this.apiUrl);
  }

  getById(id: number): Observable<DocumentEleve> {
    return this.http.get<DocumentEleve>(`${this.apiUrl}/${id}`);
  }

  create(document: DocumentEleve): Observable<DocumentEleve> {
    return this.http.post<DocumentEleve>(this.apiUrl, document);
  }

  update(id: number, document: DocumentEleve): Observable<DocumentEleve> {
    return this.http.put<DocumentEleve>(`${this.apiUrl}/${id}`, document);
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/${id}`);
  }

  getByEleve(eleveId: number) {
    return this.http.get<any[]>(`http://localhost:8000/api/eleves/${eleveId}/documents`);
  }
} 