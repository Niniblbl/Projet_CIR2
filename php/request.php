<?php
// Inclusion des fonctions de connexion et requêtes SQL
require_once('../php/database.php');

// Affiche les erreurs PHP (utile en développement)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//connexion à la base de données
$db = dbConnect();
if (!$db){
    // Si la connexion échoue, on renvoie une erreur HTTP
    header('HTTP/1.1 503 Service Unavailable');
    exit;
}

// Récupération de la méthode HTTP et de la ressource demandée (pas utilisé ici, mais prêt pour une API REST)
$requestMethod = $_SERVER['REQUEST_METHOD']; // Récupère la méthode HTTP (GET, POST, etc.)
$request = ''; 
if (isset($_SERVER['PATH_INFO'])) { // Vérifie si PATH_INFO est défini (utile pour les routes REST)
    $request = substr($_SERVER['PATH_INFO'], 1); // Enlève le premier slash
    $request = explode('/', $request); // Sépare la chaîne en un tableau par les slashes
    $requestRessource = array_shift($request); // Récupère la première partie de la ressource demandée (ex: "batiment", "stats", etc.)

    // Récupération de l'id de la ressource demandée
    $id = array_shift($request);
    if ($id == '')
        $id = NULL; // Si l'id est vide, on le met à null
} else {
    $requestRessource = null; // Si PATH_INFO n'est pas défini, on initialise la ressource à null
    $id = null;
}

// Récupère le nombre total d'enregistrements (pour les stats ou la pagination)
$enregistrements = dbRequestEnregistrement($db);
$count = 0;
if ($enregistrements && isset($enregistrements[0]['COUNT(id)'])) { // Vérification de l'existence de la clé
    $count = $enregistrements[0]['COUNT(id)']; //compte le nombre d'enregistrements
}

// Fonction utilitaire pour envoyer une réponse JSON propre
function sendJsonData($data, $code){
    header('Content-Type: application/json; charset=utf-8'); // Définit le type de contenu de la réponse
    header('Cache-control: no-store, no-cache, must-revalidate');  // Empêche la mise en cache de la réponse
    header('Pragma: no-cache'); // Empêche la mise en cache de la réponse
    
    if($data !== false){ // Si les données ne sont pas fausses (vérifie si la requête a réussi)
        http_response_code($code); // Définit le code de réponse HTTP
        echo json_encode($data); // Encode les données en JSON et les envoie dans la réponse
    } 
    else{
        http_response_code(400); // Définit le code de réponse HTTP à 400 (Bad Request)
        echo json_encode(['error' => 'Bad Request']);
    }
}
// Récupère le type de requête demandé (ex: stats, marque_ondul, etc.)
$type = $_GET['type'] ?? null;

// Statistiques globales
if ($type === 'stats') {
    // Appelle les fonctions pour chaque statistique
    $enregistrements = dbRequestEnregistrement($db);
    $installateurs = dbRequestNbInstallateurs($db);
    $onduleurs = dbRequestMarqueOnduleurs($db);
    $panneaux = dbRequestMarquesPanneaux($db);
    // Renvoie toutes les stats sous forme de JSON
    sendJsonData([
        'enregistrements' => $enregistrements[0]['COUNT(id)'] ?? 0, // Vérifie si la clé existe avant de l'utiliser
        'installateurs'   => $installateurs['installateur'] ?? 0, 
        'marques'         => $onduleurs['marque_onduleur'] ?? 0,
        'panneaux'        => $panneaux['marque_panneau'] ?? 0
    ], 200);
}

