<<<<<<< HEAD
# L'Ardoise Magique

**Version 2.1** - Systeme de gestion de menus avec interface EasyAdmin a onglets

Un systeme moderne et elegant pour gerer des menus de restaurant complexes (ardoises) avec une interface d'administration intuitive basee sur EasyAdmin.

## Fonctionnalites Principales

### Interface Administration (EasyAdmin)
- **Workflow a 3 onglets** pour une gestion simplifiee :
  1. **Configuration** : Titre et mise en ligne
  2. **Tarification** : Prix du menu complet et formules optionnelles
  3. **Composition** : Gestion illimitee de sections et plats

### Gestion de Menus Complexes
- Support de menus multi-services (ex: Menu Degustation 7 Services)
- Sections personnalisables (Mise en bouche, Entrees, Plats, Desserts...)
- Ordre d'affichage configurable
- Prix supplementaires optionnels par plat
- Descriptions detaillees pour chaque plat

### Affichage Public
- Interface elegante avec design glassmorphism
- Liste des ardoises actives sous forme de cartes
- Vue detaillee avec separateurs de sections
- Responsive design (mobile, tablette, desktop)
- Affichage conditionnel des prix et formules

## Installation et Demarrage

### Prerequis
- PHP 8.2 ou superieur
- Composer
- Symfony CLI (optionnel mais recommande)

### Installation

1. **Cloner le projet**
```bash
git clone <repository-url>
cd ardoise-magique
```

2. **Installer les dependances**
```bash
composer install
```

3. **Configurer la base de donnees**

Editez le fichier `.env` et decommentez la ligne SQLite :
```env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_%kernel.environment%.db"
```

Commentez les autres configurations de base de donnees (PostgreSQL, MySQL).

4. **Creer la base de donnees et executer les migrations**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. **Demarrer le serveur de developpement**
```bash
symfony serve
# OU
php -S localhost:8000 -t public/
```

6. **Acceder a l'application**
- Interface publique : http://localhost:8000
- Interface admin : http://localhost:8000/admin

## Guide d'Utilisation

### Creer une Ardoise (Menu)

1. Accedez a `/admin` et cliquez sur "Ardoises"
2. Cliquez sur "Creer Ardoise"

#### Onglet 1 : Configuration
- Saisissez le **titre** (ex: "Menu Prestige d'Automne")
- Definissez la **date de creation**
- Cochez **"Mettre en ligne"** pour publier

#### Onglet 2 : Tarifs & Formules
- **Prix Menu Complet** : Prix pour l'ensemble du menu (ex: 85€)
- **Formule Entree + Plat** : Prix formule courte A (optionnel)
- **Formule Plat + Dessert** : Prix formule courte B (optionnel)
- Cochez **"Afficher les prix des formules"** pour les rendre visibles

#### Onglet 3 : Composition de la Carte

Pour chaque **section** :
1. Cliquez sur "Ajouter Section"
2. Saisissez le **titre** (ex: "Mise en Bouche", "Entree Froide", "Poisson")
3. Definissez l'**ordre** (1, 2, 3...)
4. Ajoutez des **plats** :
   - **Nom** : Nom du plat (ex: "Foie Gras Poele")
   - **Description** : Description detaillee (optionnel)
   - **Supplement** : Prix additionnel (ex: 5€ pour la truffe)

### Exemple : Menu Degustation 7 Services

**Configuration :**
- Titre : "Menu Prestige d'Automne"
- Mettre en ligne : ✅

**Tarification :**
- Prix Menu Complet : 85€

**Composition :**
1. **Mise en Bouche** (ordre: 1)
   - Cromesquis de Truffe
2. **Entree Froide** (ordre: 2)
   - Carpaccio de St Jacques
3. **Entree Chaude** (ordre: 3)
   - Foie Gras Poele
4. **Poisson** (ordre: 4)
   - Turbot Sauvage
5. **Viande** (ordre: 5)
   - Biche Grand Veneur
6. **Fromage** (ordre: 6)
   - Chariot Affine
7. **Dessert** (ordre: 7)
   - La Sphere Chocolat

## Architecture Technique

