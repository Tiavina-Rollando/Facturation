<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saisie Facturation & Documents</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --accent-gold: #d4af37;
            --accent-gold-hover: #f3e5ab;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --input-bg: #0f172a;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .container {
            width: 100%;
            max-width: 1100px;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #b8860b, var(--accent-gold), #b8860b);
        }

        header {
            text-align: center;
            margin-bottom: 35px;
        }

        header h1 {
            font-family: 'Cinzel', serif;
            font-size: 28px;
            letter-spacing: 2px;
            color: var(--accent-gold);
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        header p {
            color: var(--text-muted);
            font-size: 14px;
        }

        .type-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .type-option {
            position: relative;
        }

        .type-option input[type="radio"] {
            display: none;
        }

        .type-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 16px;
            background: var(--input-bg);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 14px;
            color: var(--text-muted);
        }

        .type-option input[type="radio"]:checked + label {
            border-color: var(--accent-gold);
            color: var(--accent-gold);
            background: rgba(212, 175, 55, 0.05);
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.15);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
        }

        input {
            width: 100%;
            padding: 12px 16px;
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-main);
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        input:focus {
            border-color: var(--accent-gold);
        }

        .table-wrapper {
            overflow-x: auto;
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--input-bg);
        }

        th {
            background: #111827;
            padding: 14px 12px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--accent-gold);
            border-bottom: 1px solid var(--border-color);
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border-color);
        }

        td input {
            padding: 8px 10px;
        }

        .btn-add {
            background: transparent;
            color: var(--accent-gold);
            border: 1px dashed var(--accent-gold);
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 30px;
        }

        .btn-add:hover {
            background: rgba(212, 175, 55, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #d4af37 0%, #aa820a 100%);
            color: #000;
            font-weight: 700;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(212, 175, 55, 0.3);
        }

        .hide {
            display: none !important;
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Édition de Document</h1>
        <p>Générez vos factures, proformas et bons de commande</p>
    </header>

    <form method="post" action="generate_pdf.php">
        <!-- Sélection du type de document -->
        <div class="type-selector">
            <div class="type-option">
                <input type="radio" id="type_facture" name="doc_type" value="facture" checked onchange="updateFormView()">
                <label for="type_facture">📄 Facture</label>
            </div>
            <div class="type-option">
                <input type="radio" id="type_proforma" name="doc_type" value="proforma" onchange="updateFormView()">
                <label for="type_proforma">📋 Proforma</label>
            </div>
            <div class="type-option">
                <input type="radio" id="type_bc" name="doc_type" value="bon_commande" onchange="updateFormView()">
                <label for="type_bc">📦 Bon de Commande</label>
            </div>
        </div>

        <!-- Informations Générales -->
        <div class="form-grid">
            <div class="form-group" id="group_date">
                <label>Date du document</label>
                <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group" id="group_num">
                <label id="label_num">Numéro de document</label>
                <input type="text" name="facture_num" id="input_num" placeholder="EX: 2026-001" required>
            </div>
        </div>

        <!-- Tableau des Lignes -->
        <div class="table-wrapper">
            <table id="items_table">
                <thead>
                    <tr>
                        <th class="col-bc">BC</th>
                        <th>Code</th>
                        <th>Quantité</th>
                        <th>Désignation</th>
                        <th class="col-prix">Prix Unitaire</th>
                        <th class="col-remise">Remise (%)</th>
                        <th class="col-tissu">Tissu (m)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="col-bc"><input name="bc[]" class="input-bc"></td>
                        <td><input name="code[]" required placeholder="REF-01"></td>
                        <td><input type="number" name="quantite[]" required value="1" min="1"></td>
                        <td><input name="designation[]" required placeholder="Désignation"></td>
                        <td class="col-prix"><input type="number" name="prix[]" class="input-prix" required placeholder="0"></td>
                        <td class="col-remise"><input type="number" name="remise[]" class="input-remise" value="0" min="0" max="100" step="0.1" placeholder="0%"></td>
                        <td class="col-tissu"><input type="number" name="tissu[]" class="input-tissu" value="0" step="0.1"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <button type="button" class="btn-add" onclick="addLine()">➕ Ajouter une ligne</button>

        <button type="submit" class="btn-submit">Générer le PDF</button>
    </form>
</div>

<script>
function updateFormView() {
    const selectedType = document.querySelector('input[name="doc_type"]:checked').value;
    
    const groupNum = document.getElementById('group_num');
    const inputNum = document.getElementById('input_num');
    const labelNum = document.getElementById('label_num');
    
    const colBc = document.querySelectorAll('.col-bc');
    const colPrix = document.querySelectorAll('.col-prix');
    const colRemise = document.querySelectorAll('.col-remise');
    const colTissu = document.querySelectorAll('.col-tissu');
    
    const inputBc = document.querySelectorAll('.input-bc');
    const inputPrix = document.querySelectorAll('.input-prix');

    if (selectedType === 'proforma') {
        groupNum.classList.add('hide');
        inputNum.removeAttribute('required');
        
        colBc.forEach(el => el.classList.remove('hide'));
        colPrix.forEach(el => el.classList.remove('hide'));
        colRemise.forEach(el => el.classList.add('hide'));
        colTissu.forEach(el => el.classList.remove('hide'));
        
        inputBc.forEach(el => el.setAttribute('required', 'required'));
        inputPrix.forEach(el => el.setAttribute('required', 'required'));

    } else if (selectedType === 'bon_commande') {
        groupNum.classList.remove('hide');
        labelNum.innerText = "Numéro du Bon de Commande";
        inputNum.setAttribute('required', 'required');

        colBc.forEach(el => el.classList.add('hide'));
        colPrix.forEach(el => el.classList.remove('hide'));
        colRemise.forEach(el => el.classList.remove('hide'));
        colTissu.forEach(el => el.classList.add('hide'));
        
        inputBc.forEach(el => el.removeAttribute('required'));
        inputPrix.forEach(el => el.setAttribute('required', 'required'));

    } else { // Facture
        groupNum.classList.remove('hide');
        labelNum.innerText = "Numéro de Facture";
        inputNum.setAttribute('required', 'required');

        colBc.forEach(el => el.classList.remove('hide'));
        colPrix.forEach(el => el.classList.remove('hide'));
        colRemise.forEach(el => el.classList.add('hide'));
        colTissu.forEach(el => el.classList.remove('hide'));
        
        inputBc.forEach(el => el.setAttribute('required', 'required'));
        inputPrix.forEach(el => el.setAttribute('required', 'required'));
    }
}

function addLine() {
    const tbody = document.querySelector("#items_table tbody");
    const row = document.createElement("tr");

    row.innerHTML = `
        <td class="col-bc"><input name="bc[]" class="input-bc"></td>
        <td><input name="code[]" required placeholder="REF-01"></td>
        <td><input type="number" name="quantite[]" required value="1" min="1"></td>
        <td><input name="designation[]" required placeholder="Désignation"></td>
        <td class="col-prix"><input type="number" name="prix[]" class="input-prix" required placeholder="0"></td>
        <td class="col-remise"><input type="number" name="remise[]" class="input-remise" value="0" min="0" max="100" step="0.1" placeholder="0%"></td>
        <td class="col-tissu"><input type="number" name="tissu[]" class="input-tissu" value="0" step="0.1"></td>
    `;

    tbody.appendChild(row);
    updateFormView();
}

document.addEventListener("DOMContentLoaded", updateFormView);
</script>

</body>
</html>