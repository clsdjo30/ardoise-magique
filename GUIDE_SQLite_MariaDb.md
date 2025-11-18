# 🔧 Guide Rapide : Configuration SQLite (Dev) vs MariaDB (Prod)

## TL;DR - Résumé Rapide

### 👨‍💻 Développement (SQLite)

```bash
git clone https://github.com/clsdjo30/ardoise-magique.git
cd ardoise-magique
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony serve
# ✅ Base de données créée dans var/data.db
```

### 🚀 Production (MariaDB sur O2Switch)

```bash
ssh user@o2switch.fr
git clone ...
composer install --no-dev
# Éditer .env : DATABASE_URL="mysql://..."
php bin/console doctrine:database:create --env=prod
php bin/console doctrine:migrations:migrate --env=prod
# ✅ Base de données créée sur serveur O2Switch
```

---

## 📊 Comparaison Détaillée

### Base de Données

| Critère | SQLite (Dev) | MariaDB (Prod) |
|---------|--------------|----------------|
| **Fichier** | `var/data.db` | Base de données serveur |
| **Installation** | Aucune | Fournie par O2Switch |
| **Persistance** | Fichier local | Serveur distant |
| **Multi-user** | Limité | Excellent |
| **Backups** | Manuel | Automatique (O2Switch) |
| **Performance** | Développement | Production |

### Extensions PHP Requises

| Extension | Dev | Prod | Rôle |
|-----------|-----|------|------|
| `pdo_sqlite` | ✅ | ❌ | Accès SQLite |
| `pdo_mysql` | ❌ | ✅ | Accès MariaDB |
| `pdo_mariadb` | ❌ | ✅ | Alternative à pdo_mysql |
| `imagick` | ✅ | ✅ | Génération images PDF |
| `gd` | ✅ | ✅ | Alternative à imagick |

---

## 🔐 Variables d'Environnement

### Fichier `.env` - Développement (SQLite)

```env
APP_ENV=dev
APP_DEBUG=true
APP_SECRET=dev_secret_change_in_production
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
MAILER_DSN=smtp://localhost
```

**Points importants :**
- `APP_ENV=dev` : Mode développement
- `APP_DEBUG=true` : Afficher erreurs détaillées
- `DATABASE_URL` pointe vers SQLite local
- Aucune connexion serveur requise

### Fichier `.env` - Production (MariaDB O2Switch)

```env
APP_ENV=prod
APP_DEBUG=false
APP_SECRET=very_long_random_secret_generated_in_production
DATABASE_URL="mysql://user_o2switch:password@db.o2switch.fr:3306/database_name"
MAILER_DSN=smtp://user:pass@smtp.o2switch.fr:465
```

**Points importants :**
- `APP_ENV=prod` : Mode production
- `APP_DEBUG=false` : Pas d'erreurs au client
- `DATABASE_URL` pointe vers MariaDB O2Switch
- Credentials du panel O2Switch
- `APP_SECRET` doit être très sécurisé

---

## 🚀 Processus de Déploiement

### Étape 1 : Préparation en Local (SQLite)

```bash
# Travailler en local avec SQLite
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Tester
symfony serve
# Accéder à http://localhost:8000/admin
# Vérifier les fonctionnalités

# Commit du code (jamais var/data.db)
git add .
git commit -m "Fonctionnalité X implémentée"
git push
```

### Étape 2 : Déploiement (MariaDB O2Switch)

```bash
# 1. Connexion SSH
ssh user@o2switch.fr

# 2. Récupérer le code
git clone https://github.com/clsdjo30/ardoise-magique.git
cd ardoise-magique

# 3. Installer dépendances
composer install --no-dev --optimize-autoloader

# 4. Copier .env.example
cp .env.example .env

# 5. IMPORTANT : Éditer .env avec les credentials O2Switch
nano .env
# Remplacer DATABASE_URL par les infos du panel O2Switch

# 6. Générer clé secrète (pour production)
php bin/console secrets:generate-keys

# 7. Créer la base de données MariaDB (vierge)
php bin/console doctrine:database:create --env=prod

# 8. Migrer le schéma (créer tables)
php bin/console doctrine:migrations:migrate --env=prod --no-interaction

# 9. Compiler assets
php bin/console assets:install --env=prod
npm run build

# 10. Configurer permissions
chmod -R 775 var/cache var/log var/data

# 11. Vérifier installation
# Accéder à https://ardoise-magique.com/admin
```

---

## 🔍 Vérification de Configuration

### Développement (SQLite)

```bash
# Vérifier pdo_sqlite
php -m | grep pdo_sqlite
# Output: pdo_sqlite

# Vérifier la base de données
ls -la var/data.db
# Output: -rw-r--r-- 1 user user 12288 Nov 18 10:00 var/data.db

# Vérifier les tables créées
php bin/console doctrine:query:sql "SELECT name FROM sqlite_master WHERE type='table';"
```

