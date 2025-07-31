import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class NotesService {
  private baseUrl = 'http://localhost:8000/api/notes';

  constructor(private http: HttpClient) {}

  getAll(): Observable<any[]> {
    return this.http.get<any[]>(this.baseUrl);
  }

  getById(id: number): Observable<any> {
    return this.http.get<any>(`${this.baseUrl}/${id}`);
  }

  create(note: any): Observable<any> {
    return this.http.post<any>(this.baseUrl, note);
  }

  update(id: number, note: any): Observable<any> {
    return this.http.put<any>(`${this.baseUrl}/${id}`, note);
  }

  delete(id: number): Observable<any> {
    return this.http.delete<any>(`${this.baseUrl}/${id}`);
  }
} 