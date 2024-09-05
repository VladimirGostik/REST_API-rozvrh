<?php
require_once('config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo $e->getMessage();
}


// Prihlásenie do AIS
$username_ais = "xgostik";
$password_ais = "eBa.2.ciz.fik";
$aisId = "111253";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://is.stuba.sk/system/login.pl");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'lang=sk&login_hidden=1&auth_2fa_type=no&credential_0=' . $username_ais . '&credential_1=' . $password_ais);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/cookie.txt');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);

curl_setopt($ch, CURLOPT_URL, 'https://is.stuba.sk/auth/katalog/rozvrhy_view.pl');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'lang=sk&rozvrh_student=' . $aisId);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/cookie.txt');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$content = curl_exec($ch);

// Vloženie rozvrhu do databázy

    $sql = "INSERT INTO rozvrh (created_date, html) VALUES (?,?)";
    $stmt = $db->prepare($sql);
    $success = $stmt->execute([date("Y-m-d"), $content]);

    $sql = "SELECT html FROM rozvrh";
    $result = $db->query($sql);
    $html = $result->fetchColumn();

    if ($success) {
        echo $html;
    } else {
        echo "Insert failed";
        echo "Insert failed with error: " . $stmt->errorCode();
    }

curl_close($ch);
?>