### Stack Technique
- **Framework** : Symfony 7.3
- **Admin** : EasyAdmin 4.27
- **ORM** : Doctrine ORM 3.5
- **Templating** : Twig 3
- **Base de donnees** : SQLite (configurable pour MySQL/PostgreSQL)

### Structure des Entites

```
Ardoise (Menu principal)
├── titre: string
├── dateCreation: DateTime
├── isActive: boolean
├── prixComplet: decimal (nullable)
├── prixEntreePlat: decimal (nullable)
├── prixPlatDessert: decimal (nullable)
├── afficherPrixFormules: boolean
└── sections: Collection<Section>

Section (Partie du menu)
├── titre: string
├── ordre: integer
├── ardoise: Ardoise
└── plats: Collection<Plat>

Plat (Plat individuel)
├── nom: string
├── description: text (nullable)
├── prix: decimal (nullable - supplement)
└── section: Section
```

### Controllers Principaux

- **DashboardController** : Point d'entree de l'admin EasyAdmin
- **ArdoiseCrudController** : Gestion CRUD des ardoises avec interface a onglets
- **ArdoiseController** : Affichage public des ardoises

## Personnalisation

### Changer les Couleurs du Theme

Editez `templates/base.html.twig` et modifiez les gradients :

```css
background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
/* Changez les codes couleur selon vos preferences */
```

### Modifier l'Interface Admin

Le fichier `src/Controller/Admin/ArdoiseCrudController.php` contient toute la configuration des onglets. Vous pouvez :
- Ajouter des champs
- Modifier les labels
- Changer les icones Font Awesome
- Reorganiser les onglets

## Base de Donnees

### Changer de Base de Donnees

Pour utiliser **MySQL/MariaDB** :

```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/ardoise_magique?serverVersion=8.0.32&charset=utf8mb4"
```

Pour utiliser **PostgreSQL** :

```env
DATABASE_URL="postgresql://user:password@127.0.0.1:5432/ardoise_magique?serverVersion=16&charset=utf8"
```

Puis recréez la base de donnees :
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## Resolution de Problemes

### Erreur "could not find driver"
Installez l'extension PHP SQLite :
```bash
# Ubuntu/Debian
sudo apt-get install php-sqlite3

# macOS
brew install php@8.2
```

### Les collections ne s'affichent pas correctement
Verifiez que Symfony UX est bien configure :
```bash
composer require symfony/ux-collection
```

### Les styles ne s'appliquent pas
Effacez le cache Symfony :
```bash
php bin/console cache:clear
```

## Licence

Projet proprietaire - L'Ardoise Magique

## Developpement

Ce projet a ete developpe avec une approche UX/UI centree sur la simplicite d'utilisation pour les restaurateurs, permettant la gestion de menus complexes sans complexite technique.

### Points Techniques Importants

- **Cascades Doctrine** : Les sections et plats sont automatiquement supprimes avec l'ardoise
- **Formulaires imbriques** : SectionType contient PlatType via CollectionType
- **Rendu expanse** : Les collections sont affichees ouvertes par defaut pour une meilleure UX
- **Validation** : Les relations ManyToOne sont obligatoires pour maintenir l'integrite

---

**Bon appetit et bonne gestion !**
=======
# 🧞 L'Ardoise Magique

> Une application web **Micro-SaaS** simple et moderne pour restaurateurs : centralisez votre menu, générez PDF/images et partagez en un clic.

**Statut:** 🚀 En développement (v2.0)
**Stack:** Symfony 7 (Monolithe) + EasyAdmin 4 + Bootstrap 5
**Hébergement:** O2Switch
**Auteur:** Clsdjo30

---

## 📚 Documentation

### 🔗 Cahier des Charges Complet

**👉 [Lire le Cahier des Charges (cahier_des_charges_ardoise_magique.md)](./cahier_des_charges_ardoise_magique.md)**

Ce document détaille :
- ✅ Objectifs et contexte du projet
- ✅ Tous les cas d'usage (UC-A1 à UC-V4)
- ✅ Architecture technique et modèle de données
- ✅ Routes, sécurité et UI/UX
- ✅ Logique fonctionnelle clé (PDF, Images, Activation)
- ✅ Plan d'implémentation en 9 jours

