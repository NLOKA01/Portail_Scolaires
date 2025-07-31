import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AuthService } from '../services/auth.service';
import { ElevesService } from '../services/eleves.service';
import { EnseignantsService } from '../services/enseignants.service';
import { ClassesService } from '../services/classes.service';
import { MatieresService } from '../services/matieres.service';
import { NotesService } from '../services/notes.service';
import { BulletinsService } from '../services/bulletins.service';
import { AbsencesService } from '../services/absences.service';
import { DocumentsService } from '../services/documents.service';
import { ParentsService } from '../services/parents.service';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.css'
})
export class DashboardComponent implements OnInit {
  stats = {
    eleves: 0,
    enseignants: 0,
    classes: 0,
    matieres: 0,
    notes: 0,
    bulletins: 0,
    absences: 0,
    documents: 0,
    parents: 0
  };

  constructor(
    private authService: AuthService,
    private elevesService: ElevesService,
    private enseignantsService: EnseignantsService,
    private classesService: ClassesService,
    private matieresService: MatieresService,
    private notesService: NotesService,
    private bulletinsService: BulletinsService,
    private absencesService: AbsencesService,
    private documentsService: DocumentsService,
    private parentsService: ParentsService
  ) {}

  ngOnInit(): void {
    this.elevesService.getAll().subscribe((data: any[]) => this.stats.eleves = data.length);
    this.enseignantsService.getAll().subscribe((data: any[]) => this.stats.enseignants = data.length);
    this.classesService.getAll().subscribe((data: any[]) => this.stats.classes = data.length);
    this.matieresService.getAll().subscribe((data: any[]) => this.stats.matieres = data.length);
    this.notesService.getAll().subscribe((data: any[]) => this.stats.notes = data.length);
    this.bulletinsService.getAll().subscribe((data: any[]) => this.stats.bulletins = data.length);
    this.absencesService.getAll().subscribe((data: any[]) => this.stats.absences = data.length);
    this.documentsService.getAll().subscribe((data: any[]) => this.stats.documents = data.length);
    this.parentsService.getAll().subscribe((data: any[]) => this.stats.parents = data.length);
  }

  isLoggedIn(): boolean {
    return !!localStorage.getItem('token');
  }
} 