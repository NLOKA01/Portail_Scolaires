import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ParentUser } from '../models/parent-user.model';

@Injectable({ providedIn: 'root' })
export class ParentsService {
  private apiUrl = 'http://localhost:8000/api/parents'; // Adapter l'URL si besoin

  constructor(private http: HttpClient) {}

  getAll(): Observable<ParentUser[]> {
    return this.http.get<ParentUser[]>(this.apiUrl);
  }

  getById(id: number): Observable<ParentUser> {
    return this.http.get<ParentUser>(`${this.apiUrl}/${id}`);
  }

  create(parent: ParentUser): Observable<ParentUser> {
    return this.http.post<ParentUser>(this.apiUrl, parent);
  }

  update(id: number, parent: ParentUser): Observable<ParentUser> {
    return this.http.put<ParentUser>(`${this.apiUrl}/${id}`, parent);
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/${id}`);
  }
} 