**Pour Claude Code:** Le cahier des charges est la **source unique de vérité**. Consultez-le à chaque étape pour :
- Valider les cas d'usage implémentés
- Vérifier les spécifications de sécurité
- Assurer la cohérence des entités Doctrine
- Valider les routes et contrôleurs

---

## 🎯 Aperçu Rapide

### Problème
Les restaurateurs perdent du temps à gérer leur ardoise (Word, site web, caisse, réseaux sociaux).

### Solution
**Une seule saisie** → Génération automatique de :
- 📄 Page web publique (HTML)
- 📑 Document PDF A4 (impression)
- 🖼️ Image JPG (réseaux sociaux)

### Flux Utilisateur
```
Restaurateur
    ↓
[Saisit l'ardoise via formulaire]
    ↓
[Clique "Activer"]
    ↓
[Partage en un clic sur Facebook/Instagram]
    ↓
Client voit le menu en ligne
```

---

## 🏗️ Architecture

### Stack Technique

| Composant | Technologie | Rôle | Dev | Prod |
|-----------|-------------|------|-----|------|
| **Framework** | Symfony 7 | Monolithe backend | ✅ | ✅ |
| **Admin CRUD** | EasyAdmin 4 | Interface administration | ✅ | ✅ |
| **Frontend** | Bootstrap 5 + Twig | UI responsive | ✅ | ✅ |
| **Base de données - Dev** | SQLite 3 | Persistance locale | ✅ | ❌ |
| **Base de données - Prod** | MariaDB 10.3+ | Persistance production | ❌ | ✅ |
| **ORM** | Doctrine 2 | Mapping objet-relationnel | ✅ | ✅ |
| **Génération PDF** | mpdf/mpdf | Conversion HTML → PDF | ✅ | ✅ |
| **Génération Image** | spatie/pdf-to-image | Conversion PDF → JPG | ✅ | ✅ |
| **Authentification** | SecurityBundle | Gestion utilisateurs | ✅ | ✅ |

### Modèle de Données

```
User (Restaurateur)
├── id
├── email (unique)
├── password (hash)
├── nom_restaurant
├── slug (unique, généré automatiquement)
└── relations: 1:N → Ardoise

Ardoise
├── id
├── titre
├── is_active (une seule active par user)
├── date_creation, date_modification
└── relations: 1:N → Section

Section
├── id
├── titre (ex: "Entrées", "Plats")
├── ordre
└── relations: 1:N → Plat

Plat
├── id
├── nom
├── description (nullable)
├── prix (décimal)
└── ordre
```

