<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestion des documents</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(circle at top left, #f7f2e8, transparent 35%),
                linear-gradient(135deg, #eee8dc, #f8f6f1);
            color: #29251f;
        }

        .container {
            width: min(1100px, 94%);
            margin: 45px auto;
        }

        .card {
            background: rgba(255, 255, 255, 0.96);
            border-radius: 18px;
            box-shadow:
                0 20px 50px rgba(55, 45, 30, 0.12),
                0 2px 8px rgba(55, 45, 30, 0.06);
            overflow: hidden;
            border: 1px solid #e4ded3;
        }

        .header {
            padding: 30px 38px;
            background: linear-gradient(135deg, #2d2923, #4b4338);
            color: white;
        }

        .header h1 {
            margin: 0;
            font-family: Georgia, serif;
            font-size: 30px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .header p {
            margin: 8px 0 0;
            color: #d9d2c6;
            font-size: 14px;
        }

        .content {
            padding: 35px 38px 40px;
        }

        .section-title {
            font-family: Georgia, serif;
            font-size: 20px;
            color: #4b4033;
            margin: 0 0 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            font-size: 13px;
            font-weight: 600;
            color: #62594d;
            margin-bottom: 8px;
        }

        input,
        select {
            width: 100%;
            height: 46px;
            border: 1px solid #d8d0c4;
            border-radius: 9px;
            padding: 0 13px;
            font-size: 14px;
            color: #302c27;
            background: #fffdf9;
            outline: none;
            transition: 0.2s;
        }

        input:focus,
        select:focus {
            border-color: #8d7656;
            box-shadow: 0 0 0 3px rgba(141, 118, 86, 0.12);
        }

        .document-choice {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 30px;
        }

        .document-choice label {
            margin: 0;
            cursor: pointer;
        }

        .document-choice input {
            display: none;
        }

        .document-card {
            border: 1px solid #ddd5c8;
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            background: #faf8f4;
            transition: 0.2s;
        }

        .document-card .icon {
            font-size: 27px;
            margin-bottom: 8px;
        }

        .document-card strong {
            display: block;
            font-family: Georgia, serif;
            font-size: 16px;
            color: #41382e;
        }

        .document-card span {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: #847b6f;
        }

        .document-choice input:checked + .document-card {
            border-color: #806846;
            background: #f2ede4;
            box-shadow: inset 0 0 0 1px #806846;
        }

        .table-wrapper {
            overflow-x: auto;
            border: 1px solid #ddd6ca;
            border-radius: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th {
            background: #40382f;
            color: white;
            padding: 13px 10px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #e8e2d9;
            background: #fffdfa;
        }

        td input {
            height: 40px;
            border-radius: 7px;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 22px;
            gap: 15px;
        }

        button {
            border: none;
            cursor: pointer;
            border-radius: 9px;
            padding: 13px 21px;
            font-size: 14px;
            font-weight: 600;
            transition: 0.2s;
        }

        .add-btn {
            background: #eee8de;
            color: #574a3b;
            border: 1px solid #d9d0c2;
        }

        .add-btn:hover {
            background: #e4dccf;
        }

        .submit-btn {
            background: linear-gradient(135deg, #4a4034, #29251f);
            color: white;
            min-width: 210px;
            box-shadow: 0 7px 18px rgba(45, 39, 31, 0.18);
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 22px rgba(45, 39, 31, 0.25);
        }

        .hidden {
            display: none !important;
        }

        .required {
            color: #9c4a3d;
        }

        @media (max-width: 750px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .document-choice {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 25px 20px;
            }

            .header {
                padding: 25px 20px;
            }

            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .submit-btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="card">

        <div class="header">
            <h1>Gestion des documents</h1>
            <p>Factures, proformas et bons de commande</p>
        </div>

        <div class="content">

            <h2 class="section-title">Type de document</h2>

            <div class="document-choice">

                <label>
                    <input
                        type="radio"
                        name="document_type"
                        value="facture"
                        checked
                        onchange="changeDocumentType()"
                    >

                    <div class="document-card">
                        <div class="icon">🧾</div>
                        <strong>Facture</strong>
                        <span>Document de facturation</span>
                    </div>
                </label>

                <label>
                    <input
                        type="radio"
                        name="document_type"
                        value="proforma"
                        onchange="changeDocumentType()"
                    >

                    <div class="document-card">
                        <div class="icon">📄</div>
                        <strong>Proforma</strong>
                        <span>Devis / facture proforma</span>
                    </div>
                </label>

                <label>
                    <input
                        type="radio"
                        name="document_type"
                        value="bon_commande"
                        onchange="changeDocumentType()"
                    >

                    <div class="document-card">
                        <div class="icon">📋</div>
                        <strong>Bon de commande</strong>
                        <span>Commande de produits</span>
                    </div>
                </label>

            </div>


            <form method="post" action="old_generate_pdf.php">

                <input type="hidden" name="document_type" id="document_type" value="facture">


                <h2 class="section-title">Informations générales</h2>

                <div class="form-grid">

                    <div class="form-group">
                        <label>Date <span class="required">*</span></label>

                        <input
                            type="date"
                            name="date"
                            value="<?= date('Y-m-d') ?>"
                            required
                        >
                    </div>


                    <div
                        class="form-group"
                        id="numero_group"
                    >
                        <label id="numero_label">
                            Numéro facture <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="document_num"
                            id="document_num"
                            placeholder="Ex : 001/2026"
                        >
                    </div>

                </div>


                <h2 class="section-title">Éléments du document</h2>

                <div class="table-wrapper">

                    <table>

                        <thead>
                            <tr id="table_header">
                                <!-- généré par JavaScript -->
                            </tr>
                        </thead>

                        <tbody id="table_body">
                            <!-- généré par JavaScript -->
                        </tbody>

                    </table>

                </div>


                <div class="actions">

                    <button
                        type="button"
                        class="add-btn"
                        onclick="addLine()"
                    >
                        ＋ Ajouter une ligne
                    </button>

                    <button
                        type="submit"
                        class="submit-btn"
                    >
                        Générer le PDF
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>

let currentType = "facture";


function changeDocumentType() {

    currentType = document.querySelector(
        'input[name="document_type"]:checked'
    ).value;

    document.getElementById("document_type").value = currentType;

    const numeroGroup = document.getElementById("numero_group");
    const numeroInput = document.getElementById("document_num");
    const numeroLabel = document.getElementById("numero_label");

    /*
     * FACTURE
     */
    if (currentType === "facture") {

        numeroGroup.classList.remove("hidden");

        numeroLabel.innerHTML =
            'Numéro facture <span class="required">*</span>';

        numeroInput.required = true;

    }

    /*
     * PROFORMA
     */
    else if (currentType === "proforma") {

        numeroGroup.classList.add("hidden");

        numeroInput.required = false;
        numeroInput.value = "";

    }

    /*
     * BON DE COMMANDE
     */
    else if (currentType === "bon_commande") {

        numeroGroup.classList.remove("hidden");

        numeroLabel.innerHTML =
            'Numéro bon de commande <span class="required">*</span>';

        numeroInput.required = true;

    }


    updateTable();
}


function updateTable() {

    const header = document.getElementById("table_header");
    const body = document.getElementById("table_body");

    /*
     * BON DE COMMANDE
     *
     * Seulement :
     * Code
     * Quantité
     * Désignation
     */

    if (currentType === "bon_commande") {

        header.innerHTML = `
            <th width="20%">CODE</th>
            <th width="15%">QUANTITÉ</th>
            <th width="65%">DÉSIGNATION</th>
        `;

        body.innerHTML = "";

        addLine();

        return;
    }


    /*
     * FACTURE / PROFORMA
     *
     * BC
     * Code
     * Quantité
     * Désignation
     * Prix
     * Tissu
     */

    header.innerHTML = `
        <th width="13%">BC</th>
        <th width="13%">CODE</th>
        <th width="10%">QTÉ</th>
        <th width="34%">DÉSIGNATION</th>
        <th width="18%">PRIX UNITAIRE</th>
        <th width="12%">TISSU (M)</th>
    `;

    body.innerHTML = "";

    addLine();
}


function addLine() {

    const body = document.getElementById("table_body");

    const row = document.createElement("tr");

    if (currentType === "bon_commande") {

        row.innerHTML = `
            <td>
                <input
                    name="code[]"
                    required
                    placeholder="Code"
                >
            </td>

            <td>
                <input
                    type="number"
                    name="quantite[]"
                    min="1"
                    required
                    placeholder="Qté"
                >
            </td>

            <td>
                <input
                    name="designation[]"
                    required
                    placeholder="Désignation"
                >
            </td>
        `;

    } else {

        row.innerHTML = `
            <td>
                <input
                    name="bc[]"
                    required
                    placeholder="N° BC"
                >
            </td>

            <td>
                <input
                    name="code[]"
                    required
                    placeholder="Code"
                >
            </td>

            <td>
                <input
                    type="number"
                    name="quantite[]"
                    min="1"
                    required
                    placeholder="Qté"
                >
            </td>

            <td>
                <input
                    name="designation[]"
                    required
                    placeholder="Désignation"
                >
            </td>

            <td>
                <input
                    type="number"
                    name="prix[]"
                    min="0"
                    required
                    placeholder="Prix"
                >
            </td>

            <td>
                <input
                    type="number"
                    name="tissu[]"
                    min="0"
                    step="0.01"
                    value="0"
                    placeholder="M"
                >
            </td>
        `;
    }

    body.appendChild(row);
}


/*
 * Initialisation
 */
changeDocumentType();

</script>

</body>
</html>