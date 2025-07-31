import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class DocumentsService {
  private baseUrl = 'http://localhost:8000/api/documents';

  constructor(private http: HttpClient) {}

  getAll(): Observable<any[]> {
    return this.http.get<any[]>(this.baseUrl);
  }

  getById(id: number): Observable<any> {
    return this.http.get<any>(`${this.baseUrl}/${id}`);
  }

  create(document: any): Observable<any> {
    return this.http.post<any>(this.baseUrl, document);
  }

  update(id: number, document: any): Observable<any> {
    return this.http.put<any>(`${this.baseUrl}/${id}`, document);
  }

  delete(id: number): Observable<any> {
    return this.http.delete<any>(`${this.baseUrl}/${id}`);
  }
} 