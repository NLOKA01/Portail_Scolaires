import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { map, catchError } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class MatieresService {
  private baseUrl = 'http://localhost:8000/api/matieres';

  constructor(private http: HttpClient) {}

  getAll(): Observable<any[]> {
    return this.http.get<any>(this.baseUrl).pipe(
      map(res => res.data),
      catchError(this.handleError)
    );
  }

  getById(id: number): Observable<any> {
    return this.http.get<any>(`${this.baseUrl}/${id}`).pipe(
      map(res => res.data),
      catchError(this.handleError)
    );
  }

  create(matiere: any): Observable<any> {
    return this.http.post<any>(this.baseUrl, matiere).pipe(
      map(res => res.data),
      catchError(this.handleError)
    );
  }

  update(id: number, matiere: any): Observable<any> {
    return this.http.put<any>(`${this.baseUrl}/${id}`, matiere).pipe(
      map(res => res.data),
      catchError(this.handleError)
    );
  }

  delete(id: number): Observable<any> {
    return this.http.delete<any>(`${this.baseUrl}/${id}`).pipe(
      map(res => res.data),
      catchError(this.handleError)
    );
  }

  private handleError(error: any) {
    let msg = error?.error?.message || 'Erreur serveur';
    return throwError(() => msg);
  }
} 