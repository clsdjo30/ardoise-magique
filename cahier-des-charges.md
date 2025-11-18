# Cahier des Charges : "L'Ardoise Magique"

**Version:** 2.0 (Restructuration avec EasyAdmin)
**Date:** 18 novembre 2025
**Auteur:** Clsdjo30
**Stack:** Symfony 7 (Monolithe) + EasyAdmin 4 + Bootstrap 5

---

## Table des Matières

1. [Contexte et Objectifs](#1-contexte-et-objectifs)
2. [Acteurs et Cas d'Usage](#2-acteurs-et-cas-dusage)
3. [Spécifications Techniques](#3-spécifications-techniques)
4. [Architecture des Données](#4-architecture-des-données)
5. [Architecture des Routes](#5-architecture-des-routes)
6. [Spécifications de Sécurité](#6-spécifications-de-sécurité)
7. [Spécifications UI/UX](#7-spécifications-uiux)
8. [Logique Fonctionnelle Clé](#8-logique-fonctionnelle-clé)
9. [Phase d'Implémentation](#9-phase-dimplémentation)

---

## 1. Contexte et Objectifs

### 1.1 Problématique

Les restaurateurs investissent un temps précieux chaque jour pour gérer leur **"Plat du Jour"** ou leur **"Ardoise"**. Le processus actuel est fragmenté et inefficace :

- **Saisie manuelle redondante** : Les données sont entrées séparément dans Word, le site web, la caisse
- **Pas de synchronisation** : Risque d'incohérence entre les différents supports
- **Faible optimisation numérique** : Diffusion compliquée sur les réseaux sociaux
- **Manque de centralisation** : Aucun point unique de contrôle

### 1.2 Solution Proposée

**L'Ardoise Magique** est une application web **Micro-SaaS** simple et centralisée qui permet à un restaurateur de :

- ✅ **Saisir une seule fois** son ardoise via une interface admin intuitive
- ✅ **Générer automatiquement** plusieurs formats de sortie :
  - Page web publique (affichage TV, iFrame)
  - Document PDF A4 (impression salle)
  - Image JPG optimisée (réseaux sociaux)
- ✅ **Partager en un clic** sur Facebook, Instagram, email
- ✅ **Activer/désactiver** l'ardoise à tout moment

### 1.3 Objectifs Portfolio

Ce projet démontre la maîtrise de :

- **Stack Symfony 7** en mode monolithe
- **EasyAdmin 4** pour une admin CRUD robuste et extensible
- **Génération de contenus dynamiques** (PDF, Images)
- **Cycle complet de développement** : Auth → CRUD → Génération → Partage
- **Sécurité** : Authentification, autorisation par propriété, CSRF, XSS
- **UX/UI moderne** : Bootstrap 5, modals, formulaires réactifs

---

## 2. Acteurs et Cas d'Usage

### 2.1 Acteurs

| Acteur | Rôle | Accès |
|--------|------|-------|
| **Restaurateur (Admin)** | Gère ses ardoises, les publie, les partage | Routes protégées `/admin` |
| **Client (Visiteur)** | Consulte l'ardoise active du restaurant | Routes publiques `/ardoise/{slug}` |
| **Système** | Génère PDF, images, gère la mise en cache | Logique métier interne |

### 2.2 Cas d'Usage - Restaurateur (Admin)

#### UC-A1 : Gestion de Compte
- L'admin crée un compte (nom, email, mot de passe)
- L'admin se connecte / se déconnecte
- L'admin peut modifier son profil (nom du restaurant, email)

#### UC-A2 : Consulter le Dashboard
- Après connexion, l'admin accède à `/admin`
- Dashboard affiche toutes les ardoises créées avec leur statut (active/inactive)
- Badges de statut visuels (vert = active, gris = inactive)

#### UC-A3 : Créer une Ardoise
- L'admin clique sur **"Créer une Ardoise"**
- Formulaire avec :
  - **Titre** (ex: "Menu de Noël", "Plats du 18/11")
  - **Sections** (Entrées, Plats, Desserts, Fromages...)
  - Pour chaque section : **liste de plats** (nom, description, prix)
- Gestion dynamique des sections et plats (ajouter/supprimer à la volée)

#### UC-A4 : Éditer une Ardoise
- L'admin clique sur **"Éditer"** depuis le dashboard
- Modification du titre, des sections et des plats
- Tri des sections et plats par drag-drop (optionnel, priorité basse)
- Sauvegarde en un clic

#### UC-A5 : Supprimer une Ardoise
- L'admin clique sur **"Supprimer"**
- Confirmation modal avant suppression
- Suppression avec cascade (sections et plats supprimés)

#### UC-A6 : Activer une Ardoise
- **Contrainte** : Une seule ardoise active à la fois
- L'admin clique **"Définir comme active"**
- Les autres ardoises du user passent automatiquement à `is_active = false`
- Message flash de confirmation

#### UC-A7 : Copier le Lien Public
- L'admin voit le lien public de l'ardoise active : `{domaine}/ardoise/{slug}`
- Bouton **"Copier le lien"** (clipboard)
- Notification toast de confirmation

#### UC-A8 : Partager l'Ardoise Active
- L'admin clique sur **"Partager"** (ne s'affiche que si une ardoise est active)
- Modal de partage avec 3 options :
  1. **Facebook** : Lien cliquable qui ouvre le partage Facebook
  2. **Instagram** : Bouton téléchargement de l'image JPG
  3. **Copier le lien** : Copie en clipboard le lien public

---

### 2.3 Cas d'Usage - Visiteur/Client (Public)

#### UC-V1 : Voir l'Ardoise Web
- Accès public à `/ardoise/{slug}`
- Affichage HTML de l'ardoise active avec style "craie" (CSS custom)
- Responsive, adapté au mobile et TV

#### UC-V2 : Télécharger l'Ardoise PDF
- Accès public à `/ardoise/{slug}/pdf`
- Génération PDF A4, optimisé impression
- Format professionnel avec logo/styling

#### UC-V3 : Télécharger l'Ardoise Image
- Accès public à `/ardoise/{slug}/image`
- Téléchargement image JPG haute résolution
- Format optimisé pour les réseaux sociaux

#### UC-V4 : Ardoise Inexistante
- Si slug n'existe pas ou aucune ardoise active
- Message amical : **"Aucune ardoise publiée pour le moment"**

---

## 3. Spécifications Techniques

### 3.1 Stack Confirmée

| Composant | Technologie | Version | Dev | Prod |
|-----------|-------------|---------|-----|------|
| **Framework** | Symfony | 7.x | ✅ | ✅ |
| **Admin CRUD** | EasyAdmin | 4.x | ✅ | ✅ |
| **ORM** | Doctrine | 2.x | ✅ | ✅ |
| **Templating** | Twig | 3.x | ✅ | ✅ |
| **Frontend** | Bootstrap | 5.x | ✅ | ✅ |
| **Authentification** | SecurityBundle | Native | ✅ | ✅ |
| **Génération PDF** | mpdf/mpdf | Latest | ✅ | ✅ |
| **Génération Image** | spatie/pdf-to-image | Latest | ✅ | ✅ |
| **Base de Données - Dev** | SQLite 3 | 3.x | ✅ | ❌ |
| **Base de Données - Prod** | MariaDB | 10.3+ | ❌ | ✅ |
| **Serveur** | O2Switch (Linux) | - | ❌ | ✅ |

### 3.2 Dépendances Principales

```bash
symfony/framework-bundle
symfony/security-bundle
symfony/form
symfony/validator
doctrine/orm
doctrine/doctrine-bundle
easyadmin/easyadmin-bundle
mpdf/mpdf
spatie/pdf-to-image
stof/doctrine-extensions-bundle  # Pour Slugs auto
bootstrap
jquery  # Pour Bootstrap JS
```

### 3.3 Prérequis Système

#### Développement (SQLite - Local)

- **PHP 8.2+** avec extensions :
  - `pdo_sqlite` (gestion SQLite) - **Obligatoire**
  - `gd` ou `imagick` (pour conversion PDF → JPG)
  - `zip` (pour dépendances)
- **Composer 2.x**
- **Node.js 18+** (optionnel, pour asset bundling)
- **SQLite 3** (généralement pré-installé sur Linux/Mac/Windows)

**Avantage :** Aucune installation de serveur BD. Le fichier `var/data.db` est créé automatiquement.

#### Production (O2Switch - MariaDB)

- **PHP 8.2+** avec extensions :
  - `pdo_mysql` ou `pdo_mariadb` (gestion MariaDB) - **Obligatoire**
  - `gd` ou `imagick` (pour conversion PDF → JPG)
  - `zip` (pour dépendances)
- **Composer 2.x**
- **MariaDB 10.3+** (fourni par O2Switch)
- **ImageMagick** activé (pour `spatie/pdf-to-image`)

### 3.4 Configuration Hébergement (O2Switch)

- Vérifier que **ImageMagick** ou **GD** est activé
- Vérifier que **MariaDB** est activé
- Répertoire `/tmp` writable pour fichiers temporaires
- Permissions : `var/cache`, `var/log`, `public/uploads` writable

---

## 4. Architecture des Données

### 4.1 Entité : User (Restaurateur)

```
User
├── id : int [PK]
├── email : string [UNIQUE]
├── roles : json (ex: ["ROLE_USER", "ROLE_ADMIN"])
├── password : string [HASHED]
├── nom_restaurant : string (ex: "Le Petit Nimois")
├── slug : string [UNIQUE] (généré auto via Gedmo\Sluggable)
├── created_at : datetime
├── updated_at : datetime
└── Relation 1:N → Ardoise
```

**Notes:**
- Le `slug` est généré automatiquement à partir de `nom_restaurant` via `Gedmo\Sluggable`
- Le `slug` est l'identifiant public du restaurant (visible dans l'URL)

### 4.2 Entité : Ardoise

```
Ardoise
├── id : int [PK]
├── titre : string (ex: "Ardoise du 18 Novembre")
├── is_active : boolean [default: false]
├── date_creation : datetime [auto]
├── date_modification : datetime [auto]
├── restaurateur : ManyToOne → User [REQUIRED]
└── Relation 1:N → Section [CASCADE DELETE]
```

**Notes:**
- `is_active = true` : Seule cette ardoise est visible publiquement
- Un User ne peut avoir qu'une seule Ardoise avec `is_active = true` (contrainte métier)

### 4.3 Entité : Section

```
Section
├── id : int [PK]
├── titre : string (ex: "Nos Entrées", "Plats Chauds")
├── ordre : int [default: 0]
├── ardoise : ManyToOne → Ardoise [REQUIRED]
└── Relation 1:N → Plat [CASCADE DELETE]
```

**Notes:**
- Le tri est géré par le champ `ordre`
- Suppression de l'Ardoise → Suppression en cascade des Sections et Plats

### 4.4 Entité : Plat

```
Plat
├── id : int [PK]
├── nom : string (ex: "Velouté de Potimarron")
├── description : string [NULLABLE] (ex: "et ses éclats de châtaigne")
├── prix : decimal(10,2) (en euros, ex: 12.50)
├── ordre : int [default: 0]
└── section : ManyToOne → Section [REQUIRED]
```

**Notes:**
- `prix` en euros (affichage : "12,50 €" en fr_FR)
- Tri par champ `ordre`

### 4.2 Diagramme ER Simplifié

```
┌─────────────────┐
│      USER       │
├─────────────────┤
│ id (PK)         │
│ email (UNIQUE)  │
│ password (hash) │
│ nom_restaurant  │
│ slug (UNIQUE)   │
└────────┬────────┘
         │ 1:N
         │
    ┌────▼───────────┐
    │    ARDOISE      │
    ├─────────────────┤
    │ id (PK)         │
    │ titre           │
    │ is_active       │
    │ restaurateur_id │
    └────────┬────────┘
             │ 1:N
        ┌────▼──────────┐
        │   SECTION      │
        ├────────────────┤
        │ id (PK)        │
        │ titre          │
        │ ordre          │
        │ ardoise_id     │
        └────────┬───────┘
                 │ 1:N
            ┌────▼────────┐
            │     PLAT     │
            ├──────────────┤
            │ id (PK)      │
            │ nom          │
            │ description  │
            │ prix         │
            │ ordre        │
            │ section_id   │
            └──────────────┘
```

---

## 5. Architecture des Routes

### 5.1 Routes Publiques (Pas d'authentification)

| Méthode | Route | Nom | Contrôleur | Description |
|---------|-------|-----|-----------|-------------|
| GET | `/ardoise/{slug}` | `app_public_show_web` | PublicController::showWeb | Affiche l'ardoise web (HTML) |
| GET | `/ardoise/{slug}/pdf` | `app_public_show_pdf` | PublicController::showPdf | Génère et retourne le PDF |
| GET | `/ardoise/{slug}/image` | `app_public_show_image` | PublicController::showImage | Génère et retourne l'image JPG |

### 5.2 Routes d'Authentification

| Méthode | Route | Nom | Contrôleur | Description |
|---------|-------|-----|-----------|-------------|
| GET | `/register` | `app_register` | RegisterController::register | Formulaire d'inscription |
| POST | `/register` | - | RegisterController::register | Traitement inscription |
| GET | `/login` | `app_login` | SecurityController::login | Formulaire de connexion |
| POST | `/login` | - | - | Traitement login (Symfony natif) |
| GET | `/logout` | `app_logout` | - | Déconnexion (Symfony natif) |

### 5.3 Routes Administrateur (Protégées : is_granted('ROLE_USER'))

#### Dashboard

| Méthode | Route | Nom | Contrôleur | Description |
|---------|-------|-----|-----------|-------------|
| GET | `/admin` | `app_admin_dashboard` | Admin\DashboardController::index | Tableau de bord (liste ardoises) |

#### CRUD Ardoise (via EasyAdmin)

| Méthode | Route | Nom | Contrôleur | Description |
|---------|-------|-----|-----------|-------------|
| GET/POST | `/admin/ardoise/new` | `easyadmin_new` | EasyAdminController (auto) | Créer une ardoise |
| GET/POST | `/admin/ardoise/{id}/edit` | `easyadmin_edit` | EasyAdminController (auto) | Éditer une ardoise |
| DELETE | `/admin/ardoise/{id}` | `easyadmin_delete` | EasyAdminController (auto) | Supprimer une ardoise |
| POST | `/admin/ardoise/{id}/toggle-active` | `app_admin_ardoise_toggle_active` | Admin\ArdoiseController::toggleActive | Activer/désactiver |

#### Compte Utilisateur (optionnel)

| Méthode | Route | Nom | Contrôleur | Description |
|---------|-------|-----|-----------|-------------|
| GET/POST | `/admin/profile` | `app_admin_profile` | Admin\ProfileController::edit | Modifier profil |

---

## 6. Spécifications de Sécurité

### 6.1 Authentification

- **Système** : `Symfony\Component\Security\Http\Authentication\AuthenticationUtils`
- **Mots de passe** : Hashés via `UserPasswordHasherInterface` (bcrypt, argon2id)
- **Sessions** : Gérées par Symfony (cookies sécurisés)

### 6.2 Autorisation (Contrôle d'Accès)

**Règles générales:**

1. Routes `/admin/*` exigent `ROLE_USER`
2. Seul le propriétaire d'une Ardoise peut la modifier/supprimer/activer
3. Les routes publiques `/ardoise/*` sont accessibles sans authentification

**Implémentation:**

- Vérification par **Voter** Symfony ou if-check dans le contrôleur :
  ```php
  if ($ardoise->getRestaurateur() !== $this->getUser()) {
      throw new AccessDeniedException('Vous n\'avez pas accès à cette ardoise.');
  }
  ```

### 6.3 Protection CSRF

- Tous les formulaires incluent un token CSRF (par défaut dans Symfony)
- Validé automatiquement par `CsrfTokenManagerInterface`

### 6.4 Protection XSS

- **Échappement** : Twig échappe par défaut (`{{ variable }}`)
- **Filtre |raw** : Utilisé avec prudence (contenu de confiance uniquement)
- **Sanitization** : Les descriptions de plats sont échappées en sortie

### 6.5 Protection HTTPS

- Déploiement : **HTTPS obligatoire** en production
- Redirection HTTP → HTTPS au niveau serveur
- Headers sécurité : `X-Content-Type-Options`, `X-Frame-Options`, etc.

---

## 7. Spécifications UI/UX

### 7.1 Design System

- **Framework CSS** : Bootstrap 5
- **Thème** : Light (par défaut)
- **Palette** : Vert (primary), Gris (secondary), Rouge (danger)
- **Font** : -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto
- **Responsive** : Mobile-first, breakpoints BS5 standards

### 7.2 Layout Global (`base.html.twig`)

```html
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}L'Ardoise Magique{% endblock %}</title>
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">🧞 L'Ardoise Magique</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    {% if app.user %}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ path('app_admin_dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ path('app_logout') }}">Déconnexion</a>
                        </li>
                    {% else %}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ path('app_login') }}">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ path('app_register') }}">Inscription</a>
                        </li>
                    {% endif %}
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="container mt-3">
        {% for type, messages in app.flashes %}
            {% for message in messages %}
                <div class="alert alert-{{ type == 'success' ? 'success' : 'danger' }} alert-dismissible fade show">
                    {{ message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            {% endfor %}
        {% endfor %}
    </div>

    <!-- Main Content -->
    <main class="container my-4">
        {% block content %}{% endblock %}
    </main>

    <!-- Footer -->
    <footer class="bg-light border-top mt-5 py-3">
        <div class="container text-center text-muted">
            <p>&copy; 2025 L'Ardoise Magique. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
```

### 7.3 Page de Connexion (`security/login.html.twig`)

```html
{% extends "base.html.twig" %}

{% block title %}Connexion - L'Ardoise Magique{% endblock %}

{% block content %}
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4">Connexion</h2>

                {% if error %}
                    <div class="alert alert-danger">{{ error.messageKey|trans(error.messageData, 'security') }}</div>
                {% endif %}

                <form method="post" action="{{ path('app_login') }}">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ last_username }}" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <input type="hidden" name="_csrf_token" value="{{ csrf_token('authenticate') }}">

                    <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                </form>

                <p class="text-center mt-3">
                    Pas encore inscrit? <a href="{{ path('app_register') }}">Créer un compte</a>
                </p>
            </div>
        </div>
    </div>
</div>
{% endblock %}
```

### 7.4 Dashboard Admin (`admin/dashboard/index.html.twig`)

```html
{% extends "base.html.twig" %}

{% block title %}Dashboard - L'Ardoise Magique{% endblock %}

{% block content %}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>📋 Mes Ardoises</h1>
    <a href="{{ path('easyadmin_new', {'entity': 'Ardoise'}) }}" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Créer une Ardoise
    </a>
</div>

{% if ardoises|length > 0 %}
    <div class="list-group">
        {% for ardoise in ardoises %}
            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                <div>
                    <h5 class="mb-1">{{ ardoise.titre }}</h5>
                    <small class="text-muted">
                        Créée le {{ ardoise.date_creation|date('d/m/Y') }}
                        • {{ ardoise.sections|length }} section(s)
                    </small>
                    {% if ardoise.isActive %}
                        <span class="badge bg-success ms-2">Active</span>
                    {% else %}
                        <span class="badge bg-secondary ms-2">Inactive</span>
                    {% endif %}
                </div>

                <div class="btn-group" role="group">
                    {% if ardoise.isActive %}
                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#shareModal" data-slug="{{ app.user.slug }}">
                            <i class="bi bi-share"></i> Partager
                        </button>
                    {% else %}
                        <form method="POST" action="{{ path('app_admin_ardoise_toggle_active', {id: ardoise.id}) }}" style="display:inline;">
                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-lightning"></i> Activer
                            </button>
                        </form>
                    {% endif %}

                    <a href="{{ path('easyadmin_edit', {'entity': 'Ardoise', 'id': ardoise.id}) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-pencil"></i> Éditer
                    </a>

                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ ardoise.id }}">
                        <i class="bi bi-trash"></i> Supprimer
                    </button>
                </div>
            </div>

            <!-- Modal Suppression -->
            <div class="modal fade" id="deleteModal{{ ardoise.id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmer la suppression</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            Êtes-vous sûr de vouloir supprimer <strong>{{ ardoise.titre }}</strong>?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <form method="POST" action="{{ path('easyadmin_delete', {'entity': 'Ardoise', 'id': ardoise.id}) }}" style="display:inline;">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="_csrf_token" value="{{ csrf_token('delete') }}">
                                <button type="submit" class="btn btn-danger">Supprimer</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        {% endfor %}
    </div>

    <!-- Modal Partage -->
    <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shareModalLabel">🚀 Partager l'Ardoise Active</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">Choisissez comment diffuser votre ardoise :</p>

                    <!-- Lien Public -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">📎 Lien Public (iFrame, Email)</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="publicLink" value="{{ url('app_public_show_web', {slug: app.user.slug}) }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('#publicLink')">
                                <i class="bi bi-clipboard"></i> Copier
                            </button>
                        </div>
                    </div>

                    <!-- Partage Rapide -->
                    <div class="d-grid gap-2">
                        <h6 class="text-muted">Partage Rapide</h6>

                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ url('app_public_show_web', {slug: app.user.slug}) }}"
                           target="_blank" class="btn btn-primary">
                            <i class="bi bi-facebook"></i> Partager sur Facebook
                        </a>

                        <a href="{{ path('app_public_show_image', {slug: app.user.slug}) }}"
                           download="ardoise-{{ "now"|date("Y-m-d") }}.jpg" class="btn btn-warning text-dark">
                            <i class="bi bi-image"></i> Télécharger pour Instagram
                        </a>

                        <a href="{{ path('app_public_show_pdf', {slug: app.user.slug}) }}"
                           target="_blank" class="btn btn-danger">
                            <i class="bi bi-file-pdf"></i> Voir le PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
{% else %}
    <div class="alert alert-info text-center py-5">
        <h5>Aucune ardoise créée</h5>
        <p class="mb-0">Commencez par <a href="{{ path('easyadmin_new', {'entity': 'Ardoise'}) }}">créer votre première ardoise</a>.</p>
    </div>
{% endif %}

<script>
function copyToClipboard(selector) {
    const element = document.querySelector(selector);
    element.select();
    document.execCommand('copy');
    alert('Lien copié!');
}
</script>
{% endblock %}
```

### 7.5 Page Publique Web (`public/show_web.html.twig`)

```html
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ardoise.titre }} - {{ ardoise.restaurateur.nom_restaurant }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
            font-family: 'Comic Sans MS', cursive, sans-serif;
        }
        .ardoise-container {
            background: #fffef0;
            border: 4px solid #8b7355;
            border-radius: 8px;
            padding: 2rem;
            max-width: 700px;
            margin: 2rem auto;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .ardoise-title {
            text-align: center;
            font-size: 3rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 2rem;
            text-decoration: underline wavy;
        }
        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #8b7355;
            border-bottom: 2px dashed #8b7355;
            padding-bottom: 0.5rem;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }
        .plat {
            margin-bottom: 1.2rem;
        }
        .plat-nom {
            font-weight: bold;
            font-size: 1.1rem;
        }
        .plat-description {
            font-size: 0.9rem;
            color: #666;
            font-style: italic;
        }
        .plat-prix {
            text-align: right;
            font-weight: bold;
            color: #c41e3a;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="ardoise-container">
        <div class="ardoise-title">🧞 {{ ardoise.titre }}</div>

        {% for section in ardoise.sections %}
            <div class="section-title">{{ section.titre }}</div>
            {% for plat in section.plats %}
                <div class="plat">
                    <div class="plat-nom">{{ plat.nom }}</div>
                    {% if plat.description %}
                        <div class="plat-description">{{ plat.description }}</div>
                    {% endif %}
                    <div class="plat-prix">{{ plat.prix|number_format(2, ',', ' ') }} €</div>
                </div>
            {% endfor %}
        {% endfor %}

        <div style="text-align: center; margin-top: 2rem; color: #999; font-size: 0.9rem;">
            <p>© {{ ardoise.restaurateur.nom_restaurant }}</p>
        </div>
    </div>
</body>
</html>
```

### 7.6 Template PDF (`public/show_pdf.html.twig`)

Identique au template web, optimisé pour l'impression (pas de dégradé background, police standard).

---

## 8. Logique Fonctionnelle Clé

### 8.1 Génération PDF

**Flux :**
1. Utilisateur accède à `/ardoise/{slug}/pdf`
2. PublicController::showPdf() récupère l'Ardoise active
3. Rendre le template `public/show_pdf.html.twig` en HTML
4. Utiliser `mpdf` pour convertir HTML → PDF
5. Retourner une BinaryFileResponse au navigateur

**Code du Contrôleur :**

```php
public function showPdf(string $slug, UserRepository $userRepository): Response
{
    $user = $userRepository->findOneBy(['slug' => $slug]);
    if (!$user || !$user->getActiveArdoise()) {
        throw $this->createNotFoundException('Ardoise non trouvée');
    }

    $ardoise = $user->getActiveArdoise();
    $html = $this->renderView('public/show_pdf.html.twig', [
        'ardoise' => $ardoise,
    ]);

    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML($html);

    return new Response(
        $mpdf->Output('ardoise.pdf', 'S'),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="ardoise.pdf"',
        ]
    );
}
```

### 8.2 Génération Image (PDF → JPG)

**Flux :**
1. Utilisateur accède à `/ardoise/{slug}/image`
2. PublicController::showImage() récupère l'Ardoise active
3. Générer le PDF en mémoire (voir 8.1)
4. Sauvegarder le PDF dans `/tmp` temporaire
5. Convertir le PDF en JPG via `spatie/pdf-to-image`
6. Retourner une BinaryFileResponse en téléchargement
7. Nettoyer les fichiers temporaires

**Code du Contrôleur :**

```php
public function showImage(string $slug, UserRepository $userRepository): Response
{
    $user = $userRepository->findOneBy(['slug' => $slug]);
    if (!$user || !$user->getActiveArdoise()) {
        throw $this->createNotFoundException('Ardoise non trouvée');
    }

    $ardoise = $user->getActiveArdoise();
    $html = $this->renderView('public/show_pdf.html.twig', [
        'ardoise' => $ardoise,
    ]);

    // Générer PDF
    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML($html);
    $tempPdf = tempnam(sys_get_temp_dir(), 'ardoise_') . '.pdf';
    file_put_contents($tempPdf, $mpdf->Output('', 'S'));

    // Convertir en JPG
    $tempJpg = tempnam(sys_get_temp_dir(), 'ardoise_') . '.jpg';
    $pdf = new Spatie\PdfToImage\Pdf($tempPdf);
    $pdf->saveImage($tempJpg);

    // Retourner le fichier
    $response = new BinaryFileResponse($tempJpg);
    $response->setContentDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        'ardoise-' . date('Y-m-d') . '.jpg'
    );

    // Nettoyer les temporaires après l'envoi
    register_shutdown_function(function() use ($tempPdf, $tempJpg) {
        @unlink($tempPdf);
        @unlink($tempJpg);
    });

    return $response;
}
```

### 8.3 Activation d'une Ardoise

**Contrainte :** Une seule ardoise active par User.

**Flux :**
1. Utilisateur clique "Activer" sur une ardoise
2. ArdoiseController::toggleActive() vérifie la propriété
3. Commencer une transaction Doctrine
4. Récupérer toutes les ardoises du User
5. Passer les autres à `is_active = false`
6. Passer l'ardoise cible à `is_active = true`
7. Flush Doctrine
8. Rediriger vers le dashboard avec message flash

**Code :**

```php
public function toggleActive(Ardoise $ardoise, EntityManagerInterface $em): Response
{
    // Vérifier la propriété
    if ($ardoise->getRestaurateur() !== $this->getUser()) {
        throw new AccessDeniedException();
    }

    $user = $this->getUser();

    $em->beginTransaction();
    try {
        // Désactiver les autres ardoises
        foreach ($user->getArdoises() as $other) {
            if ($other->getId() !== $ardoise->getId()) {
                $other->setIsActive(false);
            }
        }

        // Activer l'ardoise cible
        $ardoise->setIsActive(true);

        $em->flush();
        $em->commit();

        $this->addFlash('success', 'Ardoise activée !');
    } catch (\Exception $e) {
        $em->rollback();
        $this->addFlash('error', 'Erreur lors de l\'activation.');
    }

    return $this->redirectToRoute('app_admin_dashboard');
}
```

### 8.4 Génération Automatique du Slug

Utiliser **Gedmo\Sluggable** via `stof/doctrine-extensions-bundle`.

**Configuration (config/packages/stof_doctrine_extensions.yaml) :**

```yaml
stof_doctrine_extensions:
    default_locale: fr_FR
    orm:
        default:
            sluggable: true

doctrine:
    orm:
        metadata_cache_driver: cache.doctrine.orm.metadata
        auto_generate_proxy_classes: '%kernel.debug%'
        entity_managers:
            default:
                metadata_cache_driver: cache.doctrine.orm.metadata
                naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
                quote_strategy: doctrine.orm.quote_strategy.ansi
                auto_mapping: true
```

**Annotation dans l'entité User :**

```php
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User
{
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Gedmo\Slug(fields: ['nom_restaurant'])]
    private string $slug;

    #[ORM\Column(type: 'string', length: 255)]
    private string $nom_restaurant;

    // ...
}
```

---

## 9. Phase d'Implémentation

### 9.1 Étape 1 : Setup Projet (Jour 1)

```bash
symfony new ardoise-magique --webapp
cd ardoise-magique

# Ajouter les dépendances
composer require symfony/security-bundle
composer require easyadmin/easyadmin-bundle
composer require doctrine/doctrine-bundle
composer require mpdf/mpdf
composer require spatie/pdf-to-image
composer require stof/doctrine-extensions-bundle

# ======================================
# ✅ CONFIGURATION DÉVELOPPEMENT (SQLite)
# ======================================

# Éditer .env et remplacer la ligne DATABASE_URL par :
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"

# Vérifier que pdo_sqlite est disponible
php -m | grep pdo_sqlite

# Générer authentification
php bin/console make:auth

# Générer les entités
php bin/console make:entity User
php bin/console make:entity Ardoise
php bin/console make:entity Section
php bin/console make:entity Plat

# Migrations
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate

# ✅ SQLite DB créée automatiquement dans var/data.db
```

### 9.1b : Configuration Production (O2Switch - MariaDB)

**À faire lors du déploiement en production :**

```bash
# En production, éditer le fichier .env.production et configurer :
DATABASE_URL="mysql://user:password@db.o2switch.fr:3306/databasename"

# Vérifier que pdo_mysql est disponible
php -m | grep pdo_mysql

# Créer et migrer la base de données
php bin/console doctrine:database:create --env=prod
php bin/console doctrine:migrations:migrate --env=prod --no-interaction
```

**Voir la section 📡 Déploiement (O2Switch) pour les détails complets.**

### 9.2 Étape 2 : Authentification (Jour 1-2)

- [ ] Créer formulaire d'inscription
- [ ] Créer formulaire de connexion
- [ ] Configurer SecurityBundle
- [ ] Tester login/logout
- [ ] Ajouter validation sur User (email unique, mot de passe fort)

### 9.3 Étape 3 : CRUD Ardoises via EasyAdmin (Jour 2-3)

- [ ] Configurer EasyAdmin avec les entités
- [ ] Créer DashboardController
- [ ] Configurer CrudController pour Ardoise
- [ ] Ajouter actions custom (toggle-active)
- [ ] Implémenter la gestion dynamique des sections/plats

### 9.4 Étape 4 : Routes Publiques (Jour 3)

- [ ] Créer PublicController
- [ ] Implémenter showWeb()
- [ ] Implémenter showPdf()
- [ ] Implémenter showImage()

### 9.5 Étape 5 : Génération PDF et Image (Jour 4)

- [ ] Tester mpdf
- [ ] Tester spatie/pdf-to-image
- [ ] Optimiser templates PDF/Image
- [ ] Gérer erreurs et edge cases

### 9.6 Étape 6 : UI/UX et Partage (Jour 4-5)

- [ ] Styliser le dashboard
- [ ] Styliser les pages publiques
- [ ] Implémenter modal de partage
- [ ] Tester les boutons de partage (Facebook, Instagram)
- [ ] Ajouter notifications toast

### 9.7 Étape 7 : Sécurité et Tests (Jour 5-6)

- [ ] Tests unitaires des contrôleurs
- [ ] Tests d'intégration (CRUD, activation)
- [ ] Vérifier la sécurité (CSRF, XSS, Ownership)
- [ ] Tester sur O2Switch (déploiement)
- [ ] Vérifier ImageMagick et dépendances

### 9.8 Étape 8 : Optimisations et Polish (Jour 6-7)

- [ ] Mise en cache des images générées (optionnel)
- [ ] Optimisation des requêtes DB
- [ ] SEO sur pages publiques
- [ ] Documentation code
- [ ] Livraison finale

---

## 10. Points de Vigilance & Notes

### 10.0 SQLite (Développement) vs MariaDB (Production)

#### ✅ Développement - SQLite

- **Avantages :** Installation zéro config, fichier local `var/data.db`, parfait pour le dev local
- **Configuration :** `DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"` (dans `.env`)
- **Fichier `var/data.db`** : Ignoré par `.gitignore`, persiste en local
- **Avantages pour dev :** Rapide, aucun serveur requis, facile à réinitialiser
- **⚠️ Limitation SQLite :** Pas de multi-user concurrent optimal, limité pour haute charge

#### 🚀 Production - MariaDB (O2Switch)

- **Configuration :** `DATABASE_URL="mysql://user:pass@db.o2switch.fr:3306/db_name"` (dans `.env` production)
- **Extension PHP requise :** `pdo_mysql` ou `pdo_mariadb` (obligatoire)
- **Avantages MariaDB :** Multi-user concurrent, haute performance, backups, scalabilité
- **Données développement (SQLite)** : Ne jamais migrer en production. Chaque environnement a sa propre BD.
- **Gestion des migrations :** Même schéma Doctrine pour les deux, seule la connexion change

#### Migration SQLite → MariaDB

```
1. Développement en local : Utiliser SQLite (var/data.db)
2. Déploiement production : Créer MariaDB vierge sur O2Switch
3. Exécuter migrations production : php bin/console doctrine:migrations:migrate --env=prod
4. NE JAMAIS exporter les données SQLite vers MariaDB en production
5. Production repart avec une base vierge, utilisateurs créés via formulaire d'inscription
```

### 10.1 Imagick / ImageMagick sur O2Switch

- **Prérequis** : ImageMagick doit être installé sur le serveur
- **Alternative** : Utiliser GD Library si ImageMagick n'est pas dispo
- **Test** : `php -m | grep imagick`

### 10.2 Limite de Taille de Fichier

- PDF généré : ~500KB max (attention mémoire)
- JPG généré : ~300KB max
- Vérifier `upload_max_filesize` et `post_max_size` en php.ini

### 10.3 Génération Image Asynchrone (Future Enhancement)

Si la génération d'image est trop lente :
- Utiliser une job queue (Messenger + RabbitMQ ou Redis)
- Générer l'image en arrière-plan
- Servir une image cachetée

### 10.4 Multi-restaurant (Future)

Actuellement : 1 restaurateur = 1 compte.
Évolutif vers :
- Multi-user par restaurant (gérants, cuisiniers)
- Permissions granulaires
- Branding personnalisé

---

## 11. Livrables

### Code

- ✅ Projet Symfony 7 avec EasyAdmin complet
- ✅ Tous les contrôleurs (Auth, Admin, Public)
- ✅ Templates Twig (Admin + Public)
- ✅ Styles CSS Bootstrap 5 custom
- ✅ Migrations Doctrine

### Documentation

- ✅ Ce cahier des charges (markdown)
- ✅ README.md pour installation
- ✅ Commentaires inline dans le code

### Déploiement

- ✅ Configuration O2Switch
- ✅ Variables d'environnement (.env)
- ✅ CI/CD optionnel (GitHub Actions)

---

## 12. Contacts & Support

**Développeur:** Clsdjo30
**Date de création:** 18 novembre 2025
**Dernière mise à jour:** 18 novembre 2025

---

**Fin du Cahier des Charges**
