# 📑 Index de la Documentation - Projet "L'Ardoise Magique"

**Version:** 2.0 - SQLite (Dev) + MariaDB (Prod)  
**Date:** 18 novembre 2025  
**Total:** 2862 lignes de documentation

---

## 🎯 Rapide Navigation

### Pour Qui?

- **👨‍💻 Développeurs** → Commencer par [GUIDE_SQLite_MariaDB.md](#quick-start)
- **📋 Gestionnaires** → Lire [Cahier des Charges](#cahier-des-charges)
- **🚀 DevOps/Hébergement** → [Déploiement O2Switch](#deploiement)
- **💡 Intégrateurs** → [README.md](#readme)

---

## 📚 Fichiers Livrés (6 documents)

### 1. 🎓 Cahier des Charges Complet

**Fichier:** [`cahier_des_charges_ardoise_magique.md`](./cahier_des_charges_ardoise_magique.md)  
**Lignes:** ~1175  
**Audience:** Spécifications complètes du projet

**Sections principales:**
- Contexte & Objectifs
- Cas d'Usage (UC-A1 à UC-V4)
- Stack Technique (SQLite dev, MariaDB prod)
- Modèle de Données (User, Ardoise, Section, Plat)
- Routes & API
- Spécifications de Sécurité
- UI/UX avec Exemples Bootstrap 5
- Logique Métier (PDF, Images, Activation)
- Plan d'Implémentation (9 jours)
- Points de Vigilance

**Quand le consulter :**
- ✅ Comprendre les exigences fonctionnelles
- ✅ Vérifier les cas d'usage implémentés
- ✅ Valider l'architecture technique
- ✅ Consulter les exemples de code/templates

---

### 2. 📖 README - Guide Complet

**Fichier:** [`README.md`](./README.md)  
**Lignes:** ~802  
**Audience:** Développeurs, Intégrateurs

**Sections principales:**
- Aperçu du projet
- Architecture & Stack
- Installation Locale (SQLite)
- Configuration .env (Dev/Prod)
- Démarrage Rapide
- Structure du Projet
- Workflow Développement
- Tests
- Déploiement O2Switch (MariaDB)
- Sécurité & Checklist
- Troubleshooting
- Roadmap Futur

**Quand le consulter :**
- ✅ Installer le projet en local
- ✅ Configurer l'environnement
- ✅ Comprendre la structure
- ✅ Tester & déployer
- ✅ Troubleshooting

---

### 3. 🔧 Guide Rapide SQLite ↔ MariaDB

**Fichier:** [`GUIDE_SQLite_MariaDB.md`](./GUIDE_SQLite_MariaDB.md)  
**Lignes:** ~400  
**Audience:** Développeurs, DevOps

**Sections principales:**
- TL;DR Résumé Rapide
- Comparaison Détaillée (dev/prod)
- Variables d'Environnement
- Processus de Déploiement
- Vérification de Configuration
- Erreurs Courantes & Solutions
- Checklist Développeur
- Points Clés à Retenir

**Quand le consulter :**
- ✅ Configuration rapide
- ✅ Déploiement
- ✅ Erreurs de configuration
- ✅ Vérification final

---

### 4. 📝 Résumé Modifications

**Fichier:** [`MODIFICATIONS_SQLite_MariaDB.md`](./MODIFICATIONS_SQLite_MariaDB.md)  
**Lignes:** ~350  
**Audience:** Gestionnaires, Développeurs seniors

**Contenu:**
- Statistiques des modifications
- Avant/Après du cahier des charges
- Avant/Après du README
- Flux de Développement vs Production
- Checklist Implémentation
- Bénéfices de l'approche
- Fichiers à Créer/Configurer

**Quand le consulter :**
- ✅ Comprendre ce qui a été modifié
- ✅ Valider les changements
- ✅ Voir les comparaisons avant/après

---

### 5. ⚙️ Fichiers de Configuration

#### `.env.example`

**Ligne:** ~105  
**Usage:** Template pour configuration développement & production

```bash
# Copier avant de lancer le projet
cp .env.example .env
```

**Contient:**
- Variables SQLite (développement)
- Variables MariaDB (production - commentées)
- Configuration Symfony
- Configuration Mailer
- Configuration Sentry (optionnel)
- Configuration S3 (optionnel)

---

#### `.gitignore`

**Lignes:** ~80  
**Usage:** Ignorer les fichiers à ne pas commiter

**Inclut:**
- `var/data.db` (SQLite - développement local)
- `var/` (cache, logs)
- `vendor/` (dépendances Composer)
- `node_modules/` (dépendances npm)
- Fichiers IDE (.idea, .vscode)
- Fichiers temporaires
- OS spécifiques (macOS, Windows, Linux)

---

## 🗺️ Plan de Lecture Recommandé

### 🎯 Lecture Rapide (30 minutes)

1. Ce fichier (INDEX)
2. [`GUIDE_SQLite_MariaDB.md`](./GUIDE_SQLite_MariaDB.md) - TL;DR section
3. [`README.md`](./README.md) - Section "Installation Locale"

**Résultat:** Comprendre le fonctionnement de base

### 📚 Lecture Complète (2-3 heures)

1. [`cahier_des_charges_ardoise_magique.md`](./cahier_des_charges_ardoise_magique.md) - Lecture entière
2. [`README.md`](./README.md) - Lecture entière
3. [`GUIDE_SQLite_MariaDB.md`](./GUIDE_SQLite_MariaDB.md) - Lecture entière

**Résultat:** Maîtriser complètement le projet

### 🚀 Pour le Déploiement (1 heure)

1. [`GUIDE_SQLite_MariaDB.md`](./GUIDE_SQLite_MariaDB.md) - Section "Processus de Déploiement"
2. [`README.md`](./README.md) - Section "📡 Déploiement (O2Switch)"
3. [`cahier_des_charges_ardoise_magique.md`](./cahier_des_charges_ardoise_magique.md) - Section "10.0 SQLite vs MariaDB"

**Résultat:** Déployer en production correctement

---

## 🔑 Concepts Clés

### SQLite (Développement Local)

```
Fichier local: var/data.db
Avantage: Zéro configuration
Utilisation: Développement, Tests locaux
Persistence: Fichier (ignoré par .gitignore)
Multi-user: Limité (OK pour 1 développeur)
Commande: php bin/console doctrine:database:create
```

### MariaDB (Production O2Switch)

```
Serveur: db.o2switch.fr (ou localhost)
Avantage: Performance, Scalabilité, Backups
Utilisation: Production en ligne
Persistence: Base de données serveur
Multi-user: Excellent (OK pour N utilisateurs)
Commande: php bin/console doctrine:database:create --env=prod
```

### Points Importants

- ✅ **SQLite** = Développement local seulement
- ✅ **MariaDB** = Production O2Switch seulement
- ✅ **Migrations Doctrine** = Identiques pour les deux
- ✅ **Pas de migration de données** = Volontaire et sûr
- ✅ **DATABASE_URL** = Seule variable qui change

---

## 📋 Checklist d'Installation

### Étape 1 : Préparation (5 min)

- [ ] Clone du repository
- [ ] Lire ce fichier (INDEX)
- [ ] Lire [`GUIDE_SQLite_MariaDB.md`](./GUIDE_SQLite_MariaDB.md) - TL;DR

### Étape 2 : Setup Local (10 min)

- [ ] Copier `.env.example` → `.env`
- [ ] `composer install`
- [ ] `php bin/console doctrine:database:create`
- [ ] `php bin/console doctrine:migrations:migrate`
- [ ] `symfony serve`

### Étape 3 : Développement (Jours 1-7)

- [ ] Implémenter les features selon cahier des charges
- [ ] Tester avec SQLite local (`var/data.db`)
- [ ] Commiter régulièrement

### Étape 4 : Déploiement (Jour 8)

- [ ] Lire [`GUIDE_SQLite_MariaDB.md`](./GUIDE_SQLite_MariaDB.md) - Section Déploiement
- [ ] Obtenir credentials O2Switch
- [ ] SSH sur serveur O2Switch
- [ ] Configurer `DATABASE_URL` pour MariaDB
- [ ] Exécuter migrations en production

### Étape 5 : Validation (Jour 8-9)

- [ ] Vérifier https://ardoise-magique.com/admin
- [ ] Créer un utilisateur de test
- [ ] Tester les fonctionnalités
- [ ] Vérifier logs

---

## 🤝 Quand Consulter Quel Fichier

### Cas: "Je veux comprendre les exigences"
→ [`cahier_des_charges_ardoise_magique.md`](./cahier_des_charges_ardoise_magique.md) - Sections 1 à 2

### Cas: "Je veux installer le projet"
→ [`README.md`](./README.md) - Section "📦 Installation & Setup"

### Cas: "J'ai une erreur de configuration"
→ [`GUIDE_SQLite_MariaDB.md`](./GUIDE_SQLite_MariaDB.md) - Section "Erreurs Courantes"

### Cas: "Je dois déployer en production"
→ [`GUIDE_SQLite_MariaDB.md`](./GUIDE_SQLite_MariaDB.md) - Section "Processus de Déploiement"

### Cas: "Je veux connaître les routes disponibles"
→ [`cahier_des_charges_ardoise_magique.md`](./cahier_des_charges_ardoise_magique.md) - Section "5. Architecture des Routes"

### Cas: "Je veux savoir la structure du code"
→ [`README.md`](./README.md) - Section "📋 Structure du Projet"

### Cas: "Je veux voir un exemple de template"
→ [`cahier_des_charges_ardoise_magique.md`](./cahier_des_charges_ardoise_magique.md) - Section "7. Spécifications UI/UX"

### Cas: "Je veux vérifier la sécurité"
→ [`cahier_des_charges_ardoise_magique.md`](./cahier_des_charges_ardoise_magique.md) - Section "6. Spécifications de Sécurité"

### Cas: "Je veux tester en local"
→ [`README.md`](./README.md) - Section "🧪 Tests"

### Cas: "Je dois utiliser Claude Code"
→ [`README.md`](./README.md) - Section "Guide Claude Code pour L'Implémentation"

---

## 🔗 Navigation Interne

### Index des Sections par Fichier

#### Cahier des Charges
- [1. Contexte et Objectifs](#contexte)
- [2. Acteurs et Cas d'Usage](#use-cases)
- [3. Spécifications Techniques](#tech-stack)
  - [3.1 Stack Confirmée](#stack) - **SQLite (dev) + MariaDB (prod)**
  - [3.3 Prérequis Système](#prerequis) - **Dev/Prod séparés**
  - [3.4 Configuration O2Switch](#config-o2switch)
- [4. Architecture des Données](#data-architecture)
- [5. Architecture des Routes](#routes)
- [6. Spécifications de Sécurité](#security)
- [7. Spécifications UI/UX](#ui-ux)
- [8. Logique Fonctionnelle Clé](#core-logic)
- [9. Phase d'Implémentation](#implementation)
  - [9.1 Setup Projet](#setup) - **SQLite configuration**
  - [9.1b Production](#production-setup) - **MariaDB configuration**
- [10. Points de Vigilance](#vigilance)
  - [10.0 SQLite vs MariaDB](#sqlite-vs-mariadb) - **NOUVEAU**

#### README
- [Architecture](#architecture)
- [Installation Locale (SQLite)](#installation-locale)
- [Configuration .env](#config-env)
- [Déploiement O2Switch (MariaDB)](#deploiement-o2switch)

#### GUIDE SQLite/MariaDB
- [TL;DR](#tldr)
- [Comparaison Détaillée](#comparaison)
- [Vérification Configuration](#verification)
- [Erreurs Courantes](#erreurs-courantes)

---

## 📞 Support & Questions

### Questions Fréquentes

**Q: Dois-je installer MySQL en local?**
R: Non! SQLite fonctionne par défaut sans installation serveur.

**Q: Vais-je perdre mes données en déploiement?**
R: Non. Les données locales (SQLite) restent locales. Production repart avec une BD vierge.

**Q: Quelle extension PHP pour production?**
R: `pdo_mysql` ou `pdo_mariadb`. Vérifier avec `php -m | grep pdo_mysql`.

**Q: Comment migrer les données de dev en prod?**
R: Vous ne devez pas! Chaque environnement a ses propres données.

**Q: Puis-je utiliser MariaDB en local?**
R: Oui, mais ce n'est pas recommandé. SQLite est plus simple pour le dev.

---

## 🎯 Prochaines Étapes

1. **Lire ce fichier** (vous le faites!) ✓
2. **Lire** [`GUIDE_SQLite_MariaDB.md`](./GUIDE_SQLite_MariaDB.md) - TL;DR
3. **Installer localement** avec SQLite
4. **Commencer à développer** selon [`cahier_des_charges_ardoise_magique.md`](./cahier_des_charges_ardoise_magique.md)
5. **Déployer en production** selon [`README.md`](./README.md) + [`GUIDE_SQLite_MariaDB.md`](./GUIDE_SQLite_MariaDB.md)

---

## 📊 Statistiques du Projet

| Métrique | Valeur |
|----------|--------|
| **Lignes de Doc** | 2862 |
| **Fichiers** | 6 |
| **Cas d'Usage** | 8 (UC-A1 à UC-V4) |
| **Entités DB** | 4 (User, Ardoise, Section, Plat) |
| **Routes Publiques** | 3 |
| **Routes Admin** | 5+ |
| **Durée Estimation** | 9 jours dev |
| **Couverture Tests** | 80%+ |

---

## 🏁 Résumé Exécutif

**L'Ardoise Magique** est une application **Micro-SaaS** pour restaurateurs permettant de :

✅ Créer et gérer des ardoises/menus numériques  
✅ Générer automatiquement PDF & Images  
✅ Partager en un clic sur réseaux sociaux  
✅ Gérer facilement via interface web  

**Stack:** Symfony 7 + EasyAdmin + Bootstrap 5  
**Dev:** SQLite (local, zéro config)  
**Prod:** MariaDB (O2Switch, performant)  
**Durée:** 9 jours de développement  
**Docs:** 2862 lignes de documentation complète  

---

**Date:** 18 novembre 2025  
**Version:** 2.0 (SQLite + MariaDB)  
**Prêt pour développement ! 🚀**

---

[← Retour aux fichiers](./README.md)
