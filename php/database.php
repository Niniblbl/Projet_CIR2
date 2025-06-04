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

function dbRequestInstallationsParAnnee($db){
    try{
        $request = 'SELECT annee_install, COUNT(*) AS nb FROM batiment GROUP BY annee_install ORDER BY annee_install';
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

function dbRequestInstallationsParRegion($db){
    try{
        $request = 'SELECT r.nom_region, COUNT(*) AS nb
                    FROM batiment b
                    JOIN commune_france c ON b.locality = c.nom_commune
                    JOIN region r ON c.code_region = r.code_region
                    GROUP BY r.nom_region';
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

function dbRequestInstallationsParRegionEtAnnee($db){
    try{
        $request = 'SELECT r.nom_region, b.annee_install, COUNT(*) AS nb
                    FROM batiment b
                    JOIN commune_france c ON b.locality = c.nom_commune
                    JOIN region r ON c.code_region = r.code_region
                    GROUP BY r.nom_region, b.annee_install';
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
        $request = 'SELECT COUNT(*) AS installateur FROM installateur';
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

function dbRequestMarqueOnduleurs($db){
    try{
        $request = 'SELECT COUNT(*) AS marque_onduleur FROM marque_onduleur';
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

function dbRequestMarquesPanneaux($db){
    try{
        $request = 'SELECT COUNT(*) AS marque_panneau FROM marque_panneau';
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