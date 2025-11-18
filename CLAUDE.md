# CLAUDE.md - AI Assistant Guide for L'Ardoise Magique

**Version:** 1.0
**Date:** 18 November 2025
**Project:** L'Ardoise Magique (Micro-SaaS for Restaurant Digital Menus)
**Framework:** Symfony 7.3

---

## 📋 Table of Contents

1. [Project Overview](#project-overview)
2. [Current State](#current-state)
3. [Architecture & Technology Stack](#architecture--technology-stack)
4. [Database Schema](#database-schema)
5. [Routing Structure](#routing-structure)
6. [Development Workflow](#development-workflow)
7. [Key Implementation Details](#key-implementation-details)
8. [Security Requirements](#security-requirements)
9. [Testing Guidelines](#testing-guidelines)
10. [Deployment Instructions](#deployment-instructions)
11. [Code Conventions](#code-conventions)
12. [Common Commands](#common-commands)
13. [Troubleshooting](#troubleshooting)

---

## 🎯 Project Overview

### What is L'Ardoise Magique?

L'Ardoise Magique ("The Magic Slate") is a micro-SaaS application designed for restaurant owners to:
- Create and manage digital menu boards/slates
- Generate PDF versions for printing
- Generate JPG images for social media (Instagram)
- Display on web pages (embeddable via iFrame)
- Share directly to Facebook

### Business Context

Restaurateurs waste time manually managing their "Plat du Jour" (daily specials) across multiple platforms (Word, website, POS system). This application centralizes the workflow: **write once, publish everywhere**.

### Key Features

- **User Management**: Restaurant owners can register, login, and manage their account
- **Slate Management**: Create, edit, delete, and activate slates
- **Dynamic Content**: Slates contain sections (Entrées, Plats, Desserts) with dishes (name, description, price)
- **Multi-Format Output**: Web view, PDF generation, Image (JPG) generation
- **Social Sharing**: Direct Facebook share, Instagram image download
- **Public Display**: Embeddable public URL with unique slug per restaurant

---

## 🏗️ Current State

### Implementation Status

**Status:** ⚠️ **INITIAL STATE - NOT YET IMPLEMENTED**

The project has been initialized with:
- ✅ Symfony 7.3 fresh installation
- ✅ Complete documentation (INDEX.md, cahier-des-charges.md)
- ✅ Composer dependencies installed
- ✅ Docker configuration (compose.yaml)
- ❌ No entities created yet
- ❌ No controllers implemented
- ❌ No authentication system configured
- ❌ No database migrations
- ❌ No templates created

### Directory Structure

```
ardoise-magique/
├── assets/                 # Frontend assets (CSS, JS via AssetMapper)
├── bin/                    # Symfony console
├── config/                 # Configuration files
│   ├── packages/          # Bundle configurations
│   └── routes/            # Route definitions
├── migrations/            # Database migrations (empty)
├── public/                # Web root
│   └── index.php         # Front controller
├── src/
│   ├── Controller/       # Controllers (empty - to be created)
│   ├── Entity/           # Doctrine entities (empty - to be created)
│   ├── Repository/       # Repositories (empty - to be created)
│   └── Kernel.php        # Application kernel
├── templates/             # Twig templates (only base.html.twig exists)
├── tests/                 # PHPUnit tests
├── translations/          # i18n files
├── .env                   # Environment configuration
├── composer.json          # PHP dependencies
└── symfony.lock          # Symfony flex lock file
```

---

## 🛠️ Architecture & Technology Stack

### Framework & Core

- **Symfony 7.3** (Monolith architecture)
- **PHP 8.2+**
- **Doctrine ORM 3.5** with attribute-based mapping
- **Twig 3.x** templating engine

### Frontend

- **Bootstrap 5** for admin UI (modals, forms, buttons)
- **Symfony AssetMapper** (no Node.js build required)
- **Symfony Stimulus Bundle** for interactive forms (dynamic collection handling)
- **Symfony UX Turbo** for enhanced navigation
- **Custom CSS** for public "chalk board" style

### Database

- **Development**: SQLite (`var/data.db`) - Zero configuration
- **Production**: MariaDB (O2Switch hosting) - High performance
- **Note**: Database is configured via `DATABASE_URL` in `.env`

### PDF & Image Generation

- **mpdf/mpdf**: PDF generation (pure PHP, no binary dependencies)
- **spatie/pdf-to-image**: PDF to JPG conversion (requires `imagick` PHP extension)

### Security

- **Symfony SecurityBundle**: Authentication & authorization
- **Password hashing**: Auto (bcrypt/argon2)
- **CSRF protection**: Enabled by default on all forms
- **XSS protection**: Twig auto-escaping

---

## 💾 Database Schema

### Entity Relationship Diagram

```
User (Restaurateur)
├── id: int (PK)
├── email: string (unique)
├── roles: json
├── password: string (hashed)
├── nom_restaurant: string
├── slug: string (unique) - Generated from nom_restaurant
└── ardoises: OneToMany → Ardoise

Ardoise (Slate)
├── id: int (PK)
├── titre: string
├── date_creation: datetime
├── is_active: boolean (default: false)
├── restaurateur: ManyToOne → User
└── sections: OneToMany → Section (cascade persist, remove)

Section (Menu Section: Entrées, Plats, etc.)
├── id: int (PK)
├── titre: string
├── ordre: int (for sorting)
├── ardoise: ManyToOne → Ardoise
└── plats: OneToMany → Plat (cascade persist, remove)

Plat (Dish)
├── id: int (PK)
├── nom: string
├── description: string (nullable)
├── prix: decimal (or int for cents)
├── ordre: int (for sorting)
└── section: ManyToOne → Section
```

### Important Business Rules

1. **One Active Slate per User**: Only one `Ardoise` can have `is_active = true` per `User`
2. **Slug Generation**: User's `slug` is auto-generated from `nom_restaurant` using Gedmo Sluggable
3. **Cascade Operations**: Deleting an `Ardoise` deletes all related `Section` and `Plat` entities
4. **Ordering**: Both `Section` and `Plat` have an `ordre` field for custom sorting

---

## 🛣️ Routing Structure

### Public Routes (No Authentication)

| Method | Route | Name | Controller | Description |
|--------|-------|------|------------|-------------|
| GET | `/ardoise/{slug}` | `app_public_web` | `PublicController::showWeb` | Display active slate (HTML) |
| GET | `/ardoise/{slug}/pdf` | `app_public_pdf` | `PublicController::showPdf` | Generate and display PDF |
| GET | `/ardoise/{slug}/image` | `app_public_image` | `PublicController::showImage` | Generate and download JPG |

### Authentication Routes

| Method | Route | Name | Controller | Description |
|--------|-------|------|------------|-------------|
| GET/POST | `/login` | `app_login` | `SecurityController::login` | Login page |
| GET | `/logout` | `app_logout` | `SecurityController::logout` | Logout |
| GET/POST | `/register` | `app_register` | `RegistrationController::register` | User registration |

### Admin Routes (Requires ROLE_USER)

| Method | Route | Name | Controller | Description |
|--------|-------|------|------------|-------------|
| GET | `/admin` | `app_admin_dashboard` | `Admin\DashboardController::index` | Dashboard with slate list |
| GET/POST | `/admin/ardoise/new` | `app_admin_ardoise_new` | `Admin\ArdoiseController::new` | Create new slate |
| GET/POST | `/admin/ardoise/{id}/edit` | `app_admin_ardoise_edit` | `Admin\ArdoiseController::edit` | Edit slate |
| POST | `/admin/ardoise/{id}/toggle-active` | `app_admin_ardoise_toggle_active` | `Admin\ArdoiseController::toggleActive` | Activate/deactivate slate |
| DELETE | `/admin/ardoise/{id}` | `app_admin_ardoise_delete` | `Admin\ArdoiseController::delete` | Delete slate |

---

## 🔄 Development Workflow

### 1. Initial Setup (First Time)

```bash
# Clone repository (if not already done)
git clone <repository-url>
cd ardoise-magique

# Install PHP dependencies
composer install

# Configure environment
cp .env .env.local
# Edit .env.local and set DATABASE_URL for SQLite:
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"

# Create database
php bin/console doctrine:database:create

# Run migrations (when they exist)
php bin/console doctrine:migrations:migrate

# Start development server
symfony serve
# Or: php -S localhost:8000 -t public/
```

### 2. Creating Entities

When implementing the schema:

```bash
# Generate entities interactively
php bin/console make:entity User
php bin/console make:entity Ardoise
php bin/console make:entity Section
php bin/console make:entity Plat

# Generate migration
php bin/console make:migration

# Execute migration
php bin/console doctrine:migrations:migrate
```

### 3. Creating Controllers

```bash
# Generate authentication system
php bin/console make:auth

# Generate registration form
php bin/console make:registration-form

# Generate CRUD controllers
php bin/console make:crud Ardoise

# Or create controllers manually in src/Controller/
```

### 4. Development Cycle

1. **Create/modify entities** → Generate migration → Run migration
2. **Create controllers** → Implement logic → Create templates
3. **Test locally** with SQLite database
4. **Commit changes** regularly
5. **Deploy to production** (see Deployment section)

---

## 🔑 Key Implementation Details

### 1. Slug Generation for Users

Use `stof/doctrine-extensions-bundle` (Gedmo) for automatic slug generation:

```bash
composer require stofDoctrineExtensions/doctrine-extensions-bundle
```

In `User` entity:
```php
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
class User
{
    #[Gedmo\Slug(fields: ['nom_restaurant'])]
    #[ORM\Column(length: 255, unique: true)]
    private string $slug;
}
```

### 2. Active Slate Toggle Logic

In `Admin\ArdoiseController::toggleActive`:

```php
public function toggleActive(Ardoise $ardoise): Response
{
    // Security check: ensure user owns this ardoise
    $this->denyAccessUnlessGranted('EDIT', $ardoise);

    $em = $this->entityManager;

    // Deactivate all slates for this user
    $user = $this->getUser();
    foreach ($user->getArdoises() as $a) {
        $a->setIsActive(false);
    }

    // Activate the selected slate
    $ardoise->setIsActive(true);

    $em->flush();

    $this->addFlash('success', 'Ardoise activée avec succès');
    return $this->redirectToRoute('app_admin_dashboard');
}
```

### 3. PDF Generation

In `PublicController::showPdf`:

```php
use Mpdf\Mpdf;

public function showPdf(string $slug, UserRepository $userRepo): Response
{
    $user = $userRepo->findOneBy(['slug' => $slug]);
    if (!$user) {
        throw $this->createNotFoundException('Restaurant non trouvé');
    }

    $ardoise = $user->getArdoises()->filter(fn($a) => $a->isActive())->first();
    if (!$ardoise) {
        throw $this->createNotFoundException('Aucune ardoise active');
    }

    $html = $this->renderView('public/show_pdf.html.twig', [
        'ardoise' => $ardoise,
        'user' => $user,
    ]);

    $mpdf = new Mpdf([
        'format' => 'A4',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
    ]);

    $mpdf->WriteHTML($html);

    return new Response(
        $mpdf->Output('', 'S'),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="ardoise.pdf"'
        ]
    );
}
```

### 4. Image Generation

In `PublicController::showImage`:

```php
use Spatie\PdfToImage\Pdf;

public function showImage(string $slug, UserRepository $userRepo): Response
{
    // ... (find user and ardoise - same as PDF)

    // Generate PDF to temp file
    $tempPdfPath = sys_get_temp_dir() . '/' . uniqid('ardoise_') . '.pdf';
    $html = $this->renderView('public/show_pdf.html.twig', [
        'ardoise' => $ardoise,
        'user' => $user,
    ]);

    $mpdf = new Mpdf(['format' => 'A4']);
    $mpdf->WriteHTML($html);
    $mpdf->Output($tempPdfPath, 'F');

    // Convert to image
    $tempImagePath = sys_get_temp_dir() . '/' . uniqid('ardoise_') . '.jpg';
    $pdf = new Pdf($tempPdfPath);
    $pdf->setResolution(150) // DPI for Instagram
        ->saveImage($tempImagePath);

    unlink($tempPdfPath); // Clean up PDF

    return $this->file($tempImagePath, 'ardoise-' . date('Y-m-d') . '.jpg', ResponseHeaderBag::DISPOSITION_ATTACHMENT)
        ->deleteFileAfterSend(true);
}
```

### 5. Dynamic Form Collections (Stimulus)

For managing Sections and Plats in the Ardoise form, use Symfony UX Collection:

```bash
composer require symfony/ux-collection
```

In your `ArdoiseType` form:
```php
use Symfony\UX\LiveComponent\Form\Type\CollectionType;

$builder
    ->add('sections', CollectionType::class, [
        'entry_type' => SectionType::class,
        'allow_add' => true,
        'allow_delete' => true,
        'by_reference' => false,
        'prototype' => true,
    ]);
```

### 6. Security Voter for Ownership

Create `src/Security/Voter/ArdoiseVoter.php`:

```php
namespace App\Security\Voter;

use App\Entity\Ardoise;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArdoiseVoter extends Voter
{
    const EDIT = 'EDIT';
    const DELETE = 'DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof Ardoise;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Ardoise $ardoise */
        $ardoise = $subject;

        return $ardoise->getRestaurateur() === $user;
    }
}
```

---

## 🔒 Security Requirements

### Must-Have Security Features

1. **Authentication**: All `/admin/*` routes require `ROLE_USER`
2. **Ownership Verification**: Users can only edit/delete their own slates
3. **CSRF Protection**: All forms must have CSRF tokens (enabled by default)
4. **XSS Prevention**: All user input must be escaped in Twig (default behavior)
5. **Password Hashing**: Use Symfony's auto hasher (bcrypt/argon2)
6. **SQL Injection Protection**: Use Doctrine ORM (parameterized queries)

### Security Configuration

In `config/packages/security.yaml`:

```yaml
security:
    password_hashers:
        App\Entity\User: 'auto'

    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            lazy: true
            provider: app_user_provider
            form_login:
                login_path: app_login
                check_path: app_login
                default_target_path: app_admin_dashboard
            logout:
                path: app_logout
                target: app_login

    access_control:
        - { path: ^/admin, roles: ROLE_USER }
        - { path: ^/login, roles: PUBLIC_ACCESS }
        - { path: ^/register, roles: PUBLIC_ACCESS }
```

---

## 🧪 Testing Guidelines

### Test Structure

```
tests/
├── Unit/              # Unit tests (entities, services)
├── Functional/        # Functional tests (controllers)
└── bootstrap.php
```

### Running Tests

```bash
# Run all tests
php bin/phpunit

# Run specific test
php bin/phpunit tests/Functional/PublicControllerTest.php

# Run with coverage (requires xdebug)
XDEBUG_MODE=coverage php bin/phpunit --coverage-html var/coverage
```

### Test Example: Active Slate Logic

```php
namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ArdoiseToggleTest extends WebTestCase
{
    public function testOnlyOneSlateCanBeActive(): void
    {
        $client = static::createClient();

        // Login as user
        $user = $this->createUser();
        $client->loginUser($user);

        // Create two slates
        $ardoise1 = $this->createArdoise($user, 'Slate 1');
        $ardoise2 = $this->createArdoise($user, 'Slate 2');

        // Activate first slate
        $client->request('POST', '/admin/ardoise/' . $ardoise1->getId() . '/toggle-active');
        $this->assertTrue($ardoise1->isActive());
        $this->assertFalse($ardoise2->isActive());

        // Activate second slate
        $client->request('POST', '/admin/ardoise/' . $ardoise2->getId() . '/toggle-active');
        $this->assertFalse($ardoise1->isActive());
        $this->assertTrue($ardoise2->isActive());
    }
}
```

### Testing Checklist

- [ ] User registration and login
- [ ] Slate CRUD operations
- [ ] Active slate toggle (only one active)
- [ ] Ownership verification (users can't edit others' slates)
- [ ] PDF generation
- [ ] Image generation
- [ ] Public pages with invalid slugs
- [ ] Form validation

---

## 🚀 Deployment Instructions

### Development vs Production

| Aspect | Development | Production |
|--------|-------------|------------|
| Database | SQLite (`var/data.db`) | MariaDB (O2Switch) |
| Environment | `APP_ENV=dev` | `APP_ENV=prod` |
| Debug | Enabled | Disabled |
| Cache | Auto-refresh | Optimized |
| Error Display | Detailed | Generic |

### Production Deployment (O2Switch)

#### 1. Prepare Production Environment

```bash
# SSH to O2Switch server
ssh user@your-server.o2switch.net

# Navigate to web directory
cd ~/www/ardoise-magique

# Clone or upload project files
git clone <repository-url> .
```

#### 2. Configure Production Environment

Create `.env.local` on production server:

```bash
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=<generate-random-32-char-string>

# MariaDB configuration (get from O2Switch control panel)
DATABASE_URL="mysql://db_user:db_password@localhost:3306/db_name?serverVersion=10.11.2-MariaDB&charset=utf8mb4"

# Optional: Mailer configuration
MAILER_DSN=smtp://user:pass@smtp.o2switch.net:587
```

#### 3. Install and Build

```bash
# Install dependencies (production only, no dev)
composer install --no-dev --optimize-autoloader

# Clear cache
APP_ENV=prod php bin/console cache:clear

# Create database (first time only)
php bin/console doctrine:database:create --env=prod

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# Warm up cache
php bin/console cache:warmup --env=prod
```

#### 4. Set Permissions

```bash
# Ensure var/ is writable
chmod -R 777 var/

# Secure .env files
chmod 600 .env .env.local
```

#### 5. Configure Web Server

For O2Switch (Apache with mod_rewrite):

Ensure `.htaccess` exists in `public/`:

```apache
# public/.htaccess
DirectoryIndex index.php

<IfModule mod_negotiation.c>
    Options -MultiViews
</IfModule>

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI}::$0 ^(/.+)/(.*)::\2$
    RewriteRule .* - [E=BASE:%1]

    RewriteCond %{HTTP:Authorization} .+
    RewriteRule ^ - [E=HTTP_AUTHORIZATION:%0]

    RewriteCond %{ENV:REDIRECT_STATUS} =""
    RewriteRule ^index\.php(?:/(.*)|$) %{ENV:BASE}/$1 [R=301,L]

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ %{ENV:BASE}/index.php [L]
</IfModule>
```

Point domain document root to `public/` directory.

#### 6. Verify Installation

Visit: `https://your-domain.com/login`

---

## 📝 Code Conventions

### PHP Code Style

- **PSR-12** coding standard
- **Type hints**: Always use strict types (`declare(strict_types=1);`)
- **Return types**: Always declare return types
- **Naming**:
  - Classes: PascalCase
  - Methods: camelCase
  - Constants: UPPER_SNAKE_CASE
  - Variables: camelCase (French names acceptable: `$nom_restaurant`)

### Twig Templates

- **File naming**: `snake_case.html.twig`
- **Indentation**: 4 spaces
- **Blocks**: Always extend from `base.html.twig`
- **French labels**: Acceptable for UI (e.g., "Nom du Restaurant")

### Git Commit Messages

Follow conventional commits:

```
feat: Add user registration functionality
fix: Correct active slate toggle bug
docs: Update CLAUDE.md with testing section
refactor: Simplify PDF generation logic
test: Add tests for ArdoiseController
```

### Directory Organization

```
src/
├── Controller/
│   ├── Admin/           # Admin controllers (DashboardController, ArdoiseController)
│   ├── PublicController.php
│   ├── SecurityController.php
│   └── RegistrationController.php
├── Entity/              # Doctrine entities
├── Form/                # Form types (ArdoiseType, SectionType, PlatType)
├── Repository/          # Doctrine repositories
├── Security/
│   └── Voter/          # Security voters
└── Service/            # Business logic services (optional)
```

---

## ⌨️ Common Commands

### Database

```bash
# Create database
php bin/console doctrine:database:create

# Drop database
php bin/console doctrine:database:drop --force

# Create migration
php bin/console make:migration

# Run migrations
php bin/console doctrine:migrations:migrate

# Check schema
php bin/console doctrine:schema:validate

# Load fixtures (if installed)
php bin/console doctrine:fixtures:load
```

### Code Generation

```bash
# Create entity
php bin/console make:entity EntityName

# Create controller
php bin/console make:controller ControllerName

# Create CRUD
php bin/console make:crud EntityName

# Create form
php bin/console make:form

# Create voter
php bin/console make:voter

# Create authentication
php bin/console make:auth

# Create registration
php bin/console make:registration-form
```

### Cache & Debug

```bash
# Clear cache
php bin/console cache:clear

# List routes
php bin/console debug:router

# Test route
php bin/console router:match /admin/ardoise/new

# List services
php bin/console debug:container

# Check environment variables
php bin/console debug:dotenv
```

### Development Server

```bash
# Symfony CLI (recommended)
symfony serve

# Built-in PHP server
php -S localhost:8000 -t public/

# Access at: http://localhost:8000
```

---

## 🐛 Troubleshooting

### Common Issues

#### 1. Database Connection Error

**Symptom**: "SQLSTATE[HY000] [2002] No such file or directory"

**Solution**: Check `DATABASE_URL` in `.env`:

```bash
# For SQLite (development)
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"

# For MariaDB (production)
DATABASE_URL="mysql://user:pass@localhost:3306/dbname?serverVersion=10.11.2-MariaDB"
```

#### 2. Permission Denied on var/

**Symptom**: "Unable to write to var/cache"

**Solution**:
```bash
chmod -R 777 var/
```

#### 3. Imagick Extension Not Found

**Symptom**: "Class 'Imagick' not found" when generating images

**Solution**: Install PHP imagick extension:

```bash
# On O2Switch, contact support to enable imagick
# Locally (Ubuntu/Debian):
sudo apt-get install php8.2-imagick
sudo service apache2 restart
```

Verify:
```bash
php -m | grep imagick
```

#### 4. PDF Generation Shows Blank Page

**Symptom**: PDF downloads but is empty

**Solution**:
- Check Twig template renders correctly
- Verify `mpdf` is installed: `composer show mpdf/mpdf`
- Check for JavaScript/CSS in PDF template (mpdf doesn't support all CSS)

#### 5. Active Slate Not Displaying

**Symptom**: Public page shows "No active slate"

**Solution**:
```php
// Check in database
SELECT id, titre, is_active FROM ardoise WHERE is_active = 1;

// Ensure only one is active per user
// Run toggle action again from dashboard
```

#### 6. Form Collection Not Adding Items

**Symptom**: "Add Section" button does nothing

**Solution**:
- Ensure Stimulus is loaded
- Check browser console for JS errors
- Verify `symfony/ux-collection` is installed
- Check `data-controller="collection"` attribute in template

---

## 📚 Additional Resources

### Documentation Links

- [Symfony Documentation](https://symfony.com/doc/current/index.html)
- [Doctrine ORM](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/index.html)
- [Twig Documentation](https://twig.symfony.com/doc/3.x/)
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.0/)
- [mPDF Documentation](https://mpdf.github.io/)
- [Spatie PDF to Image](https://github.com/spatie/pdf-to-image)

### Project-Specific Documentation

- `INDEX.md`: Complete documentation index and navigation guide
- `cahier-des-charges.md`: Full specifications (in French)
- `GUIDE_SQLite_MariaDb.md`: Database configuration guide (when created)
- `README.md`: Main project README (when created)

---

## 🤖 AI Assistant Guidelines

### When Working on This Project

1. **Always check current state first**: Read entity files, check migrations, verify routes exist
2. **Follow the schema exactly**: Do not deviate from the documented entity relationships
3. **Maintain French naming**: UI labels and some variables use French (e.g., `nom_restaurant`, `ardoise`)
4. **Security first**: Always verify ownership before edit/delete operations
5. **Test as you go**: Create tests for each major feature
6. **Use Symfony best practices**:
   - Use `make:*` commands when possible
   - Follow Symfony directory structure
   - Use dependency injection
   - Use repository pattern for database queries
7. **PDF/Image generation**: Ensure templates are simple HTML/CSS (no JavaScript)
8. **Single active slate rule**: Always enforce only one active slate per user
9. **Commit frequently**: Clear, descriptive commit messages in English

### Implementation Order (Recommended)

1. **Phase 1: Foundation**
   - Create User entity with slug
   - Implement authentication (make:auth, make:registration-form)
   - Test login/logout/register

2. **Phase 2: Core Entities**
   - Create Ardoise, Section, Plat entities
   - Generate migrations
   - Create repositories

3. **Phase 3: Admin Interface**
   - Create DashboardController (list slates)
   - Create ArdoiseController (CRUD)
   - Implement dynamic forms with Stimulus
   - Add active slate toggle

4. **Phase 4: Public Display**
   - Create PublicController
   - Implement web view (HTML)
   - Implement PDF generation
   - Implement image generation

5. **Phase 5: Social Sharing**
   - Add modal to dashboard
   - Implement Facebook share link
   - Implement Instagram image download

6. **Phase 6: Polish**
   - Add form validation
   - Improve error handling
   - Add flash messages
   - Style public page (chalk board look)

7. **Phase 7: Testing & Deployment**
   - Write functional tests
   - Test on production-like environment
   - Deploy to O2Switch
   - Verify all features work

### Questions to Ask User

If unclear during implementation:
- "Should I create the User entity with all fields now, or start with basic authentication?"
- "Do you want fixtures/sample data for testing?"
- "Should I implement all CRUD operations at once or incrementally?"
- "Do you want logging/monitoring for PDF generation failures?"
- "Should there be a confirmation modal before deleting a slate?"

---

## 📞 Support & Contact

For questions or issues:
1. Check `INDEX.md` for documentation navigation
2. Review `cahier-des-charges.md` for detailed specifications
3. Consult Symfony documentation for framework questions
4. Check git history for implementation details

---

**Last Updated**: 18 November 2025
**Version**: 1.0
**Status**: Ready for implementation

This document is maintained as the single source of truth for AI assistants working on L'Ardoise Magique. Update this file as the project evolves.
