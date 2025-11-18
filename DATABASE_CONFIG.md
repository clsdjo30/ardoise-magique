# Configuration de la Base de Données

Ce projet supporte différentes configurations de base de données selon votre environnement de développement.

## 🪟 Windows (Développement Local avec Scoop)

Si vous développez sur Windows avec Scoop et que vous avez SQLite activé :

### Configuration `.env.local`

```env
###> doctrine/doctrine-bundle ###
# SQLite for development (Windows - local environment)
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
###< doctrine/doctrine-bundle ###
```

### Commandes

```bash
# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Démarrer le serveur
symfony serve
```

### Avantages
- ✅ Aucune installation serveur nécessaire
- ✅ Fichier SQLite dans `var/data.db`
- ✅ Parfait pour le développement en solo
- ✅ Léger et rapide

---

## 🐧 Linux / Mac (Développement avec Docker)

Si vous préférez utiliser Docker Compose avec PostgreSQL :

### Configuration `.env.local`

```env
###> doctrine/doctrine-bundle ###
# PostgreSQL for development (using Docker Compose)
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
###< doctrine/doctrine-bundle ###
```

### Commandes

```bash
# Démarrer PostgreSQL
docker compose up -d

# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Démarrer le serveur
symfony serve
```

### Avantages
- ✅ Environnement isolé
- ✅ Plus proche de la production
- ✅ Support complet PostgreSQL
- ✅ Facile à partager avec l'équipe

---

## 🚀 Production (O2Switch)

En production, le projet utilise **MariaDB** hébergé sur O2Switch.

### Configuration `.env.local` (sur le serveur)

```env
###> symfony/framework-bundle ###
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=<générer-un-secret-unique>
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
# MariaDB for production (O2Switch)
DATABASE_URL="mysql://db_user:db_password@localhost:3306/db_name?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
###< doctrine/doctrine-bundle ###
```

### Déploiement

```bash
# Sur le serveur O2Switch via SSH
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear --env=prod
```

---

## 📝 Notes Importantes

### Fichier `.env.local`

⚠️ **Le fichier `.env.local` n'est PAS tracké par Git** (il est dans `.gitignore`).

Chaque développeur doit créer son propre fichier `.env.local` selon son environnement :

1. Copier `.env` vers `.env.local`
2. Modifier `DATABASE_URL` selon votre configuration
3. Ne jamais commiter `.env.local`

### Compatibilité des Migrations

Les migrations Doctrine sont compatibles avec tous les moteurs de base de données. La migration `Version20251118000000.php` utilise la syntaxe SQLite mais Doctrine la traduit automatiquement pour PostgreSQL ou MySQL/MariaDB.

### Vérifier les Extensions PHP

Selon votre configuration, vérifiez que vous avez l'extension PDO appropriée :

```bash
# Windows (Scoop)
php -m | findstr pdo

# Linux/Mac
php -m | grep pdo
```

Extensions nécessaires :
- **SQLite** : `pdo_sqlite` + `sqlite3`
- **PostgreSQL** : `pdo_pgsql`
- **MySQL/MariaDB** : `pdo_mysql`

---

## 🔧 Changement de Configuration

Pour passer d'une base de données à une autre :

1. Modifier `DATABASE_URL` dans `.env.local`
2. Supprimer le cache : `php bin/console cache:clear`
3. Créer la nouvelle base : `php bin/console doctrine:database:create`
4. Exécuter les migrations : `php bin/console doctrine:migrations:migrate`

⚠️ **Attention** : Les données ne seront pas migrées automatiquement entre les bases.

---

## 📞 Support

En cas de problème avec la base de données :

1. Vérifier les extensions PHP : `php -m`
2. Vérifier la configuration : `php bin/console debug:dotenv`
3. Tester la connexion : `php bin/console doctrine:schema:validate`

**Date de mise à jour** : 18 Novembre 2025