// Listes pour les selects
if ($type === 'marque_ondul') {
    // Liste des marques d'onduleur pour le formulaire
    $stmt = $db->query('SELECT DISTINCT marque_onduleur FROM batiment WHERE marque_onduleur IS NOT NULL AND marque_onduleur != "" ORDER BY marque_onduleur LIMIT 20'); //Limite a 20 résultats
    while ($row = $stmt->fetch()) { // Récupère chaque marque d'onduleur
        echo '<option value="' . htmlspecialchars($row['marque_onduleur']) . '">' . htmlspecialchars($row['marque_onduleur']) . '</option>'; // Affiche chaque marque d'onduleur dans une option HTML
    }
    exit;
}
if ($type === 'marque_pan') {
    // Liste des marques de panneaux pour le formulaire
    $stmt = $db->query('SELECT DISTINCT marque_panneau FROM batiment WHERE marque_panneau IS NOT NULL AND marque_panneau != "" ORDER BY marque_panneau  LIMIT 20'); // Limite a 20 résultats
    while ($row = $stmt->fetch()) { // Récupère chaque marque de panneau
        echo '<option value="' . htmlspecialchars($row['marque_panneau']) . '">' . htmlspecialchars($row['marque_panneau']) . '</option>'; // Affiche chaque marque de panneau dans une option HTML
    }
    exit;
}
if ($type === 'dep') {
    // Liste des départements pour le formulaire
    $stmt = $db->query('SELECT DISTINCT nom_departement,code_departement FROM departement ORDER BY RAND() LIMIT 20'); // Limite a 20 résultats
    while ($row = $stmt->fetch()) { // Récupère chaque département
        echo '<option value="' . htmlspecialchars($row['code_departement']) . '">' . htmlspecialchars($row['nom_departement']) . '</option>'; // Affiche chaque département dans une option HTML
    }
    exit;
}

if ($type === 'annees') {
    // Liste des années d'installation pour le formulaire
    $stmt = $db->query('SELECT DISTINCT annee_install FROM batiment ORDER BY annee_install DESC LIMIT 20'); // Limite a 20 résultats en décroissant pour les plus récents
    while ($row = $stmt->fetch()) { // Récupère chaque année d'installation
        echo '<option value="' . htmlspecialchars($row['annee_install']) . '">' . htmlspecialchars($row['annee_install']) . '</option>'; // Affiche chaque année d'installation dans une option HTML
    }
    exit;
}

//details d'une installation
$id = $_GET['id'] ?? null;
if ($type === 'batiment_details' && !empty($id)) {
    // Récupère toutes les infos détaillées pour une installation donnée
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
    $stmt->execute(['id' => $id]); // Exécute la requête avec l'ID de l'installation
    $result = $stmt->fetch(PDO::FETCH_ASSOC); // Récupère le résultat sous forme de tableau associatif
    sendJsonData($result, 200); // Envoie les données en JSON
    exit;
}

// Donnees pour les graphiques
if ($type === 'installations_par_annee'){
    // Nombre d'installations par année
    $iannee = dbRequestInstallationsParAnnee($db);
    sendJsonData($iannee, 200); // Envoie les données en JSON
}

if ($type === 'installations_par_region'){
    // Nombre d'installations par région
    $regions = dbRequestInstallationsParRegion($db);
    sendJsonData($regions, 200); // Envoie les données en JSON
}

if ($type === 'installations_par_region_et_annee'){
    // Nombre d'installations par région et par année
    $data = dbRequestInstallationsParRegionEtAnnee($db);
    sendJsonData($data, 200); // Envoie les données en JSON
}

//coordonnées pour la carte
if ($type === 'batiments_coords'){
    // Récupère les coordonnées et infos principales pour afficher les marqueurs sur la carte
    $params = [];
    $sql = 'SELECT b.lat, b.lon, b.id, b.locality, b.puissance_crete
        FROM batiment b
        JOIN commune_france c ON c.code_insee = b.code_insee
        JOIN departement d ON d.code_departement = c.code_departement
        WHERE b.lat IS NOT NULL AND b.lon IS NOT NULL';
    // Filtres (année, département)
    if (!empty($_GET['annee'])) {
        $sql .= ' AND b.annee_install = :annee';
        $params['annee'] = $_GET['annee']; // Filtre par année d'installation
    }
    if (!empty($_GET['departement'])) {
        $sql .= ' AND d.code_departement = :departement';
        $params['departement'] = $_GET['departement']; // Filtre par département
    }
    $sql .= ' ORDER BY RAND() LIMIT 20';

    $stmt = $db->prepare($sql); // Prépare la requête SQL avec les filtres
    $stmt->execute($params); // Exécute la requête avec les paramètres
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); // Récupère le résultat sous forme de tableau associatif
    header('Content-Type: application/json; charset=utf-8'); // Définit le type de contenu de la réponse
    echo json_encode($result); // Envoie les données en JSON
    exit;
}

