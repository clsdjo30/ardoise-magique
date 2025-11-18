Cahier des Charges : Projet "L'Ardoise Magique"
Version : 1.1 (Ajout du Partage Social)
Date : 18/11/2025
Auteur : Clsdjo30
Projet : Application "L'Ardoise Magique" (Micro-SaaS)
1. Contexte et Objectifs
1.1. Contexte
Les restaurateurs perdent un temps précieux chaque jour à gérer leur "Plat du Jour". La saisie est souvent manuelle, redondante (Word, site web, caisse) et mal optimisée pour une diffusion numérique moderne.
1.2. Objectif du Projet
Créer une application web (micro-SaaS) simple et centralisée qui permet à un restaurateur de :
Saisir son ardoise une seule fois via un formulaire simple.
Générer automatiquement deux formats de sortie principaux :
Une page web publique (pour affichage sur TV, site web en iFrame).
Un document PDF A4 (pour impression en salle).
Fournir des outils de partage optimisés pour les réseaux sociaux (Facebook, Instagram) en un clic.
1.3. Objectifs du Portfolio
Démontrer la maîtrise de la stack Symfony 7 en mode monolithe.
Prouver la capacité à créer un outil "métier" à forte valeur ajoutée.
Montrer la maîtrise d'un cycle de développement complet (Auth, CRUD, Génération PDF, Génération d'Image).
2. Acteurs et Use Cases (Scénarios Utilisateur)
2.1. Acteurs
Restaurateur (Admin) : L'utilisateur authentifié qui gère ses ardoises.
Visiteur (Public) : Le client final ou le système qui consulte l'ardoise publique.
2.2. Use Cases - Restaurateur (Admin)


ID
Use Case
Description
UC-A1
Gestion de Compte
L'Admin peut créer un compte (s'enregistrer), se connecter et se déconnecter.
UC-A2
Voir le Dashboard
Après connexion, l'Admin voit un tableau de bord listant toutes ses ardoises créées.
UC-A3
Créer une Ardoise
L'Admin peut créer une nouvelle ardoise (ex: "Menu de Noël", "Plats du Jour du 18/11").
UC-A4
Éditer une Ardoise
L'Admin peut modifier le titre de l'ardoise et gérer dynamiquement les sections et les plats (ajouter/modifier/supprimer).
UC-A5
Supprimer une Ardoise
L'Admin peut supprimer une ardoise (avec confirmation).
UC-A6
Activer une Ardoise
L'Admin peut "Activer" une ardoise. Une seule ardoise peut être active à la fois. C'est cette ardoise qui sera visible sur les liens publics.
UC-A7
Obtenir les Liens
Sur le dashboard, l'Admin peut copier (via un bouton) l'URL publique de son ardoise (ex: .../ardoise/le-petit-nimois).
UC-A8
Partager l'Ardoise Active
Sur le dashboard, l'Admin clique sur "Partager". Un pop-up (modal) s'ouvre et lui propose 3 choix : 1. Partager le lien sur Facebook. 2. Télécharger l'image pour Instagram. 3. Copier le lien public.

