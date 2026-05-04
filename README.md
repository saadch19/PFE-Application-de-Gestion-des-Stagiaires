# Application de Gestion des Stagiaires (PFE)

Projet de fin d'etudes (PFE) realise par Saad Chaoui et Soumia EL-MZABI (FST-Fes) lors d'un stage chez Alten Maroc.

## Contexte

L'entreprise souhaite mettre en place une application web afin d'ameliorer la gestion des stagiaires, leur affectation ainsi que la communication entre les differents acteurs (RH, encadrants, stagiaires).

Problemes actuels :

- Gestion manuelle des stagiaires
- Difficulte de suivi
- Absence d'une communication centralisee

## Objectifs

- Automatiser la gestion des stagiaires
- Simplifier l'affectation des stages et des encadrants
- Assurer un suivi efficace des stagiaires
- Mettre a disposition un espace de communication

## Acteurs

- Administrateur
- Responsable de competence
- Encadrant
- Stagiaire

## Besoins fonctionnels

Gestion des stagiaires :

- Ajouter, modifier et supprimer un stagiaire
- Consulter la liste des stagiaires
- Archiver les stagiaires

Gestion des stages :

- Creer un stage
- Affecter un stagiaire a un stage
- Affecter un encadrant

Gestion des utilisateurs :

- Creer des comptes utilisateurs
- Gerer les roles
- Modifier les mots de passe

Suivi et communication :

- Messagerie interne
- Gestion des taches
- Suivi des absences

Autres fonctionnalites :

- Generation automatique d'attestations
- Gestion des demandes (prolongation, attestation, etc.)

## Besoins techniques

- Frontend : HTML, CSS, Bootstrap, JavaScript, jQuery
- Backend : PHP (Laravel)
- Base de donnees : MySQL
- AJAX pour les interactions dynamiques
- Architecture : MVC
- Versioning : Git

## User stories (resume)

Administrateur :

- Gerer les comptes utilisateurs
- Affecter les encadrants
- Consulter les demandes
- Archiver les stagiaires

Responsable de competence :

- Ajouter des stagiaires
- Affecter un stage
- Affecter un encadrant
- Suivre les absences
- Gerer les informations liees aux stages
- Assurer le suivi administratif

Encadrant :

- Attribuer des taches
- Suivre les stagiaires
- Valider la fin du stage

Stagiaire :

- Consulter les taches
- Envoyer des demandes
- Consulter les informations

## Installation (local)

Prerequis : PHP, Composer, MySQL.

1) Installer les dependances :

```bash
composer install
```

2) Configurer l'environnement :

- Copier `.env.example` vers `.env`
- Configurer les variables de base de donnees

3) Generer la cle :

```bash
php artisan key:generate
```

4) Migrer et initialiser :

```bash
php artisan migrate --seed
```

5) Lancer le serveur :

```bash
php artisan serve
```

## Comptes de demonstration

Les seeders creent des comptes de demo. Exemple :

- admin@internships.local / password123

## Dossier important

- `routes/web.php` : definition des routes
- `app/Http/Controllers` : logique metier
- `app/Models` : modele Eloquent
- `resources/views` : vues Blade
- `database/migrations` : schema
- `database/seeders` : donnees de demo