// Affichage des 100 premières installations (admin)
if ($type === 'all_installations') {
    // Pagination
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100; // Nombre d'installations à afficher par page

    // Nombre total d'installations
    $stmt = $db->query('SELECT COUNT(*) FROM batiment'); 
    $total = $stmt->fetchColumn();

    // Récupère les installations avec toutes les infos utiles pour l'admin
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
      ORDER BY b.id LIMIT :limit';
    $stmt = $db->prepare($sql); // Prépare la requête SQL avec les jointures pour récupérer les infos des régions, départements et communes
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT); // Définit la limite pour la pagination
    $stmt->execute(); 
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); // Récupère les résultats sous forme de tableau associatif

    // Renvoie les données et le total pour la pagination
    sendJsonData(['rows' => $rows, 'total' => $total], 200); // Envoie les données en JSON
    exit;
}

// Ajout d'une installation
if ($type === 'add_installation'){
    // Liste des champs attendus
    $fields = ['locality','marque_panneau','panneau_modele','nb_panneaux','marque_onduleur','modele_onduleur','nb_onduleur','annee_install','mois_install','puissance_crete','surface','lat','lon', 'installateur']; // Champs requis pour l'ajout d'une installation
    $values = []; // Tableau pour stocker les valeurs des champs
    foreach ($fields as $f) {
        $values[$f] = $_POST[$f] ?? null; // Récupère la valeur du champ depuis le formulaire, ou null si non défini
        // Si lat/lon sont vides, on les met à null
        if (($f === 'lat' || $f === 'lon') && ($values[$f] === '' || $values[$f] === null)){
            $values[$f] = null; 
        }
    }

    // Vérification modèle panneau <-> marque panneau dans batiment
    $stmt = $db->prepare('SELECT 1 FROM batiment WHERE panneau_modele = ? AND marque_panneau = ? LIMIT 1'); // Prépare la requête pour vérifier si le modèle de panneau est associé à la marque dans la base
    $stmt->execute([$values['panneau_modele'], $values['marque_panneau']]); // Exécute la requête avec le modèle et la marque de panneau
    if (!$stmt->fetch()){ // Si aucun résultat n'est trouvé, cela signifie que le modèle de panneau n'est pas associé à la marque
        sendJsonData(['success'=>false, 'error'=>'Le modèle de panneau n\'est pas associé à cette marque dans la base.'], 400); // Envoie une erreur JSON
        exit;
    }

    // Vérification modèle onduleur <-> marque onduleur dans batiment
    $stmt = $db->prepare('SELECT 1 FROM batiment WHERE modele_onduleur = ? AND marque_onduleur = ? LIMIT 1'); // Prépare la requête pour vérifier si le modèle d'onduleur est associé à la marque dans la base
    $stmt->execute([$values['modele_onduleur'], $values['marque_onduleur']]); // Exécute la requête avec le modèle et la marque d'onduleur
    if (!$stmt->fetch()) {
        sendJsonData(['success'=>false, 'error'=>'Le modèle d\'onduleur n\'est pas associé à cette marque dans la base.'], 400); // Envoie une erreur JSON si le modèle d'onduleur n'est pas associé à la marque
        exit;
    }

    // Cherche le code_insee ET le nom officiel de la commune
    $stmt = $db->prepare('SELECT code_insee, nom_commune FROM commune_france WHERE nom_commune = ? LIMIT 1'); // Prépare la requête pour récupérer le code INSEE et le nom officiel de la commune
    $stmt->execute([$values['locality']]); // Exécute la requête avec le nom de la commune
    $row = $stmt->fetch(PDO::FETCH_ASSOC); // Récupère le résultat sous forme de tableau associatif

    if (!$row || empty($row['code_insee'])) {
        sendJsonData(['success'=>false, 'error'=>'Commune inconnue, code INSEE introuvable pour "'.$values['locality'].'"'], 400); // Envoie une erreur JSON si la commune n'est pas trouvée ou si le code INSEE est vide
        exit;
    }

    // Met à jour les valeurs avec le code INSEE et le nom officiel
    $values['code_insee'] = $row['code_insee'];
    $values['locality'] = $row['nom_commune'];

    // Insère la nouvelle installation dans la base
    $sql = "INSERT INTO batiment (".implode(',',$fields).",code_insee) VALUES (:".implode(',:',$fields).",:code_insee)";
    $stmt = $db->prepare($sql); // Prépare la requête SQL pour insérer une nouvelle installation
    $stmt->execute($values); // Exécute la requête avec les valeurs des champs
    sendJsonData(['success'=>true], 200); // Envoie une réponse JSON indiquant que l'ajout a réussi
    exit;
}

