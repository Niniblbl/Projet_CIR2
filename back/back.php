<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_pwd'])) {
    if ($_POST['admin_pwd'] === 'admin2025') { // mot de passe ici
        $_SESSION['admin_ok'] = true;
        header('Location: back.php');
        exit;
    } else {
        $error = "Mot de passe incorrect.";
    }
}
if (empty($_SESSION['admin_ok'])) {
    header('Location: ../back/admin_login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Administration des installations</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../back/back.css">
</head>
<body>
  <header>
    <img src="../images/logo.png" alt="Logo" id="logo">
    <nav>
      <a href="../html/index.html">Accueil</a>
      <a href="../html/map.html">Carte</a>
      <a href="../html/recherche.html">Recherche</a>
      <a href="logout.php" style="color:#e74c3c;font-weight:bold;">Déconnexion</a>
    </nav>
  </header>
  <main>
    <h1>Administration des installations</h1>
    <section>
      <h2>Ajouter une installation</h2>
      <form id="add-form">
        <input type="text" name="locality" placeholder="Commune" required>
        <input type="text" name="marque_panneau" placeholder="Marque panneau" required>
        <input type="text" name="panneau_modele" placeholder="Modèle panneau" required>
        <input type="number" name="nb_panneaux" placeholder="Nombre panneaux" required>
        <input type="text" name="marque_onduleur" placeholder="Marque onduleur" required>
        <input type="text" name="modele_onduleur" placeholder="Modèle onduleur" required>
        <input type="number" name="nb_onduleur" placeholder="Nombre onduleurs"  required>
        <input type="number" name="annee_install" placeholder="Année install" required>
        <input type="text" name="mois_install" placeholder="Mois install" required>
        <input type="number" name="puissance_crete" placeholder="Puissance crête">
        <input type="number" name="surface" placeholder="Surface">
        <input type="text" name="lat" placeholder="Latitude">
        <input type="text" name="lon" placeholder="Longitude">
        <input type="text" name="installateur" placeholder="Installateur" required>
        <button type="submit">Ajouter</button>
      </form>
    </section>
    <section>
      <h2>Liste des installations (100 max)</h2>
      <div style="overflow-x:auto;">
        <table id="table-installations">
          <thead>
            <tr>
              <th>ID</th>
              <th>Année install</th>
              <th>Mois install</th>
              <th>Région</th>
              <th>Département</th>
              <th>Commune</th>
              <th>installateur</th>
              <th>détails</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="table-body">
          </tbody>
        </table>
        <div id="pagination" style="text-align:center; margin:18px 0;"></div>
      </div>
    </section>
  </main>
  <footer>
    Made by Anita and Mael CIR2 2025
  </footer>
  <script src="back.js"></script>
</body>
</html>