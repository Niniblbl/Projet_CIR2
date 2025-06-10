<?php
session_start(); // Démarre la session pour pouvoir la détruire
session_destroy(); // Détruit toutes les données de session (déconnexion de l'admin)
header('Location: ../back/admin_login.html'); // Redirige l'utilisateur vers la page de login admin
exit;
?>