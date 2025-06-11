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

#------------------------------------------------------------
#        Script MySQL.
#------------------------------------------------------------


#------------------------------------------------------------
# Table: modele_panneau
#------------------------------------------------------------

CREATE TABLE modele_panneau(
        panneau_modele Varchar (50) NOT NULL
	,CONSTRAINT modele_panneau_PK PRIMARY KEY (panneau_modele)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: marque_onduleur
#------------------------------------------------------------

CREATE TABLE marque_onduleur(
        marque_onduleur Varchar (50) NOT NULL
	,CONSTRAINT marque_onduleur_PK PRIMARY KEY (marque_onduleur)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: region
#------------------------------------------------------------

CREATE TABLE region(
        code_region Int NOT NULL ,
        nom_region  Varchar (50) NOT NULL
	,CONSTRAINT region_PK PRIMARY KEY (code_region)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: departement
#------------------------------------------------------------

CREATE TABLE departement(
        code_departement Varchar (3) NOT NULL ,
        nom_departement  Varchar (50) NOT NULL
	,CONSTRAINT departement_PK PRIMARY KEY (code_departement)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: commune_france
#------------------------------------------------------------

CREATE TABLE commune_france(
        code_insee       Varchar (5) NOT NULL ,
        nom_commune      Varchar (50) NOT NULL ,
        code_region      Int NOT NULL ,
        code_departement Varchar (3) NOT NULL
	,CONSTRAINT commune_france_PK PRIMARY KEY (code_insee)

	,CONSTRAINT commune_france_region_FK FOREIGN KEY (code_region) REFERENCES region(code_region)
	,CONSTRAINT commune_france_departement0_FK FOREIGN KEY (code_departement) REFERENCES departement(code_departement)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: modele_onduleur
#------------------------------------------------------------

CREATE TABLE modele_onduleur(
        modele_onduleur Varchar (50) NOT NULL
	,CONSTRAINT modele_onduleur_PK PRIMARY KEY (modele_onduleur)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: marque_panneau
#------------------------------------------------------------

CREATE TABLE marque_panneau(
        marque_panneau Varchar (50) NOT NULL
	,CONSTRAINT marque_panneau_PK PRIMARY KEY (marque_panneau)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: installateur
#------------------------------------------------------------

CREATE TABLE installateur(
        installateur Varchar (50) NOT NULL
	,CONSTRAINT installateur_PK PRIMARY KEY (installateur)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: batiment
#------------------------------------------------------------

CREATE TABLE batiment(
        id              Int NOT NULL ,
        mois_install    Varchar (2) NOT NULL ,
        annee_install   Year NOT NULL ,
        nb_panneaux     Int NOT NULL ,
        nb_onduleurs    Int NOT NULL ,
        puissance_crete Int NOT NULL ,
        surface         Float NOT NULL ,
        locality        Varchar (50) NOT NULL ,
        lat             Float NOT NULL ,
        lon             Float NOT NULL ,
        code_insee      Varchar (5) NOT NULL ,
        modele_onduleur Varchar (50) NOT NULL ,
        marque_onduleur Varchar (50) NOT NULL ,
        installateur    Varchar (50) NOT NULL ,
        panneau_modele  Varchar (50) NOT NULL ,
        marque_panneau  Varchar (50) NOT NULL
	,CONSTRAINT batiment_PK PRIMARY KEY (id)

	,CONSTRAINT batiment_commune_france_FK FOREIGN KEY (code_insee) REFERENCES commune_france(code_insee)
	,CONSTRAINT batiment_modele_onduleur0_FK FOREIGN KEY (modele_onduleur) REFERENCES modele_onduleur(modele_onduleur)
	,CONSTRAINT batiment_marque_onduleur1_FK FOREIGN KEY (marque_onduleur) REFERENCES marque_onduleur(marque_onduleur)
	,CONSTRAINT batiment_installateur2_FK FOREIGN KEY (installateur) REFERENCES installateur(installateur)
	,CONSTRAINT batiment_modele_panneau3_FK FOREIGN KEY (panneau_modele) REFERENCES modele_panneau(panneau_modele)
	,CONSTRAINT batiment_marque_panneau4_FK FOREIGN KEY (marque_panneau) REFERENCES marque_panneau(marque_panneau)
)ENGINE=InnoDB;

# Requêtes d'insertion des données

INSERT INTO region (code_region, nom_region)
SELECT DISTINCT reg_code, reg_nom 
FROM donnee_commune;

INSERT INTO departement (code_departement, nom_departement) 
SELECT DISTINCT (dep_code,dep_nom)
FROM donnee_commune;

INSERT INTO marque_panneau (marque_panneau)
SELECT panneaux_marque 
FROM donnee_data;

INSERT INTO modele_panneau (panneau_modele)
SELECT DISTINCT panneaux_modele
FROM donnee_data;

INSERT INTO marque_onduleur (marque_onduleur)
SELECT onduleur_marque 
FROM donnee_data;

INSERT INTO modele_onduleur (modele_onduleur)
SELECT DISTINCT onduleur_modele 
FROM donnee_data;

INSERT INTO installateur (installateur)
SELECT DISTINCT installateur 
FROM donnee_data;

INSERT INTO commune_france (code_insee, nom_commune, code_region, code_departement)
SELECT DISTINCT code_insee, nom_standard, reg_code, dep_code 
FROM donnee_commune;

INSERT IGNORE INTO batiment (id, mois_install, annee_install, nb_panneaux, nb_onduleurs, puissance_crete, surface, locality,lat, lon, code_insee, modele_onduleur, marque_onduleur, installateur, panneau_modele, marque_panneau) 
SELECT d.id, d.mois_installation, d.an_installation, d.nb_panneaux, d.nb_onduleur, d.puissance_crete, d.surface, d.locality, d.lat, d.lon, c.code_insee, d.onduleur_modele, d.onduleur_marque, d.installateur, d.panneaux_modele, d.panneaux_marque
FROM donnee_data d 
JOIN donnee_commune c ON c.nom_standard = d.locality 

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