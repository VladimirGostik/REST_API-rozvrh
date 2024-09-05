<?php

require_once('config.php');

// Vytvorenie pripojenia k databáze
$db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vymazanie rozvrhu
$sql = "DELETE FROM rozvrh";
$stmt = $db->prepare($sql);
$stmt->execute();

$sql = "DELETE FROM predmet";
$stmt = $db->prepare($sql);
$stmt->execute();


// Presmerovanie na index
header("Location: index.php");

?>
