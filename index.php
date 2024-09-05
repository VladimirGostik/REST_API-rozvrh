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

// Kontrola, či existuje nejaký rozvrh v databáze
$rozvrhExists = false;
$sql = "SELECT COUNT(*) AS count FROM predmet";
$stmt = $db->query($sql);
$count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
if ($count > 0) {
    $rozvrhExists = true;
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.3.4/axios.min.js"></script>
    <title>Denné menu</title>
    <style>
        h1{
            text-align: center;
        }
        
        body {
            background: #a8d3c9;
            background: -webkit-linear-gradient(to right, #95cde7, #aab6ee);
            background: linear-gradient(to right, #a8d3c9, #aab6ee);
        }
        .center {
            text-align: center;
            margin-top: 50px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark" aria-label="navbar">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample10" aria-controls="navbarsExample10" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample10">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Domov</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rozvrh.php">Rozvrh</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="temy.php">Témy</a>
                </li>
               
            </ul>
            
        </div>
    </div>
</nav>
<h1>Rozvrh...</h1>

<div class="center">
    <button onclick="stiahnutRozvrh()">Aktualizovať rozvrh</button> <br>
    <button onclick="vymazatRozvrh()">Vymazať rozvrh</button>

    <?php if($rozvrhExists): ?>
        <p>Rozvrh je uložený v databáze.</p>
    <?php else: ?>
        <p>Rozvrh nie je uložený v databáze.</p>
    <?php endif; ?>

</div>
</body>

<script>
function stiahnutRozvrh() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "stiahnut_rozvrh.php", true);
    xhr.onload = function() {
        if (xhr.status === 200) {

            var data = parseHtml(xhr.responseText);
            // (tu vložte kód na manipuláciu s DOM a zobrazenie dát)
            //console.log(data);
            location.reload();
        } else {
            console.error("Chyba pri sťahovaní rozvrhu: " + xhr.status);
        }
    };
    xhr.send();
}


function vymazatRozvrh() {
  // Potvrdenie pred vymazaním
    // Odoslanie požiadavky na server
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "vymazat_rozvrh.php", true);
    xhr.onload = function() {
      if (xhr.status === 200) {
        // Zobrazte správu o úspešnom vymazaní
        console.log("vymazany rozvrh z databazy...");
        location.reload();
      } else {
        // Zobrazte správu o chybe
        console.error("Chyba pri sťahovaní rozvrhu: " + xhr.status);
      }
    };
    xhr.send();
}

function parseHtml(html) {
    // Inicializujeme prázdný zoznam pre ukladanie dát
    var data = [];
    // Vytvoríme dokument z HTML reťazca
    var parser = new DOMParser();
    var doc = parser.parseFromString(html, 'text/html');

    // Nájdeme všetky riadky tabuľky
    var rows = doc.querySelectorAll('table tr');
    var currentDay = null;

    // Prechádzame každý riadok tabuľky
    rows.forEach(function(row) {
        // Nájdeme bunku s triedou "zahlavi" a atribútom align="left"
        var cell = row.querySelector('td.zahlavi[align="left"]');
        var predmetCellsCvic = row.querySelectorAll('td.rozvrh-cvic');
        var predmetCellsPred = row.querySelectorAll('td.rozvrh-pred');

        // Ak sme našli bunku pre den
        if (cell) {
            // Získame text z bunky (den)
            var dayText = cell.textContent.trim();
            // Prevedieme skratku dena na úplný názov (napr. Po na Pondelok)
            switch(dayText) {
                case "Po":
                    currentDay = "Pondelok";
                    break;
                case "Ut":
                    currentDay = "Utorok";
                    break;
                case "St":
                    currentDay = "Streda";
                    break;
                case "Št":
                    currentDay = "Štvrtok";
                    break;
                case "Pi":
                    currentDay = "Piatok";
                    break;
                default:
                    break;
            }
        } 
        
        // Ak sme našli bunky s predmetmi typu cvičenie
        if (predmetCellsCvic.length > 0) {
            predmetCellsCvic.forEach(function(predmetCell) {
                var miestnostElement = predmetCell.querySelector('a');
                if (miestnostElement) {
                    var miestnost = miestnostElement.textContent.trim();
                    var nazovElement = predmetCell.querySelector('a:nth-of-type(2)');
                    var nazov = nazovElement ? nazovElement.textContent.trim() : '';
                    // Pridáme informácie do zoznamu dát
                    data.push({
                        'den': currentDay,
                        'miestnost': miestnost,
                        'nazov': nazov,
                        'typ': 'cvičenie',
                    });
                }
            });
        } 
        
        // Ak sme našli bunky s predmetmi typu prednáška
        if (predmetCellsPred.length > 0) {
            predmetCellsPred.forEach(function(predmetCell) {
                var miestnostElement = predmetCell.querySelector('a');
                if (miestnostElement) {
                    var miestnost = miestnostElement.textContent.trim();
                    var nazovElement = predmetCell.querySelector('a:nth-of-type(2)');
                    var nazov = nazovElement ? nazovElement.textContent.trim() : '';
                    // Pridáme informácie do zoznamu dát
                    data.push({
                        'den': currentDay,
                        'miestnost': miestnost,
                        'nazov': nazov,
                        'typ': 'prednáška',
                    });
                }
            });
        } 
    }); 
    vlozitDoDatabazy(data);            // Zobrazte stiahnuté údaje v tabuľke
    return data;
}

function vlozitDoDatabazy(data) {
    data.forEach(item => {
        postPredmet(item.den, item.typ, item.nazov, item.miestnost);
    });
}

async function postPredmet(day, typ, nazov, miestnost) {
    const formData = new FormData();
    formData.append('den', day);
    formData.append('typ', typ);
    formData.append('nazov', nazov);
    formData.append('miestnost', miestnost);

    try {
        const response = await axios.post('api.php', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        console.log(response); // Log the response from the server
    } catch (error) {
        console.error('Error:', error);
    }
}


</script>


</html>

