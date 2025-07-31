import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class AbsencesService {
  private baseUrl = 'http://localhost:8000/api/absences';

  constructor(private http: HttpClient) {}

  getAll(): Observable<any[]> {
    return this.http.get<any[]>(this.baseUrl);
  }

  getById(id: number): Observable<any> {
    return this.http.get<any>(`${this.baseUrl}/${id}`);
  }

  create(absence: any): Observable<any> {
    return this.http.post<any>(this.baseUrl, absence);
  }

  update(id: number, absence: any): Observable<any> {
    return this.http.put<any>(`${this.baseUrl}/${id}`, absence);
  }

  delete(id: number): Observable<any> {
    return this.http.delete<any>(`${this.baseUrl}/${id}`);
  }
} 