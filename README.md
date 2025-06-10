# Projet CIR2 – Visualisation des installations de panneaux solaires

Ce projet web permet de visualiser et d’administrer les installations de panneaux solaires en France.  
Vous pouvez rechercher des installations, les afficher sur une carte interactive, consulter des statistiques et gérer les données via une interface d’administration sécurisée.

---

## Structure du projet

- **README.md** : Ce fichier d’explications
- **css/** : Feuilles de styles (`style.css`, `leaflet.css`, etc.)
- **html/** : Pages HTML principales (`index.html`, `map.html`, `details.html`, etc.)
- **images/** : Images utilisées sur le site
- **js/** : Scripts JavaScript (`main.js`, `map.js`, `form.js`, etc.)
- **php/** : Scripts PHP pour la base de données et les requêtes serveur (`request.php`, `database.php`, etc.)
- **back/** : Interface d’administration (`back.php`, `back.js`, `back.css`, etc.)

---

## Fonctionnalités principales

- Visualisation des installations sur une carte interactive (Leaflet)
- Recherche et filtrage par année, département, marque, etc.
- Affichage des détails d’une installation
- Statistiques régionales et nationales (graphiques)
- Interface d’administration sécurisée (ajout, modification, suppression d’installations)

---

## Installation

1. **Configurer les accès à la base** dans `php/login.php` si besoin (identifiants, nom de la base…)
2. **Ouvrir `html/index.html`** dans votre navigateur pour accéder au site
3. **Accéder à l’administration** via `/back/back.php` (mot de passe par défaut : `admin2025`)

---

## Accès administrateur

- Interface : `/back/back.php`
- Déconnexion : `/back/logout.php`
- Mot de passe par défaut : `admin2025` 

---

## Auteurs

Anita & Mael  
CIR2 2025

---

*Projet réalisé dans le cadre du cursus CIR2 – 2025*