<?php
require_once('config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo $e->getMessage();
}

function getRozvrh($db) {
    $sql = "SELECT * FROM predmet";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $data;
}
  
function addRozvrh($db, $data) {
    if (!isset($data['den'], $data['typ'], $data['nazov'], $data['miestnost'])) {
        // Ak chýbajú niektoré údaje, vrátiť chybový stav
        return ['error' => 'Missing data:'];
    }

    $sql = "INSERT INTO predmet (den, typ, nazov, miestnost) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    // Nastavenie parametrov dotazu
    $stmt->execute([$data['den'], $data['typ'], $data['nazov'], $data['miestnost']]);

    return ['success' => true];
}



function deleteRozvrh($db, $data) {
    $sql = "DELETE FROM predmet WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$data['id']]);
}

function updateRozvrh($db, $data) {
    $sql = "UPDATE predmet SET den = ?, typ = ?, nazov = ?, miestnost = ? WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$data['den'], $data['typ'], $data['nazov'], $data['miestnost'], $data['id']]);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
            $data = getRozvrh($db);
            header('Content-Type: application/json');
            echo json_encode($data);
        break;

    case 'POST':
        addRozvrh($db, $_POST);
        break;
        
    case 'DELETE':
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);
        deleteRozvrh($db, $data); 
        echo json_encode(['success' => true]);
        break;

    case 'PUT':
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);
        updateRozvrh($db, $data);
        echo json_encode(['success' => true]);
        break;
}
?>