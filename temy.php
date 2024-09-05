<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabuľka s témami</title>
    <!-- Pripojenie externých štýlov, ak je to potrebné -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.3.4/axios.min.js"></script>
    <style>
        /* Štýly pre modálne okno */

        main {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #fff;
        }

        /* Form Styles */
        section {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        select, input[type="text"] {
            width: calc(100% - 16px);
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #333;
            color: #fff;
            cursor: pointer;
        }

        th:hover {
            background-color: #555;
        }

        tbody tr:hover {
            background-color: #f2f2f2;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            border-radius: 10px;
        }

        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close-modal:hover,
        .close-modal:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        h1 {
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
    <header>
        <h1>Tabuľka s témami</h1>
    </header>
    <main>
        <section id="filters">
            <!-- Dropdown pre výber pracoviska -->
            <label for="workplaceSelect">Pracovisko:</label>
            <select id="workplaceSelect">
                <option value="642">Ústav automobilovej mechatroniky</option>
                <option value="548">Ústav elektroenergetiky a aplikovanej elektrotechniky</option>
                <option value="549">Ústav elektroniky a fotoniky</option>
                <option value="550">Ústav elektrotechniky</option>
                <option value="816">Ústav informatiky a matematiky</option>
                <option value="817">Ústav jadrového a fyzikálneho inžinierstva</option>
                <option value="818">Ústav multimediálnych informačných a komunikačných technológií</option>
                <option value="356">Ústav robotiky a kybernetiky</option>
            </select>

            <!-- Dropdown pre výber typu práce -->
            <label for="workTypeSelect">Typ práce:</label>
            <select id="workTypeSelect">
                <option value="BP">Bakalárska práca</option>
                <option value="DP">Diplomová práca</option>
                <option value="DizP">Dizertačná práca</option>
            </select>

            <button onclick="renderTable()">Zobraziť dáta</button>
        </section>
        
        <!-- Tabuľka pre zobrazenie dát -->
        <table id="themesTable">
            <thead>
                <tr>
                    <th onclick="sortTable(0)">Názov projektu</th>
                    <th onclick="sortTable(1)">Učiteľ</th>
                    <th onclick="sortTable(2)">Ústav</th>
                    <th onclick="sortTable(3)">Program</th>
                    <th onclick="sortTable(4)">Typ práce</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
        <div id="abstractModal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <p id="modalText"></p>
            </div>
        </div>
    </main>

    <script>
    const abstractModal = document.getElementById('abstractModal');
    const closeModalSpan = document.getElementsByClassName("close-modal")[0];

    closeModalSpan.onclick = function() {
        abstractModal.style.display = "none";
    }

    async function fetchData(idPracovisko, typPrace) {
        try {
            const response = await fetch(`https://node37.webte.fei.stuba.sk/zadanie2/api_methods.php?id_pracoviska=${idPracovisko}&typ_prace=${typPrace}`);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Chyba pri načítaní dát:', error);
            return [];
        }
    }

    async function renderTable() {
        const idPracovisko = document.getElementById('workplaceSelect').value;
        const typPrace = document.getElementById('workTypeSelect').value;

        if (!idPracovisko) {
            console.error('Missing id_pracoviska parameter');
            return;
        }

        const data = await fetchData(idPracovisko, typPrace);

        if (data && Array.isArray(data)) {
            const tableBody = document.getElementById('tableBody');
            tableBody.innerHTML = '';

            data.forEach(theme => {
                const [nazovProjektu, ucitel, ustav, program, typPrace, abstrakt] = theme;
                const escapedAbstrakt = escapeHtml(abstrakt); // Escape HTML characters
                const row = tableBody.insertRow();
                row.innerHTML = `
                    <td class="project-name" data-abstract="${escapedAbstrakt}" onclick="showAbstract(this)">${nazovProjektu}</td>
                    <td>${ucitel}</td>
                    <td>${ustav}</td>
                    <td>${program}</td>
                    <td>${typPrace}</td>
                `;
            });
        } else {
            if (data && data.error) {
                console.error('Error:', data.error);
                alert('Error: ' + data.error);
            } else {
                console.error('Data is undefined or not an array:', data);
            }
        }
    }


    function showAbstract(element) {
        const modalText = document.getElementById('modalText');
        const abstract = element.getAttribute('data-abstract');
        modalText.textContent = abstract;
        abstractModal.style.display = "block";
    }

let ascending = true;

function sortTable(columnIndex) {
    const table = document.getElementById('themesTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].innerText.toLowerCase();
        const bValue = b.cells[columnIndex].innerText.toLowerCase();

        if (ascending) {
            return aValue.localeCompare(bValue);
        } else {
            return bValue.localeCompare(aValue);
        }
    });

    ascending = !ascending;

    rows.forEach(row => tbody.appendChild(row));
}

    window.onload = function() {
        renderTable();
    };

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>

</body>
</html>
