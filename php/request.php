<?php
require_once('../php/database.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    $stmt = $db->query('SELECT DISTINCT nom_departement,code_departement FROM departement ORDER BY RAND() LIMIT 20');
    while ($row = $stmt->fetch()) {
        echo '<option value="' . htmlspecialchars($row['code_departement']) . '">' . htmlspecialchars($row['nom_departement']) . '</option>';
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

//details 
$id = $_GET['id'] ?? null;
if ($type === 'batiment_details' && !empty($id)) {
    $stmt = $db->prepare('SELECT 
        d.nom_departement,
        b.marque_panneau,
        b.nb_panneaux,
        b.mois_install,
        b.panneau_modele,
        b.nb_onduleur,
        b.annee_install,
        b.marque_onduleur,
        b.puissance_crete,
        b.surface,
        b.modele_onduleur
      FROM batiment b
      JOIN commune_france c ON c.nom_commune = b.locality
      JOIN departement d ON d.code_departement = c.code_departement
      WHERE b.id = :id
      LIMIT 1');
    $stmt->execute(['id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    sendJsonData($result, 200);
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

if ($type === 'batiments_coords') {
    $params = [];
    $sql = 'SELECT b.lat, b.lon, b.id
        FROM batiment b
        JOIN commune_france c ON c.code_insee = b.code_insee
        JOIN departement d ON d.code_departement = c.code_departement
        WHERE b.lat IS NOT NULL AND b.lon IS NOT NULL';

    if (!empty($_GET['annee'])) {
        $sql .= ' AND b.annee_install = :annee';
        $params['annee'] = $_GET['annee'];
    }
    if (!empty($_GET['departement'])) {
        $sql .= ' AND d.code_departement = :departement';
        $params['departement'] = $_GET['departement'];
    }
    $sql .= ' ORDER BY RAND() LIMIT 20';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result);
    exit;
}

// Affichage des 100 premières installations
if ($type === 'all_installations') {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

    // Total count
    $stmt = $db->query('SELECT COUNT(*) FROM batiment');
    $total = $stmt->fetchColumn();

    // Data
    $sql = 'SELECT 
        b.id,
        b.locality,
        b.annee_install,
        b.mois_install,
        r.nom_region AS region,
        d.nom_departement AS departement,
        c.nom_commune AS ville,
        b.installateur,
        b.panneau_modele,
        b.marque_panneau,
        b.nb_panneaux,
        b.marque_onduleur,
        b.modele_onduleur,
        b.nb_onduleur,
        b.puissance_crete,
        b.surface,
        b.lat,
        b.lon
      FROM batiment b
      LEFT JOIN commune_france c ON c.nom_commune = b.locality
      LEFT JOIN departement d ON d.code_departement = c.code_departement
      LEFT JOIN region r ON r.code_region = c.code_region
      LIMIT :limit OFFSET :offset';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendJsonData(['rows' => $rows, 'total' => $total], 200);
    exit;
}

// Ajout d'une installation
if ($type === 'add_installation') {
    $fields = ['locality','marque_panneau','panneau_modele','nb_panneaux','marque_onduleur','modele_onduleur','nb_onduleur','annee_install','mois_install','puissance_crete','surface','lat','lon', 'installateur'];
    $values = [];
    foreach ($fields as $f) {
        $values[$f] = $_POST[$f] ?? null;
        // Pour lat et lon, si vide, force à null
        if (($f === 'lat' || $f === 'lon') && ($values[$f] === '' || $values[$f] === null)) {
            $values[$f] = null;
        }
    }

    // Cherche le code_insee ET le nom officiel de la commune
    $stmt = $db->prepare('SELECT code_insee, nom_commune FROM commune_france WHERE nom_commune = ? LIMIT 1');
    $stmt->execute([$values['locality']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || empty($row['code_insee'])) {
        sendJsonData(['success'=>false, 'error'=>'Commune inconnue, code INSEE introuvable pour "'.$values['locality'].'"'], 400);
        exit;
}

// Ajoute le code_insee et remplace locality par le nom officiel
$values['code_insee'] = $row['code_insee'];
$values['locality'] = $row['nom_commune'];

    $sql = "INSERT INTO batiment (".implode(',',$fields).",code_insee) VALUES (:".implode(',:',$fields).",:code_insee)";
    $stmt = $db->prepare($sql);
    $stmt->execute($values);
    sendJsonData(['success'=>true], 200);
    exit;
}

// Modification d'une installation
if ($type === 'update_installation') {
    $data = json_decode(file_get_contents('php://input'), true);
    $fields = ['locality','marque_panneau','panneau_modele','nb_panneaux','marque_onduleur','modele_onduleur','nb_onduleur','annee_install','mois_install','puissance_crete','surface','lat','lon','installateur'];
    $set = [];
    $params = [];
    foreach ($fields as $f) {
        if(isset($data[$f])) {
            $set[] = "$f = :$f";
            $params[$f] = $data[$f];
        }
    }
    $params['id'] = $data['id'] ?? null;
    $sql = "UPDATE batiment SET ".implode(', ',$set)." WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    sendJsonData(['success'=>true], 200);
    exit;
}

// Suppression d'une installation
if ($type === 'delete_installation' && !empty($_GET['id'])) {
    $stmt = $db->prepare('DELETE FROM batiment WHERE id = :id');
    $stmt->execute(['id' => $_GET['id']]);
    sendJsonData(['success'=>true], 200);
    exit;
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
            JOIN commune_france c ON b.code_insee = c.code_insee
            JOIN region r ON c.code_region = r.code_region
            JOIN departement d ON c.code_departement = d.code_departement
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
        $sql .= " AND d.code_departement = ?";
        $params[] = $dep;
    }
    $sql .= " LIMIT 100"; 
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendJsonData($resultats, 200);
    exit;
}


?>