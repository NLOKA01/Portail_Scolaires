import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class ClassesService {
  private baseUrl = 'http://localhost:8000/api/classes';

  constructor(private http: HttpClient) {}

  getAll(): Observable<any[]> {
    return this.http.get<any[]>(this.baseUrl);
  }

  getById(id: number): Observable<any> {
    return this.http.get<any>(`${this.baseUrl}/${id}`);
  }

  create(classe: any): Observable<any> {
    return this.http.post<any>(this.baseUrl, classe);
  }

  update(id: number, classe: any): Observable<any> {
    return this.http.put<any>(`${this.baseUrl}/${id}`, classe);
  }

  delete(id: number): Observable<any> {
    return this.http.delete<any>(`${this.baseUrl}/${id}`);
  }
} 