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