2.3. Use Cases - Visiteur (Public)
ID
Use Case
Description
UC-V1
Voir l'Ardoise Web
Le Visiteur accède à l'URL publique (.../ardoise/{slug}) et voit l'ardoise active du restaurant, formatée pour le web.
UC-V2
Voir l'Ardoise PDF
Le Visiteur accède à l'URL PDF (.../ardoise/{slug}/pdf) et le navigateur ouvre/télécharge le PDF de l'ardoise active.
UC-V3
Voir l'Ardoise Image
Le Visiteur (ou l'Admin via le modal) accède à l'URL (.../ardoise/{slug}/image) et le navigateur télécharge une image .jpg de l'ardoise.
UC-V4
Voir Ardoise Inexistante
Si le Visiteur accède à un slug qui n'existe pas ou si aucune ardoise n'est active, un message "Aucune ardoise publiée pour le moment" s'affiche.

3. Spécifications Techniques
3.1. Stack Technique (Confirmée O2Switch)
Framework : Symfony 7 (Monolithe)
Frontend (Admin) : Twig + AssetMapper + Bootstrap 5 (y compris Modals)
Frontend (Public) : Twig + CSS custom (pour le look "craie")
Base de Données : MySQL / MariaDB
ORM : Doctrine
Authentification : Symfony SecurityBundle (make:auth)
Admin CRUD : Symfony make:crud + Formulaires Symfony
Génération PDF : mpdf/mpdf (PHP pur, pas de dépendance binaire)
Génération Image (PDF > JPG) : spatie/pdf-to-image (wrapper pour imagick. Prérequis : l'extension imagick doit être activée sur O2Switch).
Interactivité : Stimulus (fourni avec Symfony) pour la gestion dynamique des formulaires.
3.2. Modèle de Données (Entités / ORM)
User (Restaurateur)
id (PK, int)
email (string, unique)
roles (json)
password (string, hash)
nom_restaurant (string)
slug (string, unique) : Clé publique du restaurant (ex: "le-petit-nimois")
ardoises (relation: OneToMany vers Ardoise)
Ardoise
id (PK, int)
titre (string, ex: "Ardoise du 18 Novembre")
date_creation (datetime)
is_active (boolean, default: false)
restaurateur (relation: ManyToOne vers User)
sections (relation: OneToMany vers Section, cascade: persist, remove)
Section (ex: Entrées, Plats, Desserts)
id (PK, int)
titre (string, ex: "Nos Entrées")
ordre (int, pour le tri)
ardoise (relation: ManyToOne vers Ardoise)
plats (relation: OneToMany vers Plat, cascade: persist, remove)
Plat
id (PK, int)
nom (string, ex: "Velouté de Potimarron")
description (string, nullable, ex: "et ses éclats de châtaigne")
prix (decimal ou int en centimes)
ordre (int, pour le tri)
section (relation: ManyToOne vers Section)
4. Architecture des Routes (Routing)
4.1. Routes Publiques (Pas de sécurité)
GET /ardoise/{slug}
Nom : app_public_web
Contrôleur : PublicController::showWeb(string $slug)
Description : Affiche l'ardoise active (HTML).
GET /ardoise/{slug}/pdf
Nom : app_public_pdf
Contrôleur : PublicController::showPdf(string $slug)
Description : Génère et affiche le PDF de l'ardoise active.
GET /ardoise/{slug}/image
Nom : app_public_image
Contrôleur : PublicController::showImage(string $slug)
Description : Génère et télécharge une image .jpg de l'ardoise active.
4.2. Routes d'Authentification
GET /login (name: app_login)
POST /login
GET /logout (name: app_logout)
GET /register (name: app_register)
POST /register
4.3. Routes Administrateur (Protégées : is_granted('ROLE_USER'))
GET /admin
Nom : app_admin_dashboard
Contrôleur : Admin\DashboardController::index
GET /admin/ardoise/new
Nom : app_admin_ardoise_new
Contrôleur : Admin\ArdoiseController::new
POST /admin/ardoise/new (idem)
GET /admin/ardoise/{id}/edit
Nom : app_admin_ardoise_edit
Contrôleur : Admin\ArdoiseController::edit(Ardoise $ardoise)
POST /admin/ardoise/{id}/edit (idem)
POST /admin/ardoise/{id}/toggle-active
Nom : app_admin_ardoise_toggle_active
Contrôleur : Admin\ArdoiseController::toggleActive(Ardoise $ardoise)
DELETE /admin/ardoise/{id}
Nom : app_admin_ardoise_delete
Contrôleur : Admin\ArdoiseController::delete(Ardoise $ardoise)
5. Spécifications de Sécurité
Authentification : Gérée par Symfony SecurityBundle. Mots de passe hashés.
Autorisation (Contrôle d'accès) :
Routes /admin/* exigent ROLE_USER.
Propriété (Ownership) : Un User ne peut jamais accéder (éditer, supprimer, activer) une Ardoise qui ne lui appartient pas (Vérification via Voter ou if ($ardoise->getRestaurateur() !== $this->getUser())).
Protection CSRF : Jetons CSRF actifs sur tous les formulaires (par défaut).
Protection XSS : Échappement des données en sortie (par défaut avec Twig).
6. Spécifications UI/UX (Templates Bootstrap 5)
base.html.twig (Layout Global)
Inchangé (navbar, container, flash messages).
security/login.html.twig (Connexion)
Inchangé (card, form).
admin/dashboard/index.html.twig (Tableau de Bord)
Composants : list-group, btn, badge, modal
Structure :
<h1>Mon Tableau de Bord</h1>
...
<a href="{{ path('app_admin_ardoise_new') }}" class="btn btn-success mb-3">...</a>
<div class="list-group">
{% for ardoise in ardoises %}
<div class="list-group-item d-flex ...">
<div> {{ ardoise.titre }} <span class="badge bg-{{ ... }}">{{ ... }}</span> </div>
<div class="btn-group">
<!-- Bouton de Partage -->
{% if ardoise.isActive %}
<button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#shareModal">Partager</button>
{% else %}
<form method="POST" action="{{ path('app_admin_ardoise_toggle_active', {id: ardoise.id}) }}"> <button type="submit" class="btn btn-sm btn-outline-warning">Définir Active</button> </form>
{% endif %}
<a href="{{ path('app_admin_ardoise_edit', {id: ardoise.id}) }}" class="btn btn-sm btn-primary">Éditer</a>
...Formulaire de suppression (DELETE)...
</div></div>
{% endfor %}
</div>
<!-- Ajout du Modal de Partage (une seule fois, hors de la boucle) -->
{% if ardoises|length > 0 %}
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<div class="modal-header"> <h5 class="modal-title" id="shareModalLabel">Partager l'ardoise active</h5> <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> </div>
<div class="modal-body">
<p>Utilisez ces outils pour diffuser votre ardoise.</p>
<div class="mb-3">
<strong>Lien Public (pour iFrame / Email)</strong>
<input type="text" class="form-control" value="{{ url('app_public_web', {slug: app.user.slug}) }}" readonly>
</div>
<div class="d-grid gap-2">
<h5>Partage Rapide</h5>
<a href="https://www.facebook.com/sharer/sharer.php?u={{ url('app_public_web', {slug: app.user.slug}) }}" target="_blank" class="btn btn-primary"><i class="bi bi-facebook"></i> Partager sur Facebook</a>
<a href="{{ path('app_public_image', {slug: app.user.slug}) }}" download="ardoise-{{ "now"|date("Y-m-d") }}.jpg" class="btn btn-warning text-dark"><i class="bi bi-instagram"></i> Télécharger pour Instagram (.jpg)</a>
</div>
</div>
</div></div></div>
{% endif %}
admin/ardoise/_form.html.twig (Formulaire Ardoise)
Inchangé (CollectionType, Stimulus pour ajout/suppression dynamique).
public/show_web.html.twig (Ardoise Publique Web)
Inchangé (CSS "craie", layout minimaliste).
public/show_pdf.html.twig (Template pour le PDF)
Inchangé (HTML sémantique pur, optimisé pour mpdf et spatie/pdf-to-image).
7. Logique Fonctionnelle Clé (Implémentation)
7.1. Génération PDF (Contrôleur)
Le PublicController::showPdf fera :
Trouver le User par $slug, puis l'Ardoise active.
Rendre le template public/show_pdf.html.twig dans une variable $html.
Instancier mpdf: $mpdf = new \Mpdf\Mpdf();.
Écrire le HTML : $mpdf->WriteHTML($html);.
Retourner la réponse PDF au navigateur : $mpdf->Output('ardoise.pdf', 'I'); (inline).
7.2. Gestion "Active" (Contrôleur)
Le ArdoiseController::toggleActive fera :
Vérifier la propriété (Voter/Sécurité).
Commencer une transaction Doctrine.
Récupérer toutes les autres ardoises du User et les passer à is_active = false.
Passer l'ardoise cible ($ardoise) à is_active = true.
flush() Doctrine.
Rediriger vers le dashboard avec un message flash.
7.3. Gestion du Slug (Entité User)
Utiliser stof/doctrine-extensions-bundle (Gedmo Sluggable) pour générer le slug à partir de nom_restaurant lors de la création du User.
7.4. (NOUVEAU) Génération Image (Contrôleur)
Le PublicController::showImage fera :
(Logique de cache optionnelle : vérifier si l'image existe déjà et est à jour).
Trouver le User par $slug, puis l'Ardoise active.
Générer le PDF en mémoire ou dans un fichier temporaire :
Rendre le template public/show_pdf.html.twig en $html.
Utiliser mpdf pour créer le PDF et le sauver dans un chemin temporaire (ex: sys_get_temp_dir() . '/' . uniqid() . '.pdf').
Convertir le PDF en Image (JPG) :
$pdfToImage = new Spatie\PdfToImage\Pdf($tempPdfPath);
$tempImagePath = ... (chemin temporaire pour le jpg)
$pdfToImage->saveImage($tempImagePath);
Supprimer le fichier PDF temporaire.
Retourner une BinaryFileResponse (Symfony) pour le fichier JPG, en forçant le téléchargement (download=true).
Supprimer le fichier JPG temporaire après l'envoi (ou gérer un cache).
