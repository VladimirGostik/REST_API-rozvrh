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

?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rozvrh</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.3.4/axios.min.js"></script>
    <link rel="stylesheet" href="/zadanie2/css/style.css"> <!-- Tu pridajte cestu k vášmu CSS súboru -->
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
<h1>Rozvrh</h1>
<div class="container">
    <div class="center">
        <button onclick="stiahnutRozvrh()">Stiahnuť rozvrh</button>
    </div>
    <div id="formularPridat" class="center formular hidden">
        <h2>Pridať nový predmet</h2>
        <form>
            <div class="form-group">
                <label for="den">Deň:</label>
                <select id="den" name="den" class="form-control">
                    <option value="Pondelok">Pondelok</option>
                    <option value="Utorok">Utorok</option>
                    <option value="Streda">Streda</option>
                    <option value="Štvrtok">Štvrtok</option>
                    <option value="Piatok">Piatok</option>
                </select>
            </div>
            <div class="form-group">
                <label for="typ">Typ:</label>
                <select id="typ" name="typ" class="form-control">
                    <option value="prednáška">Prednáška</option>
                    <option value="cvičenie">Cvičenie</option>
                    <option value="konzultácia">Konzultácia</option>
                    <option value="telesna_vychova">Telesná výchova</option>
                </select>
            </div>
            <div class="form-group">
                <label for="nazov">Názov:</label>
                <input type="text" id="nazov" name="nazov" class="form-control">
            </div>
            <div class="form-group">
                <label for="miestnost">Miestnosť:</label>
                <input type="text" id="miestnost" name="miestnost" class="form-control">
            </div>
            <button type="button" onclick="pridatRozvrh()" class="btn btn-primary">Pridať</button>
        </form>
    </div>
</div>

<div class="container">
    <div id="formularVymazat" class="center formular hidden">
        <h2>Vymazať predmet</h2>
        <form>
            <div class="form-group">
                <label for="predmetVymazat">Vybrať predmet:</label>
                <select id="predmetVymazat" name="predmetVymazat" class="form-control">
                    <!-- Obsah dropdown listu sa vygeneruje cez JavaScript -->
                </select>
            </div>
            <button type="button" onclick="vymazatRozvrh()" class="btn btn-danger">Vymazať</button>
        </form>
    </div>
</div>

<div class="container">
    <div id="formularUpravit" class="center formular hidden">
        <h2>Upraviť predmet</h2>
        <form>
            <div class="form-group">
                <label for="predmetUpravit">Vybrať predmet:</label>
                <select id="predmetUpravit" name="predmetUpravit" onchange="nacitatPredmet(this.value)" class="form-control">
                    <!-- Obsah dropdown listu sa vygeneruje cez JavaScript -->
                </select>
            </div>
            <div class="form-group">
                <label for="novyDen">Nový deň:</label>
                <select id="novyDen" name="novyDen" class="form-control">
                    <!-- Dropdown list pre nový deň -->
                </select>
            </div>
            <div class="form-group">
                <label for="novyTyp">Nový typ:</label>
                <select id="novyTyp" name="novyTyp" class="form-control">
                    <!-- Dropdown list pre nový typ -->
                </select>
            </div>
            <div class="form-group">
                <label for="novyNazov">Nový názov:</label>
                <input type="text" id="novyNazov" name="novyNazov" class="form-control">
            </div>
            <div class="form-group">
                <label for="novaMiestnost">Nová miestnosť:</label>
                <input type="text" id="novaMiestnost" name="novaMiestnost" class="form-control">
            </div>
            <button type="button" onclick="upravitRozvrh()" class="btn btn-success">Upraviť</button>
        </form>
    </div>
</div>

<div class="container">
    <div id="rozvrh" class="center"></div>
</div>

<div class="container">
    <div class="legend center">
        <div class="legend-item">
            <span class="cvičenie"></span> Cvičenie
        </div>
        <div class="legend-item">
            <span class="prednáška"></span> Prednáška
        </div>
        <div class="legend-item">
            <span class="telesna_vychova"></span> Telesná výchova
        </div>
        <div class="legend-item">
            <span class="konzultácia"></span> Konzultácia
        </div>
    </div>