// Modification d'une installation
if ($type === 'update_installation'){
    // Récupère les données envoyées en JSON
    $data = json_decode(file_get_contents('php://input'), true);
    $fields = ['locality','marque_panneau','panneau_modele','nb_panneaux','marque_onduleur','modele_onduleur','nb_onduleur','annee_install','mois_install','puissance_crete','surface','lat','lon','installateur']; // Champs requis pour la modification d'une installation
    $set = []; // Tableau pour stocker les champs à mettre à jour
    $params = []; // Tableau pour stocker les paramètres de la requête préparée
    foreach ($fields as $f) {
        if(isset($data[$f])) {
            $set[] = "$f = :$f"; // Ajoute le champ à mettre à jour dans la requête SQL
            $params[$f] = $data[$f]; // Ajoute la valeur du champ dans les paramètres de la requête préparée
        }
    }
    $params['id'] = $data['id'] ?? null; // Récupère l'ID de l'installation à modifier, ou null si non défini
    $sql = "UPDATE batiment SET ".implode(', ',$set)." WHERE id = :id"; // Prépare la requête SQL pour mettre à jour l'installation
    $stmt = $db->prepare($sql); // Prépare la requête SQL avec les champs à mettre à jour
    $stmt->execute($params); // Exécute la requête avec les paramètres
    sendJsonData(['success'=>true], 200); // Envoie une réponse JSON indiquant que la modification a réussi
    exit;
}

// Suppression d'une installation
if ($type === 'delete_installation' && !empty($_GET['id'])) {
    $stmt = $db->prepare('DELETE FROM batiment WHERE id = :id'); // Prépare la requête SQL pour supprimer une installation
    $stmt->execute(['id' => $_GET['id']]); // Exécute la requête avec l'ID de l'installation à supprimer
    sendJsonData(['success'=>true], 200); // Envoie une réponse JSON indiquant que la suppression a réussi
    exit;
}

// Recherche d'installations
if ($type === 'recherche'){
    // Récupère les filtres du formulaire
    $marqueOndul = $_GET['marque_ondul'] ?? '';
    $marquePan = $_GET['marque_pan'] ?? '';
    $dep = $_GET['dep'] ?? '';
    // Prépare la requête SQL avec les filtres
    $sql = "SELECT  b.id, CONCAT(b.mois_install, '/', b.annee_install) AS date,
                b.nb_panneaux,
                b.surface,
                b.puissance_crete,
                r.nom_region AS localisation
            FROM batiment b
            JOIN commune_france c ON b.code_insee = c.code_insee
            JOIN region r ON c.code_region = r.code_region
            JOIN departement d ON c.code_departement = d.code_departement
            WHERE 1"; // Commence par une condition toujours vraie pour faciliter l'ajout de filtres
    $params = []; // Tableau pour stocker les paramètres de la requête préparée

    if ($marqueOndul !== '') {
        $sql .= " AND b.marque_onduleur = ?"; // Filtre par marque d'onduleur
        $params[] = $marqueOndul; 
    }
    if ($marquePan !== '') {
        $sql .= " AND b.marque_panneau = ?"; // Filtre par marque de panneau
        $params[] = $marquePan;
    }
    if ($dep !== '') {
        $sql .= " AND d.code_departement = ?"; // Filtre par département
        $params[] = $dep;
    }
    $sql .= " LIMIT 100";  // Limite le nombre de résultats à 100 
    $stmt = $db->prepare($sql); // Prépare la requête SQL avec les filtres
    $stmt->execute($params); // Exécute la requête avec les paramètres
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC); // Récupère les résultats sous forme de tableau associatif

    sendJsonData($resultats, 200); // Envoie les résultats en JSON
    exit;
}


?>