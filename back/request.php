<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('../back/database.php');

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

// Statistiques globales
if ($type === 'stats') {
    $enregistrements = dbRequestEnregistrement($db);
    $installateurs = dbRequestNbInstallateurs($db);
    $onduleurs = dbRequestMarqueOnduleurs($db);
    $panneaux = dbRequestMarquesPanneaux($db);

    sendJsonData([
        'enregistrements' => $enregistrements[0]['COUNT(id)'] ?? 0,
        'installateurs'   => $installateurs['installateur'] ?? 0,
        'marques'         => $onduleurs['marque_onduleur'] ?? 0,
        'panneaux'        => $panneaux['marque_panneau'] ?? 0
    ], 200);
}

// Listes pour les selects
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
    $stmt = $db->query('SELECT DISTINCT nom_departement FROM departement ORDER BY RAND() LIMIT 20');
    while ($row = $stmt->fetch()) {
        echo '<option value="' . htmlspecialchars($row['nom_departement']) . '">' . htmlspecialchars($row['nom_departement']) . '</option>';
    }
    exit;
}

if ($type === 'annees') {
    $stmt = $db->query('SELECT DISTINCT annee_install FROM batiment ORDER BY annee_install DESC LIMIT 20');
    while ($row = $stmt->fetch()) {
        echo '<option value="' . htmlspecialchars($row['annee_install']) . '">' . htmlspecialchars($row['annee_install']) . '</option>';
    }
    exit;
}

// Graphiques
if ($type === 'installations_par_annee') {
    $iannee = dbRequestInstallationsParAnnee($db);
    sendJsonData($iannee, 200);
}

if ($type === 'installations_par_region') {
    $regions = dbRequestInstallationsParRegion($db);
    sendJsonData($regions, 200);
}

if ($type === 'installations_par_region_et_annee') {
    $data = dbRequestInstallationsParRegionEtAnnee($db);
    sendJsonData($data, 200);
}

if ($type === 'recherche') {
    $marqueOndul = $_GET['marque_ondul'] ?? '';
    $marquePan = $_GET['marque_pan'] ?? '';
    $dep = $_GET['dep'] ?? '';

    $sql = "SELECT CONCAT(b.mois_install, '/', b.annee_install) AS date,
                b.nb_panneaux,
                b.surface,
                b.puissance_crete,
                r.nom_region AS localisation
            FROM batiment b
            JOIN commune_france c ON b.locality = c.nom_commune
            JOIN region r ON c.code_region = r.code_region
            WHERE 1";
    $params = [];

    if ($marqueOndul !== '') {
        $sql .= " AND b.marque_onduleur = ?";
        $params[] = $marqueOndul;
    }
    if ($marquePan !== '') {
        $sql .= " AND b.marque_panneau = ?";
        $params[] = $marquePan;
    }
    if ($dep !== '') {
        $sql .= " AND b.departement = ?";
        $params[] = $dep;
    }
    $sql .= " LIMIT 100"; 
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendJsonData($resultats, 200);
    exit;
}



//sendJsonData([
   // 'enregistrements' => $enregistrements[0]['COUNT(id)'] ?? 0,
   // 'installateurs' => $installateurs['installateur'] ?? 0,
    //'marques' => $onduleurs['marque_onduleur'] ?? 0,
   // 'panneaux' => $panneaux['marque_panneau'] ?? 0 ], 200);
 //   exit;



?>