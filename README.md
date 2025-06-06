<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>README - Projet CIR2</title>
</head>
<body>
  <h1>Projet_CIR2</h1>
  <p>
    Ce projet permet de visualiser les installations de panneaux solaires en France.<br>
    Vous pouvez rechercher des installations sur une carte interactive ainsi que par des filtres.<br>
    Des statistiques sur les installations sont également disponibles.
  </p>
  <h2>Structure du projet</h2>
  <ul>
    <li><strong>README.md</strong> : Ce fichier</li>
    <li><strong>css/</strong> : Feuilles de styles (<code>style.css</code>, <code>leaflet.css</code>)</li>
    <li><strong>html/</strong> : Pages HTML (<code>index.html</code>, <code>map.html</code>, <code>details.html</code>, etc.)</li>
    <li><strong>images/</strong> : Images utilisées sur le site</li>
    <li><strong>js/</strong> : Scripts JavaScript (<code>main.js</code>, <code>map.js</code>, etc.)</li>
    <li><strong>php/</strong> : Scripts PHP pour la base de données et les requêtes serveur (<code>request.php</code>, <code>database.php</code>, etc.)</li>
    <li><strong>partie administrateur</strong> : accéder par <code>/back/back.html</code>
  </ul>
  <h2>Fonctionnalités principales</h2>
  <ul>
    <li>Visualisation des installations sur une carte interactive (Leaflet)</li>
    <li>Recherche et filtrage par année, département, marque, etc.</li>
    <li>Affichage des détails d’une installation</li>
    <li>Statistiques régionales et nationales</li>
  </ul>
  <h2>Installation</h2>
  <ol>
    <li>Cloner le dépôt ou copier les fichiers sur votre serveur local (WAMP, XAMPP, etc.)</li>
    <li>Importer la base de données</li>
    <li>Configurer les accès à la base dans <code>php/login.php</code> si besoin</li>
    <li>Ouvrir <code>html/index.html</code> dans votre navigateur</li>
  </ol>
  <h2>Auteurs</h2>
  <p>
    Anita et Mael<br>
    CIR2 2025
  </p>
</body>
</html>