### Production (MariaDB O2Switch)

```bash
# Vérifier pdo_mysql
php -m | grep pdo_mysql
# Output: pdo_mysql

# Tester la connexion
php bin/console doctrine:database:create --env=prod --if-not-exists

# Lister les tables
php bin/console doctrine:query:sql "SHOW TABLES;" --env=prod

# Vérifier le schéma
php bin/console doctrine:migrations:list --env=prod
```

---

## ⚠️ Erreurs Courantes & Solutions

### Erreur : "PDOException: SQLSTATE[HY000]: General error"

**Cause:** Mauvaise configuration `DATABASE_URL`

**Solution:**
```bash
# Vérifier le format DATABASE_URL
# Correct : mysql://user:password@hostname:port/database_name
# Exemple : mysql://user_o2switch:pass@db.o2switch.fr:3306/db_12345

# Tester la connexion
php bin/console doctrine:database:create --env=prod --if-not-exists
```

### Erreur : "Could not find driver pdo_sqlite"

**Cause:** Extension PHP `pdo_sqlite` non disponible

**Solution:**
```bash
# Vérifier l'installation
php -m | grep pdo_sqlite

# Si absent, contacter l'hébergeur
# En développement local : réinstaller PHP avec pdo_sqlite
```

### Erreur : "Could not find driver pdo_mysql"

**Cause:** Extension PHP `pdo_mysql` non disponible sur O2Switch

**Solution:**
```bash
# Contacter O2Switch pour activer pdo_mysql
# Ou utiliser pdo_mariadb si disponible
# DATABASE_URL="mysql://..." fonctionne avec pdo_mariadb aussi
```

### Erreur : "Database does not exist"

**Cause:** Base de données non créée sur O2Switch

**Solution:**
```bash
# Créer la base de données
php bin/console doctrine:database:create --env=prod

# Ou via panel O2Switch :
# Control Panel → Databases → Créer nouvelle base
```

### Fichier `var/data.db` commité par accident

**Solution:**
```bash
# Supprimer du repository
git rm --cached var/data.db
git commit -m "Remove SQLite development database"

# Vérifier que .gitignore contient /var/data.db
cat .gitignore | grep "var/data.db"

# Push
git push
```

---

## 📝 Checklist Développeur

### Avant de Commencer

- [ ] Clone du repository
- [ ] `composer install` exécuté
- [ ] `.env` copié de `.env.example`
- [ ] `php -m | grep pdo_sqlite` → ✅
- [ ] `php bin/console doctrine:database:create` → ✅
- [ ] `php bin/console doctrine:migrations:migrate` → ✅

### Pendant le Développement

- [ ] Travailler avec SQLite (`var/data.db`)
- [ ] Jamais commiter `var/data.db`
- [ ] Vérifier `.gitignore` contient `/var/data.db`
- [ ] Commiter régulièrement les migrations

### Avant le Déploiement

- [ ] Tous les tests passent : `composer test`
- [ ] Migrations générées pour chaque changement d'entité
- [ ] Code reviséé et mergé
- [ ] Version taguée en Git

### Lors du Déploiement

- [ ] Credentials O2Switch prêts (du panel)
- [ ] `DATABASE_URL` correctement configurée
- [ ] Migration vers MariaDB effectuée : `--env=prod`
- [ ] Assets compilés : `--env=prod`
- [ ] Vérification HTTPS active
- [ ] Logs consultés : `tail -f var/log/prod.log`

---

## 🎯 Points Clés à Retenir

1. **SQLite est pour le développement local uniquement**
   - Fichier `var/data.db`
   - Jamais commiter en Git
   - Facile à réinitialiser

2. **MariaDB est pour la production O2Switch**
   - Credentials du panel O2Switch
   - Base de données vierge lors du déploiement
   - Pas de migration des données locales

3. **Migrations Doctrine fonctionnent pour les deux**
   - Même schéma pour SQLite et MariaDB
   - Seule la connexion change

4. **Environnement change beaucoup**
   - `APP_ENV=dev` vs `APP_ENV=prod`
   - `APP_DEBUG=true` vs `APP_DEBUG=false`
   - DATABASE_URL complètement différente

5. **Pas de données partagées entre dev et prod**
   - Dev : Données locales (SQLite)
   - Prod : Données de production (MariaDB)
   - Intentionnel et sécurisé

---

## 📚 Ressources

- [Cahier des Charges Complet](./cahier-des-charges.md)
- [README Détaillé](./README.md)
- [Modifications SQLite/MariaDB](./MODIFICATIONS_SQLite_MariaDB.md)
- [Symfony Doctrine Documentation](https://www.doctrine-project.org/)
- [Symfony Environment Configuration](https://symfony.com/doc/current/configuration.html#environments)

---

**Bon développement ! 🚀**