**Voir le diagramme complet:** [Cahier des Charges → 4. Architecture des Données](./cahier_des_charges_ardoise_magique.md#4-architecture-des-données)

---

## 📦 Installation & Setup

### Prérequis - Développement (SQLite)

- **PHP 8.2+** avec extensions : `pdo_sqlite`, `gd` ou `imagick`, `zip`
- **Composer 2.x**
- **Node.js 18+** (optionnel, pour asset bundling)
- **Git**
- **SQLite 3** (généralement pré-installé)

**Avantage :** Aucune installation de serveur BD. Le fichier `var/data.db` est créé automatiquement.

### Prérequis - Production (O2Switch - MariaDB)

- **PHP 8.2+** avec extensions : `pdo_mysql` ou `pdo_mariadb`, `gd` ou `imagick`, `zip`
- **Composer 2.x**
- **MariaDB 10.3+** (fourni par O2Switch)
- **ImageMagick** activé
- **Git** pour les déploiements

### Installation Locale (Développement avec SQLite)

```bash
# 1. Cloner le projet
git clone https://github.com/clsdjo30/ardoise-magique.git
cd ardoise-magique

# 2. Installer les dépendances PHP
composer install

# 3. Copier le fichier d'environnement
cp .env.example .env

# 4. 📝 La DATABASE_URL est déjà configurée pour SQLite en dev
#    Vérifier/éditer .env si besoin :
#    DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
#    (Laisser par défaut pour SQLite en développement)

# 5. Vérifier que pdo_sqlite est disponible
php -m | grep pdo_sqlite

# 6. Créer la base de données SQLite et exécuter les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 7. Créer un utilisateur de test (optionnel)
php bin/console app:create-user "Le Petit Nimois" admin@example.com password123

# 8. Lancer le serveur local
symfony serve
# ou
php -S localhost:8000 -t public

# 9. Accéder à l'application
# Admin : http://localhost:8000/admin
# Public : http://localhost:8000/ardoise/le-petit-nimois

# ✅ La base de données SQLite est créée dans var/data.db
# ✅ Fichier automatiquement ignoré par .gitignore
```

### Configuration du Fichier `.env`

#### Développement (SQLite - Défaut)

```env
# ✅ DÉVELOPPEMENT - SQLite (Défaut)
# Aucune configuration nécessaire pour SQLite
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"

# Environnement
APP_ENV=dev
APP_DEBUG=true

# Secret (générer avec: php bin/console secrets:generate-keys)
APP_SECRET=your_secret_key_here

# Mailer (optionnel)
MAILER_DSN=smtp://user:pass@smtp.mailtrap.io:465
```

#### Production (MariaDB sur O2Switch)

```env
# 🚀 PRODUCTION - MariaDB O2Switch
# À configurer lors du déploiement
DATABASE_URL="mysql://user_o2switch:password@db.o2switch.fr:3306/database_name"

# Environnement
APP_ENV=prod
APP_DEBUG=false

# Secret (générer une nouvelle clé en production)
APP_SECRET=your_production_secret_key_here_very_long_and_secure

# Mailer
MAILER_DSN=smtp://user:pass@smtp.o2switch.fr:465

# Sentry (optionnel, pour monitoring)
SENTRY_DSN=https://xxxxx@xxxxx.ingest.sentry.io/xxxxx
```

---

## 🚀 Démarrage Rapide (Pour Développeurs)

### Développement avec SQLite

```bash
# 1. Copier .env et laisser DATABASE_URL pour SQLite
cp .env.example .env

# 2. Installer les dépendances
composer install

# 3. Créer la base de données SQLite
php bin/console doctrine:database:create

# 4. Migrer
php bin/console doctrine:migrations:migrate

# 5. Lancer le serveur
symfony serve

# 🎉 Accédez à http://localhost:8000/admin
```

### Production sur O2Switch (MariaDB)

```bash
# ⚠️ Voir la section "📡 Déploiement (O2Switch)"
# pour les instructions complètes
```

```bash
php bin/console make:entity User
php bin/console make:entity Ardoise
php bin/console make:entity Section
php bin/console make:entity Plat
```

### Générer la Migration

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### Accéder à EasyAdmin

- **URL:** `http://localhost:8000/admin`
- **Interface:** Gestion CRUD complète des ardoises
- **Authentification:** Requiert un compte `ROLE_USER`

### Tester les Routes Publiques

```bash
# Voir l'ardoise (web)
curl http://localhost:8000/ardoise/mon-restaurant

# Télécharger le PDF
curl http://localhost:8000/ardoise/mon-restaurant/pdf --output ardoise.pdf

# Télécharger l'image
curl http://localhost:8000/ardoise/mon-restaurant/image --output ardoise.jpg
```

---

## 📋 Structure du Projet

```
ardoise-magique/
├── src/
│   ├── Controller/
│   │   ├── PublicController.php          # Routes publiques (show, pdf, image)
│   │   ├── Admin/
│   │   │   ├── DashboardController.php   # Dashboard restaurateur
│   │   │   ├── ArdoiseController.php     # Actions custom (toggle-active)
│   │   │   └── CrudController/           # EasyAdmin CRUD
│   │   └── SecurityController.php        # Login/Logout
│   ├── Entity/
│   │   ├── User.php                      # Restaurateur
│   │   ├── Ardoise.php                   # Menu/Ardoise
│   │   ├── Section.php                   # Catégories (Entrées, Plats...)
│   │   └── Plat.php                      # Plats individuels
│   ├── Repository/
│   │   ├── UserRepository.php
│   │   ├── ArdoiseRepository.php
│   │   ├── SectionRepository.php
│   │   └── PlatRepository.php
│   ├── Service/                          # Logique métier
│   │   ├── ArdoiseManager.php            # Gestion ardoises (activation)
│   │   ├── PdfGenerator.php              # Génération PDF
│   │   └── ImageGenerator.php            # Génération images
│   ├── Security/
│   │   └── ArdoiseVoter.php              # Vérification propriété
│   └── ...
├── templates/
│   ├── base.html.twig                    # Layout global
│   ├── security/
│   │   ├── login.html.twig
│   │   └── register.html.twig
│   ├── admin/
│   │   ├── dashboard/
│   │   │   └── index.html.twig           # Dashboard restaurateur
│   │   └── _form.html.twig               # Formulaire ardoise (EasyAdmin)
│   └── public/
│       ├── show_web.html.twig            # Affichage web
│       ├── show_pdf.html.twig            # Template PDF
│       └── show_error.html.twig          # Ardoise non trouvée
├── public/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   └── app.css                       # Styles personnalisés
│   ├── js/
│   │   ├── bootstrap.bundle.min.js
│   │   └── app.js                        # Scripts custom
│   └── index.php                         # Point d'entrée
├── migrations/                           # Migrations Doctrine
├── tests/                                # Tests unitaires/intégration
├── config/
│   ├── packages/
│   │   ├── easyadmin.yaml               # Configuration EasyAdmin
│   │   ├── doctrine.yaml                # Configuration ORM
│   │   └── security.yaml                # Configuration Symfony Security
│   └── routes.yaml                      # Routes
├── .env.example                          # Variables d'environnement
├── .gitignore
├── composer.json
├── composer.lock
├── cahier_des_charges_ardoise_magique.md # Spécifications complètes
└── README.md                             # Ce fichier
```

---

## 🔄 Workflow Développement

### Étapes d'Implémentation (Plan 9 jours)

Voir le **Cahier des Charges → [9. Phase d'Implémentation](./cahier_des_charges_ardoise_magique.md#9-phase-dimplémentation)** pour les détails complets.

#### **Jour 1 : Setup & Auth**
- [ ] Créer le projet Symfony 7
- [ ] Configurer EasyAdmin
- [ ] Implémenter l'authentification (login/register)

#### **Jour 2-3 : CRUD Ardoises**
- [ ] Configurer EasyAdmin pour Ardoise/Section/Plat
- [ ] Dashboard restaurateur
- [ ] Gestion dynamique des sections/plats

#### **Jour 3 : Routes Publiques**
- [ ] PublicController::showWeb()
- [ ] PublicController::showPdf()
- [ ] PublicController::showImage()

#### **Jour 4 : Génération PDF & Image**
- [ ] Configurer mpdf
- [ ] Configurer spatie/pdf-to-image
- [ ] Tester conversion PDF → JPG

#### **Jour 4-5 : UI/UX & Partage**
- [ ] Styliser dashboard
- [ ] Styliser pages publiques
- [ ] Modal de partage (Facebook, Instagram)

#### **Jour 5-6 : Sécurité & Tests**
- [ ] Tests unitaires
- [ ] Tests d'intégration
- [ ] Vérification sécurité

#### **Jour 6-7 : Déploiement & Polish**
- [ ] Déployer sur O2Switch
- [ ] Optimisations
- [ ] Documentation

### Utiliser Claude Code pour L'Implémentation

**Claude Code est configuré pour lire ce README et le cahier des charges.**

Pour chaque tâche, donnez à Claude Code :

```
Implémente le Use Case UC-A3 (Créer une Ardoise)

Critères de succès:
- Formulaire avec champs: titre, sections dynamiques, plats
- Gestion des sections/plats (ajouter/supprimer)
- Validation côté serveur
- Redirection vers dashboard avec message flash

Consulte:
- Cahier des Charges → 2.2 Use Cases Admin
- Cahier des Charges → 3. Spécifications Techniques
- Cahier des Charges → 4.2 Entité Ardoise
- Cahier des Charges → 7.4 Dashboard Admin (template exemple)
```

Claude Code cherchera les informations dans :
1. Ce README (vous êtes ici)
2. Le cahier des charges (fichier lié)
3. Le code existant du projet

---

## 🔐 Sécurité

### Authentification & Autorisation

- ✅ **Login/Logout** via SecurityBundle
- ✅ **Mots de passe hashés** (bcrypt/argon2id)
- ✅ **Contrôle d'accès** : Routes `/admin/*` protégées
- ✅ **Voter Symfony** : Un user ne peut accéder qu'à ses ardoises
- ✅ **CSRF Protection** : Tokens sur tous les formulaires
- ✅ **XSS Protection** : Échappement Twig par défaut

**Voir:** [Cahier des Charges → 6. Spécifications de Sécurité](./cahier_des_charges_ardoise_magique.md#6-spécifications-de-sécurité)

### Avant Production - Checklist (SQLite → MariaDB)

```bash
# 1. ✅ Préparer la migration SQLite → MariaDB
#    Exporter les données SQLite (optionnel, dev seulement)
php bin/console doctrine:query:sql "SELECT * FROM user;" | sqlite3 var/data.db

# 2. ✅ Configurer DATABASE_URL pour MariaDB O2Switch
#    Éditer .env ou .env.prod avec les credentials O2Switch
#    DATABASE_URL="mysql://user:pass@db.o2switch.fr:3306/db_name"

# 3. ✅ Générer une clé secrète (nouvelle pour production)
php bin/console secrets:generate-keys

# 4. ✅ Vérifier que pdo_mysql est activé sur O2Switch
#    ssh user@o2switch.fr
#    php -m | grep pdo_mysql

# 5. ✅ Vérifier ImageMagick sur O2Switch
#    ssh user@o2switch.fr
#    php -m | grep imagick

# 6. ✅ Activer HTTPS
#    Configurer le serveur/Load Balancer pour HTTPS
#    O2Switch fournit Let's Encrypt gratuit

# 7. ✅ Vérifier les headers de sécurité
#    Ajouter dans config/packages/framework.yaml:
#    headers:
#        X-Content-Type-Options: nosniff
#        X-Frame-Options: DENY
#        X-XSS-Protection: 1; mode=block

# 8. ✅ Tester la génération PDF/Image en production
#    Voir: tests/Feature/PublicControllerTest.php

# 9. ✅ Compiler les assets (production)
#    php bin/console assets:install --env=prod
#    npm run build (si Node.js disponible)

# 10. ✅ Exécuter les tests
#     composer test

# 11. ✅ Exécuter les migrations en production
#     php bin/console doctrine:migrations:migrate --env=prod --no-interaction
```

---

## 🧪 Tests

### Exécuter les Tests

```bash
# Tous les tests
composer test

# Tests unitaires seulement
composer test:unit

# Tests d'intégration seulement
composer test:integration

# Couverture de code
composer test:coverage
```

### Exemples de Tests

```php
// tests/Feature/PublicControllerTest.php
public function testShowWebActiveArdoise()
{
    // Arrange
    $user = $this->createUser('Le Petit Nimois');
    $ardoise = $this->createArdoise($user, 'Menu du Jour', true);

    // Act
    $response = $this->client->request('GET', '/ardoise/le-petit-nimois');

    // Assert
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertStringContainsString('Menu du Jour', $response->getContent());
}

public function testToggleActiveArdoise()
{
    // Arrange
    $user = $this->createUser('Le Petit Nimois');
    $ardoise1 = $this->createArdoise($user, 'Menu 1', true);
    $ardoise2 = $this->createArdoise($user, 'Menu 2', false);

    // Act
    $this->client->loginUser($user);
    $this->client->request('POST', '/admin/ardoise/' . $ardoise2->getId() . '/toggle-active');

    // Assert
    $this->assertFalse($ardoise1->isActive());
    $this->assertTrue($ardoise2->isActive());
}
```

---

## 📡 Déploiement (O2Switch)

### Prérequis sur O2Switch (Production - MariaDB)

- ✅ PHP 8.2+ avec `pdo_mysql` ou `pdo_mariadb` (obligatoire)
- ✅ `imagick` ou `gd` pour la génération d'images
- ✅ `zip` pour les dépendances
- ✅ **MariaDB 10.3+** (fourni par O2Switch)
- ✅ Composer (pour installer les dépendances)
- ✅ HTTPS (certificat Let's Encrypt gratuit)
- ✅ `pdo_sqlite` n'est **pas nécessaire** en production

### Processus de Déploiement (O2Switch - MariaDB)

```bash
# 1. SSH sur le serveur O2Switch
ssh utilisateur@ardoise-magique.com

# 2. Cloner le repository
git clone https://github.com/clsdjo30/ardoise-magique.git
cd ardoise-magique

# 3. Installer les dépendances
composer install --no-dev --optimize-autoloader

# 4. Configurer l'environnement PRODUCTION
cp .env.example .env

# 5. Éditer .env.production avec les credentials O2Switch
#    ⚠️ Important : Configurer DATABASE_URL pour MariaDB
nano .env
#    Exemple :
#    DATABASE_URL="mysql://user_o2switch:password@db.o2switch.fr:3306/db_name"
#    APP_ENV=prod
#    APP_DEBUG=false

# 6. Générer la clé secrète (nouvelle clé pour production)
php bin/console secrets:generate-keys

# 7. ✅ Créer et migrer la base de données MariaDB
php bin/console doctrine:database:create --env=prod
php bin/console doctrine:migrations:migrate --env=prod --no-interaction

# 8. Compiler les assets
php bin/console assets:install --env=prod
# ou npm run build (si Node.js disponible)

# 9. Configurer le web server (Apache ou Nginx)
# Pointe le DocumentRoot vers /public
# Assurer HTTPS activé

# 10. Vérifier les permissions
chmod -R 775 var/cache var/log var/data public/uploads

# 11. Vérifier l'installation
# Accéder à https://ardoise-magique.com/admin
# Vérifier les logs
tail -f var/log/prod.log
```

**⚠️ Différences avec Développement (SQLite) :**

| Aspect | Développement (SQLite) | Production (O2Switch - MariaDB) |
|--------|------------------------|--------------------------------|
| **DATABASE_URL** | `sqlite:///%kernel.project_dir%/var/data.db` | `mysql://user:pass@db.o2switch.fr:3306/db_name` |
| **Extension PHP** | `pdo_sqlite` | `pdo_mysql` ou `pdo_mariadb` |
| **APP_ENV** | `dev` | `prod` |
| **APP_DEBUG** | `true` | `false` |
| **Base créée** | Automatique en local | Sur serveur O2Switch |

### Variable d'Environnement DATABASE_URL

**Important :** O2Switch fournit généralement les credentials via un panel d'administration.

```bash
# Pour O2Switch - Récupérer les infos depuis le panel et configurer :
# DATABASE_URL="mysql://username:password@hostname:port/database_name"

# Exemple typique O2Switch :
# DATABASE_URL="mysql://user_12345:secretpass@ftp.o2switch.net:3306/database_12345"

# Tester la connexion :
php bin/console doctrine:database:create --env=prod --connection=default
```

### Configuration Apache (.htaccess)

```apache
# public/.htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

### Monitoring

```bash
# Vérifier les erreurs
tail -f var/log/prod.log

# Vérifier la santé de l'app
curl https://ardoise-magique.com/_health

# Statut des migrations
php bin/console doctrine:migrations:list
```

---

## 📞 Points de Contact & Support

### Problèmes Courants

#### Base de Données SQLite en Développement

```bash
# ✅ Vérifier que pdo_sqlite est disponible
php -m | grep pdo_sqlite

# ✅ Créer/réinitialiser la base de données SQLite
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# ✅ Supprimer le fichier var/data.db et recommencer
rm var/data.db
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

#### Erreur MariaDB sur O2Switch

```bash
# ❌ Erreur : "Connection refused"
# ✅ Solution : Vérifier les credentials DATABASE_URL
# Database Host : généralement "ftp.o2switch.net" ou "localhost" (selon config)
# Port : 3306 (port MySQL par défaut)
# Username/Password : dans le panel O2Switch

# ❌ Erreur : "SQLSTATE[HY000]: General error: 1030"
# ✅ Solution : Vérifier les permissions sur la base de données O2Switch

# ✅ Tester la connexion MariaDB
php bin/console doctrine:database:create --env=prod --if-not-exists
php bin/console doctrine:migrations:migrate --env=prod --no-interaction
```

#### Migration SQLite → MariaDB en Production

```bash
# ⚠️ Important : Ne jamais migrer les données de SQLite en production
# SQLite n'est utilisé que pour le développement local

# En production : Créer une nouvelle base MariaDB vierge
php bin/console doctrine:database:create --env=prod --if-not-exists
php bin/console doctrine:migrations:migrate --env=prod --no-interaction

# Tous les données de développement (SQLite) restent sur la machine locale
# Production repart avec une base MariaDB vierge
```

#### ImageMagick non disponible
```bash
# Vérifier l'installation
php -m | grep imagick

# Alternative : utiliser GD
# Modifier dans PublicController:
use Intervention\Image\ImageManager;
// Configurer le driver à 'gd' au lieu de 'imagick'
```

#### Erreur "Ardoise non trouvée"
- Vérifier que le slug existe
- Vérifier qu'une ardoise est `is_active = true`
- Tester avec : `SELECT * FROM ardoise WHERE restaurateur_id = X AND is_active = 1;`

#### PDF génération trop lente
- Activer la mise en cache des images
- Voir : Cahier des Charges → [10.3 Génération Image Asynchrone](./cahier_des_charges_ardoise_magique.md#103-génération-image-asynchrone-future-enhancement)
- Implémenter une job queue avec Messenger

#### Erreur CSRF sur formulaires
- Vérifier que le token CSRF est présent : `{{ csrf_token('form_name') }}`
- Vérifier que la clé secrète est correctement générée

### Ressources

- 📖 [Documentation Symfony 7](https://symfony.com/doc/current/index.html)
- 📖 [Documentation EasyAdmin](https://symfony.com/doc/current/bundles/EasyAdminBundle/index.html)
- 📖 [Documentation Doctrine](https://www.doctrine-project.org/projects/doctrine-orm/en/2.x/index.html)
- 📖 [Documentation mPDF](https://mpdf.github.io/)
- 📖 [Documentation spatie/pdf-to-image](https://github.com/spatie/pdf-to-image)

### Signaler un Bug

Créer une issue GitHub avec :
- Description du problème
- Steps to reproduce
- Output des logs (`var/log/dev.log`)
- Environnement (PHP version, OS, etc.)

---

## 📝 Licence

MIT License - Voir le fichier `LICENSE` pour les détails.

---

## 👨‍💻 Auteur

**Clsdjo30**
Développeur Symfony | Spécialiste Micro-SaaS
[GitHub](https://github.com/clsdjo30) | [Portfolio](https://clsdjo30.dev)

---

## 🗺️ Roadmap Futur

- [ ] **v2.1** : Drag-drop pour tri des sections/plats
- [ ] **v2.2** : Multi-user par restaurant (gérants, cuisiniers)
- [ ] **v2.3** : Génération asynchrone d'images (job queue)
- [ ] **v2.4** : Templates personnalisables (thèmes)
- [ ] **v2.5** : API REST pour intégrations tiers
- [ ] **v3.0** : Mobile app (React Native)

---

## 📊 Statistiques du Projet

- **Lignes de code :** ~2500 (visé)
- **Durée estimation :** 9 jours développement
- **Couverture tests :** 80%+ (visé)
- **Performance :** <200ms page load (visé)
- **Accessibilité :** WCAG 2.1 AA (visé)

---

## 🙏 Remerciements

Merci à :
- Symfony Team pour le framework robuste
- EasyAdmin Team pour l'admin bundle puissant
- mPDF & Spatie pour les outils de génération
- Bootstrap Team pour le framework CSS

---

**Dernière mise à jour :** 18 novembre 2025
**Version README :** 2.0

---

### 🔗 Liens Importants

- **Cahier des Charges :** [cahier_des_charges_ardoise_magique.md](./cahier_des_charges_ardoise_magique.md)
- **Issues & Features :** [GitHub Issues](https://github.com/clsdjo30/ardoise-magique/issues)
- **Discussions :** [GitHub Discussions](https://github.com/clsdjo30/ardoise-magique/discussions)
- **Wiki :** [GitHub Wiki](https://github.com/clsdjo30/ardoise-magique/wiki)

---

**Bon développement ! 🚀**
>>>>>>> 914bd82 ( Add doc)
