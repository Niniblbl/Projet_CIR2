<?php
require_once('../php/database.php');

//connexion à la base de données
$db = dbConnect();
if (!$db)
{
    header('HTTP/1.1 503 Service Unavailable');
    exit;
}

// récupération de la méthode HTTP et de la ressource URL demandée
$requestMethod = $_SERVER['REQUEST_METHOD'];
$request = '';
if (isset($_SERVER['PATH_INFO'])) {
    $request = substr($_SERVER['PATH_INFO'], 1);
    $request = explode('/', $request);
    $requestRessource = array_shift($request);

    // Récupération de l'id de la ressource demandée
    $id = array_shift($request);
    if ($id == '')
        $id = NULL;
} else {
    $requestRessource = null;
    $id = null;
}

$enregistrements = dbRequestEnregistrement($db);
$count = 0;
if ($enregistrements && isset($enregistrements[0]['COUNT(id)'])) { // Vérification de l'existence de la clé
    $count = $enregistrements[0]['COUNT(id)'];
}

function sendJsonData($data, $code){
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    
    if($data !== false){
        http_response_code($code);
        echo json_encode($data);
    } 
    else{
        http_response_code(400);
        echo json_encode(['error' => 'Bad Request']);
    }
}

$type = $_GET['type'] ?? null;

if ($type === 'stats') {
    $enregistrements = dbRequestEnregistrement($db);
    $installateurs = dbRequestNbInstallateurs($db);
    $onduleurs = dbRequestMarqueOnduleurs($db);
    $panneaux = dbRequestMarquesPanneaux($db);
}

if ($type === 'marque_ondul') {
    $stmt = $db->query('SELECT DISTINCT marque_onduleur FROM marque_onduleur LIMIT 20');
    while ($row = $stmt->fetch()) {
        echo '<option value="' . htmlspecialchars($row['marque_onduleur']) . '">' . htmlspecialchars($row['marque_onduleur']) . '</option>';
    }
    exit;
}
if ($type === 'marque_pan') {
    $stmt = $db->query('SELECT DISTINCT marque_panneau FROM marque_panneau LIMIT 20');
    while ($row = $stmt->fetch()) {
        echo '<option value="' . htmlspecialchars($row['marque_panneau']) . '">' . htmlspecialchars($row['marque_panneau']) . '</option>';
    }
    exit;
}
if ($type === 'dep') {
    $stmt = $db->query('SELECT DISTINCT nom_departement FROM departement LIMIT 20');
    while ($row = $stmt->fetch()) {
        echo '<option value="' . htmlspecialchars($row['nom_departement']) . '">' . htmlspecialchars($row['nom_departement']) . '</option>';
    }
    exit;
}    

if ($type === 'installations_par_annee') {
    $iannee = dbRequestInstallationsParAnnee($db);
    sendJsonData($iannee, 200);
    exit;
}

if ($type === 'installations_par_region') {
    $regions = dbRequestInstallationsParRegion($db);
    sendJsonData($regions, 200);
    exit;
}

if ($type === 'installations_par_region_et_annee') {
    $data = dbRequestInstallationsParRegionEtAnnee($db);
    sendJsonData($data, 200);
    exit;
}

sendJsonData([
    'enregistrements' => $enregistrements[0]['COUNT(id)'] ?? 0,
    'installateurs' => $installateurs['installateur'] ?? 0,
    'marques' => $onduleurs['marque_onduleur'] ?? 0,
    'panneaux' => $panneaux['marque_panneau'] ?? 0 ], 200);
    exit;
?>