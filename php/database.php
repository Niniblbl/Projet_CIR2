<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Définit les constantes de connexion à la base de données
require_once('../php/login.php');

//Etablit une connexion à la base de données
function dbConnect()
{
  try{
    // Crée une nouvelle connexion PDO avec les constantes définies dans login.php
    $db = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_NAME.';charset=utf8;'.
      'port='.DB_PORT, DB_USER, DB_PASSWORD);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Active les erreurs PDO
  }
  catch (PDOException $exception){
    // En cas d'erreur de connexion, log l'erreur et retourne false
    error_log('Connection error: '.$exception->getMessage());
    return false;
  }
  return $db; // Retourne l'objet PDO si tout va bien
}

// Retourne le nombre total d'enregistrements dans la table batiment
function dbRequestEnregistrement($db){
    try{
        $request = 'SELECT COUNT(id) FROM batiment'; //requête SQL pour compter les enregistrements
        $statement = $db->prepare($request); // Prépare la requête SQL
        $statement->execute(); // Exécute la requête préparée
        // On récupère le résultat sous forme de tableau associatif
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $exception){
        // Log l'erreur SQL si la requête échoue
        error_log('Request error: '.$exception->getMessage());
        return false;
    }
    return $result; // Retourne le résultat de la requête
}

// Retourne le nombre d'installations par année (pour les graphes)
function dbRequestInstallationsParAnnee($db){
    try{
        $request = 'SELECT annee_install, COUNT(*) AS nb FROM batiment GROUP BY annee_install ORDER BY annee_install'; // Requête SQL pour compter les installations par année
        $statement = $db->prepare($request); // Prépare la requête SQL
        $statement->execute();  // Exécute la requête préparée
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);   // Récupère le résultat sous forme de tableau associatif
    }
    catch (PDOException $exception){
        error_log('Request error: '.$exception->getMessage()); // Log l'erreur SQL si la requête échoue
        return false;
    }
    return $result; // Retourne le résultat de la requête
}

// Retourne le nombre d'installations par région (pour les graphes)
function dbRequestInstallationsParRegion($db){
    try{
        $request = 'SELECT r.nom_region, COUNT(*) AS nb
                    FROM batiment b
                    JOIN commune_france c ON b.locality = c.nom_commune
                    JOIN region r ON c.code_region = r.code_region
                    GROUP BY r.nom_region';  // Requête SQL pour compter les installations par région
        $statement = $db->prepare($request); // Prépare la requête SQL
        $statement->execute(); // Exécute la requête préparée
        $result = $statement->fetchAll(PDO::FETCH_ASSOC); // Récupère le résultat sous forme de tableau associatif
    }
    catch (PDOException $exception){
        error_log('Request error: '.$exception->getMessage()); // Log l'erreur SQL si la requête échoue
        return false;
    }
    return $result; // Retourne le résultat de la requête
}

// Retourne le nombre d'installations par région et par année (pour les graphes)
function dbRequestInstallationsParRegionEtAnnee($db){
    try{
        $request = 'SELECT r.nom_region, b.annee_install, COUNT(*) AS nb
                    FROM batiment b
                    JOIN commune_france c ON b.locality = c.nom_commune
                    JOIN region r ON c.code_region = r.code_region
                    GROUP BY r.nom_region, b.annee_install'; // Requête SQL pour compter les installations par région et par année
        $statement = $db->prepare($request); // Prépare la requête SQL
        $statement->execute(); // Exécute la requête préparée
        $result = $statement->fetchAll(PDO::FETCH_ASSOC); // Récupère le résultat sous forme de tableau associatif
    }
    catch (PDOException $exception){
        error_log('Request error: '.$exception->getMessage()); // Log l'erreur SQL si la requête échoue
        return false;
    }
    return $result; // Retourne le résultat de la requête
}

// Retourne le nombre d'installateurs (pour les stats)
function dbRequestNbInstallateurs($db){
    try{
        $request = 'SELECT COUNT(*) AS installateur FROM installateur'; // Requête SQL pour compter les installateurs
        $statement = $db->prepare($request); // Prépare la requête SQL
        $statement->execute(); // Exécute la requête préparée
        $result = $statement->fetch(PDO::FETCH_ASSOC); // Récupère le résultat sous forme de tableau associatif
    }
    catch (PDOException $exception){
        error_log('Request error: '.$exception->getMessage()); // Log l'erreur SQL si la requête échoue
        return false;
    }
    return $result; // Retourne le résultat de la requête
}

// Retourne le nombre de marques d'onduleur (pour les stats)
function dbRequestMarqueOnduleurs($db){
    try{
        $request = 'SELECT COUNT(*) AS marque_onduleur FROM marque_onduleur'; // Requête SQL pour compter les marques d'onduleur
        $statement = $db->prepare($request); // Prépare la requête SQL
        $statement->execute(); // Exécute la requête préparée
        $result = $statement->fetch(PDO::FETCH_ASSOC); // Récupère le résultat sous forme de tableau associatif
    }
    catch (PDOException $exception){
        error_log('Request error: '.$exception->getMessage()); // Log l'erreur SQL si la requête échoue
        return false;
    }
    return $result; // Retourne le résultat de la requête
}

// Retourne le nombre de marques de panneaux (pour les stats)
function dbRequestMarquesPanneaux($db){
    try{
        $request = 'SELECT COUNT(*) AS marque_panneau FROM marque_panneau'; // Requête SQL pour compter les marques de panneaux
        $statement = $db->prepare($request); // Prépare la requête SQL
        $statement->execute(); // Exécute la requête préparée
        $result = $statement->fetch(PDO::FETCH_ASSOC); // Récupère le résultat sous forme de tableau associatif
    }
    catch (PDOException $exception){
        error_log('Request error: '.$exception->getMessage()); // Log l'erreur SQL si la requête échoue
        return false;
    }
    return $result; // Retourne le résultat de la requête
}

// Retourne le nombre de panneaux pour un bâtiment donné (utile pour les détails)
function dbRequestnbPanneaux($db,$id){
    try{
        $request = 'SELECT nb_panneaux FROM batiment WHERE id = :id'; // Requête SQL pour obtenir le nombre de panneaux pour un bâtiment spécifique
        $statement = $db->prepare($request); // Prépare la requête SQL
        $statement->execute(); // Exécute la requête préparée avec l'ID du bâtiment
        $result = $statement->fetch(PDO::FETCH_ASSOC); // Récupère le résultat sous forme de tableau associatif
    }
    catch (PDOException $exception){
        error_log('Request error: '.$exception->getMessage()); // Log l'erreur SQL si la requête échoue
        return false;
    }
    return $result; // Retourne le résultat de la requête
}
?>