</div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.24.0/axios.min.js"></script>
    <script>
    stiahnutRozvrh();
    let rozvrhData = []; // Globálna premenná pre uloženie načítaných údajov

    async function stiahnutRozvrh() {
        try {
            const response = await axios.get('api.php');
            rozvrhData = response.data; // Uložiť načítané údaje do globálnej premennej
            zobrazRozvrh(rozvrhData);
            vygenerujDropdown();
        } catch (error) {
            console.error('Chyba pri stiahnutí rozvrhu:', error);
        }
    }

    async function pridatRozvrh() {
        const den = document.getElementById('den').value;
        const typ = document.getElementById('typ').value;
        const nazov = document.getElementById('nazov').value;
        const miestnost = document.getElementById('miestnost').value;

        try {
            const formData = new FormData();
            formData.append('den', den);
            formData.append('typ', typ);
            formData.append('nazov', nazov);
            formData.append('miestnost', miestnost);

            const response = await axios.post('api.php', formData);
            console.log(response.data);
            window.location.reload();
        } catch (error) {
            console.error('Chyba pri pridaní rozvrhu:', error);
        }
    }


        async function vymazatRozvrh(id) {
            try {
                const dropdown = document.getElementById('predmetVymazat');
                const idToDelete = dropdown.value; // Uložíme hodnotu z dropdown menu do premennej idToDelete
                //console.log(idToDelete); // Zobraziť ID na konzole pre overenie
                
                const response = await axios.delete('api.php', { data: { id: idToDelete } }); // Posielať ID ako dáta
                console.log(response.data);
                window.location.reload();
            } catch (error) {
                console.error('Chyba pri vymazaní rozvrhu:', error);
            }
        }

        async function upravitRozvrh() {
                const predmetId = document.getElementById('predmetUpravit').value;
                const den = document.getElementById('novyDen').value;
                const typ = document.getElementById('novyTyp').value;
                const nazov = document.getElementById('novyNazov').value;
                const miestnost = document.getElementById('novaMiestnost').value;
                try {
                    const data = {
                        id: predmetId,
                        den: den,
                        typ: typ,
                        nazov: nazov,
                        miestnost: miestnost
                    };
                    const response = await axios.put('api.php', data);
                    console.log(response.data); 
                    window.location.reload(); // Reload the page
                } catch (error) {
                    console.error('Chyba pri úprave rozvrhu:', error);
                }
            }


        function zobrazRozvrh(rozvrh) {
            const dni = ['Pondelok', 'Utorok', 'Streda', 'Štvrtok', 'Piatok'];
            const rozvrhElement = document.getElementById('rozvrh');
            rozvrhElement.innerHTML = '';
            
            dni.forEach(den => {
                const denElement = document.createElement('div');
                denElement.classList.add('den');
                denElement.innerHTML = `<h2>${den}</h2>`;
                
                const predmetyVDni = rozvrh.filter(predmet => predmet.den === den);
                predmetyVDni.forEach(predmet => {
                    const predmetElement = document.createElement('div');
                    const className = predmet.typ.toLowerCase().replace(/\s/g, '-'); // Replace space with hyphen

                    predmetElement.classList.add('predmet');
                    predmetElement.classList.add(className);
                    predmetElement.textContent = `${predmet.nazov} - ${predmet.miestnost}`;
                    denElement.appendChild(predmetElement);
                });

                rozvrhElement.appendChild(denElement);
            });
        }

        async function vygenerujDropdown() {
            var dropdownVymazat = document.getElementById('predmetVymazat');
            var dropdownUpravit = document.getElementById('predmetUpravit');
            var dropdownDen = document.getElementById('novyDen');
            var dropdownTyp = document.getElementById('novyTyp');
            dropdownVymazat.innerHTML = ''; // Vyčistiť obsah dropdown listu
            dropdownUpravit.innerHTML = ''; // Vyčistiť obsah dropdown listu
            dropdownDen.innerHTML = ''; // Vyčistiť obsah dropdown listu
            dropdownTyp.innerHTML = ''; // Vyčistiť obsah dropdown listu

            // Definovať všetky možné hodnoty pre deň a typ
            const mozneDni = ['Pondelok', 'Utorok', 'Streda', 'Štvrtok', 'Piatok'];
            const mozneTypy = ['prednáška', 'cvičenie', 'konzultácia', 'telesna_vychova'];

            // Pridať všetky možné hodnoty do roletových menu
            mozneDni.forEach(den => {
                var optionDen = document.createElement('option');
                optionDen.value = den;
                optionDen.text = den;
                dropdownDen.appendChild(optionDen);
            });

            mozneTypy.forEach(typ => {
                var optionTyp = document.createElement('option');
                optionTyp.value = typ;
                optionTyp.text = typ.charAt(0).toUpperCase() + typ.slice(1); // Upraviť prvý znak na veľký
                dropdownTyp.appendChild(optionTyp);
            });

            try {
                const response = await axios.get('api.php');
                const predmety = response.data; // Získať predmety zo servera

                predmety.forEach(function(predmet) {
                    var optionVymazat = document.createElement('option');
                    var optionUpravit = document.createElement('option');
                    optionVymazat.value = predmet.id;
                    optionUpravit.value = predmet.id;
                    optionVymazat.text = predmet.nazov;
                    optionUpravit.text = predmet.nazov;
                    dropdownVymazat.appendChild(optionVymazat);
                    dropdownUpravit.appendChild(optionUpravit);
                });
            } catch (error) {
                console.error('Chyba pri načítaní predmetov:', error);
            }
        }

        function nacitatPredmet(predmetId) {
            try {
                // Nájsť predmet podľa ID v globálnej premennej rozvrhData
                const predmet = rozvrhData.find(item => item.id === parseInt(predmetId));
                
                // Ak sa predmet našiel, nastaviť hodnoty polí formulára
                if (predmet) {
                    document.getElementById('novyDen').value = predmet.den;
                    document.getElementById('novyTyp').value = predmet.typ;
                    document.getElementById('novyNazov').value = predmet.nazov;
                    document.getElementById('novaMiestnost').value = predmet.miestnost;
                } else {
                    console.error('Predmet s daným ID nebol nájdený.');
                }
            } catch (error) {
                console.error('Chyba pri načítaní predmetu:', error);
            }
        }


    </script>
</body>
</html>
