<?php 

require_once('../php/login.php');
// Définit les constantes de connexion à la base de données

//Etablit une connexion à la base de données
function dbConnect()
{
  try
  {
    $db = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_NAME.';charset=utf8;'.
      'port='.DB_PORT, DB_USER, DB_PASSWORD);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
  }
  catch (PDOException $exception)
  {
    error_log('Connection error: '.$exception->getMessage());
    return false;
  }
  return $db;
}

function dbRequestEnregistrement($db){
    try{
        $request = 'SELECT COUNT(id) FROM batiment';
        $statement = $db->prepare($request);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $exception){
        error_log('Request error: '.$exception->getMessage());
        return false;
    }
    return $result;
}
function dbRequestAnnee($db){
    try{
        $request = 'SELECT AVG(nb_installations) AS moyenne_par_annee FROM (SELECT COUNT(*) AS nb_installations FROM batiment GROUP BY annee_install) as t';
        $statement = $db->prepare($request);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $exception){
        error_log('Request error: '.$exception->getMessage());
        return false;
    }
    return $result;
}

function dbRequestRegion($db){
    try{
        $request = 'SELECT r.nom_region, COUNT(b.id_installation) AS nb_installations
                    FROM batiment b
                    JOIN commune_france c ON b.code_insee = c.code_insee
                    JOIN region r ON c.code_region = r.code_region
                    GROUP BY r.nom_region
                    ORDER BY nb_installations DESC';
        $statement = $db->prepare($request);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $exception){
        error_log('Request error: '.$exception->getMessage());
        return false;
    }
    return $result;
}

function dbRequestNbInstallateurs($db){
    try{
        $request = 'SELECT COUNT(*) AS nb_installateurs FROM installateur';
        $statement = $db->prepare($request);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
    }
    catch (PDOException $exception){
        error_log('Request error: '.$exception->getMessage());
        return false;
    }
    return $result;
}

function dbRequestNbMarquesPanneaux($db){
    try{
        $request = 'SELECT COUNT(*) AS nb_marques_panneaux FROM marque_panneau';
        $statement = $db->prepare($request);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
    }
    catch (PDOException $exception){
        error_log('Request error: '.$exception->getMessage());
        return false;
    }
    return $result;
}
?>