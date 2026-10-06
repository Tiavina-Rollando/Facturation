```php
<?php

require 'vendor/autoload.php';

$config = require 'config.php';

use Dompdf\Dompdf;
use NumberToWords\NumberToWords;


/*
|--------------------------------------------------------------------------
| Vérification du formulaire
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Accès non autorisé.");
}


/*
|--------------------------------------------------------------------------
| Type de document
|--------------------------------------------------------------------------
*/

$documentType = $_POST['document_type'] ?? 'facture';

$typesAutorises = [
    'facture',
    'proforma',
    'bon_commande'
];

if (!in_array($documentType, $typesAutorises, true)) {
    die("Type de document invalide.");
}


/*
|--------------------------------------------------------------------------
| Informations générales
|--------------------------------------------------------------------------
*/

$date = $_POST['date'] ?? '';

if (empty($date)) {
    die("La date est obligatoire.");
}

$dateObject = DateTime::createFromFormat('Y-m-d', $date);

if (!$dateObject) {
    die("Date invalide.");
}

$dateFormatee = $dateObject->format('d/m/Y');


/*
|--------------------------------------------------------------------------
| Numéro
|--------------------------------------------------------------------------
|
| Facture       -> obligatoire
| Proforma      -> aucun numéro
| Bon commande  -> obligatoire
|
*/

$documentNum = trim($_POST['document_num'] ?? '');

if (
    ($documentType === 'facture' || $documentType === 'bon_commande')
    && empty($documentNum)
) {
    die("Le numéro du document est obligatoire.");
}


/*
|--------------------------------------------------------------------------
| Données des lignes
|--------------------------------------------------------------------------
*/

$codes = $_POST['code'] ?? [];
$quantites = $_POST['quantite'] ?? [];
$designations = $_POST['designation'] ?? [];

$bcs = $_POST['bc'] ?? [];
$prix = $_POST['prix'] ?? [];
$tissus = $_POST['tissu'] ?? [];


$lignes = [];


/*
|--------------------------------------------------------------------------
| BON DE COMMANDE
|--------------------------------------------------------------------------
|
| Uniquement :
| Code
| Quantité
| Désignation
|
*/

if ($documentType === 'bon_commande') {

    for ($i = 0; $i < count($codes); $i++) {

        if (
            empty($codes[$i]) ||
            empty($quantites[$i]) ||
            empty($designations[$i])
        ) {
            continue;
        }

        $lignes[] = [
            'code' => htmlspecialchars($codes[$i]),
            'quantite' => (int)$quantites[$i],
            'designation' => htmlspecialchars($designations[$i])
        ];
    }
}


/*
|--------------------------------------------------------------------------
| FACTURE / PROFORMA
|--------------------------------------------------------------------------
|
| BC
| Code
| Quantité
| Désignation
| Prix
| Tissu
|
*/

else {

    for ($i = 0; $i < count($codes); $i++) {

        if (
            empty($codes[$i]) ||
            empty($quantites[$i]) ||
            empty($designations[$i])
        ) {
            continue;
        }

        $bc = $bcs[$i] ?? '';
        $prixUnitaire = (float)($prix[$i] ?? 0);
        $tissuMetres = (float)($tissus[$i] ?? 0);
        $quantite = (int)$quantites[$i];

        $montant = $quantite * $prixUnitaire;

        $lignes[] = [
            'bc' => htmlspecialchars($bc),
            'code' => htmlspecialchars($codes[$i]),
            'quantite' => $quantite,
            'designation' => htmlspecialchars($designations[$i]),
            'prix' => $prixUnitaire,
            'tissu' => $tissuMetres,
            'montant' => $montant
        ];
    }
}


/*
|--------------------------------------------------------------------------
| Calculs
|--------------------------------------------------------------------------
|
| Seulement pour Facture et Proforma
|--------------------------------------------------------------------------
*/

$totalHT = 0;
$totalTissu = 0;
$tva = 0;
$ttc = 0;
$totalFinal = 0;
$avance = 0;
$reste = 0;
$resteEnLettres = '';


if (
    $documentType === 'facture'
    || $documentType === 'proforma'
) {

    foreach ($lignes as $ligne) {

        $totalHT += $ligne['montant'];

        $totalTissu +=
            $ligne['tissu']
            * $ligne['quantite']
            * $config['prix_tissu_m'];
    }


    $tva = $totalHT * $config['tva'];

    $ttc = $totalHT + $tva;

    $totalFinal = $ttc - $totalTissu;

    $avance = $totalFinal * 0.40;

    $reste = $totalFinal - $avance;


    /*
     * Conversion du reste en lettres
     */

    $numberToWords = new NumberToWords();

    $transformer =
        $numberToWords->getNumberTransformer('fr');

    $resteEnLettres =
        $transformer->toWords((int)$reste);
}


/*
|--------------------------------------------------------------------------
| Préparation des informations du document
|--------------------------------------------------------------------------
*/

if ($documentType === 'facture') {

    $titre = "FACTURE";

} elseif ($documentType === 'proforma') {

    $titre = "PROFORMA";

} else {

    $titre = "BON DE COMMANDE";
}


/*
|--------------------------------------------------------------------------
| Génération HTML
|--------------------------------------------------------------------------
*/

ob_start();

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<style>

@page {
    margin: 35px 40px;
}

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
    color: #29251f;
}


/*
|--------------------------------------------------------------------------
| En-tête
|--------------------------------------------------------------------------
*/

.header {
    width: 100%;
    margin-bottom: 25px;
}

.header td {
    vertical-align: top;
}

.company {
    font-size: 12px;
    line-height: 1.55;
}

.company-name {
    font-size: 16px;
    font-weight: bold;
    color: #3d352c;
    margin-bottom: 5px;
}

.client {
    text-align: right;
    line-height: 1.55;
}

.client-title {
    font-weight: bold;
    font-size: 12px;
}


/*
|--------------------------------------------------------------------------
| Titre
|--------------------------------------------------------------------------
*/

.title-box {
    text-align: center;
    margin: 20px 0 25px;
}

.title {
    font-family: DejaVu Serif, serif;
    font-size: 20px;
    font-weight: bold;
    letter-spacing: 1px;
    color: #302a23;
}

.document-number {
    font-size: 13px;
    margin-top: 7px;
    font-weight: bold;
}


/*
|--------------------------------------------------------------------------
| Table
|--------------------------------------------------------------------------
*/

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    background: #40382f;
    color: white;
    border: 1px solid #40382f;
    padding: 8px 6px;
    text-align: center;
    font-size: 10px;
}

.table td {
    border: 1px solid #cfc7bb;
    padding: 7px 6px;
    vertical-align: middle;
}

.center {
    text-align: center;
}

.right {
    text-align: right;
}

.bold {
    font-weight: bold;
}


/*
|--------------------------------------------------------------------------
| Totaux
|--------------------------------------------------------------------------
*/

.totals-container {
    width: 100%;
    margin-top: 15px;
}

.totals {
    width: 40%;
    margin-left: 60%;
    border-collapse: collapse;
}

.totals td {
    border: 1px solid #cfc7bb;
    padding: 7px;
}

.totals .label {
    font-weight: bold;
}


/*
|--------------------------------------------------------------------------
| Phrase finale
|--------------------------------------------------------------------------
*/

.final-amount {
    text-align: center;
    margin-top: 25px;
    font-size: 11px;
}


/*
|--------------------------------------------------------------------------
| Bon de commande
|--------------------------------------------------------------------------
*/

.command-info {
    margin-bottom: 20px;
    font-size: 11px;
}

</style>

</head>

<body>


<!--
====================================================================
    EN-TÊTE
====================================================================
-->

<table class="header">

<tr>

<td width="55%" class="company">

    <div class="company-name">
        <?= htmlspecialchars($config['fournisseur']['nom']) ?>
    </div>

    Tél : <?= htmlspecialchars($config['fournisseur']['tel']) ?><br>

    Nif : <?= htmlspecialchars($config['fournisseur']['nif']) ?><br>

    Stat : <?= htmlspecialchars($config['fournisseur']['stat']) ?><br>

    <?= htmlspecialchars($config['fournisseur']['adresse']) ?>

</td>


<td width="45%" class="client">

    Antananarivo, le <?= $dateFormatee ?><br><br>

    <span class="client-title">
        <?= htmlspecialchars($config['client']['nom']) ?>
    </span><br>

    <?= htmlspecialchars($config['client']['adresse']) ?><br>

    Nif : <?= htmlspecialchars($config['client']['nif']) ?><br>

    Stat : <?= htmlspecialchars($config['client']['stat']) ?>

</td>

</tr>

</table>


<!--
====================================================================
    TITRE
====================================================================
-->

<div class="title-box">

    <div class="title">
        <?= $titre ?>
    </div>


    <?php if (
        $documentType === 'facture'
        || $documentType === 'bon_commande'
    ): ?>

        <div class="document-number">
            N° <?= htmlspecialchars($documentNum) ?>
        </div>

    <?php endif; ?>

</div>


<!--
====================================================================
    BON DE COMMANDE
====================================================================
-->

<?php if ($documentType === 'bon_commande'): ?>


<table class="table">

<thead>

<tr>

    <th width="20%">
        CODE
    </th>

    <th width="15%">
        QUANTITÉ
    </th>

    <th width="65%">
        DÉSIGNATION
    </th>

</tr>

</thead>


<tbody>

<?php foreach ($lignes as $ligne): ?>

<tr>

    <td class="center">
        <?= $ligne['code'] ?>
    </td>

    <td class="center">
        <?= str_pad(
            $ligne['quantite'],
            2,
            "0",
            STR_PAD_LEFT
        ) ?>
    </td>

    <td>
        <?= str_replace(
            'Couleur',
            '<br>Couleur',
            $ligne['designation']
        ) ?>
    </td>

</tr>

<?php endforeach; ?>

</tbody>

</table>


<!--
    FIN BON DE COMMANDE
    Aucun montant / TVA / tissu / avance
-->


<?php else: ?>


<!--
====================================================================
    FACTURE / PROFORMA
====================================================================
-->

<table class="table">

<thead>

<tr>

    <th width="9%">
        CODE
    </th>

    <th width="10%">
        QUANTITÉ
    </th>

    <th width="39%">
        DÉSIGNATION
    </th>

    <th width="21%">
        PRIX UNITAIRE<br>
        ARIARY
    </th>

    <th width="21%">
        MONTANT<br>
        ARIARY
    </th>

</tr>

</thead>


<tbody>

<?php foreach ($lignes as $ligne): ?>

<tr>

    <td class="center">
        <?= $ligne['code'] ?>
    </td>

    <td class="center">
        <?= str_pad(
            $ligne['quantite'],
            2,
            "0",
            STR_PAD_LEFT
        ) ?>
    </td>


    <td class="center">

        BC :
        <?= $ligne['bc'] ?>

        <br>

        <?= str_replace(
            'Couleur',
            '<br>Couleur',
            $ligne['designation']
        ) ?>

    </td>


    <td class="right">

        <?= number_format(
            $ligne['prix'],
            0,
            ',',
            ' '
        ) ?>

    </td>


    <td class="right">

        <?= number_format(
            $ligne['montant'],
            0,
            ',',
            ' '
        ) ?>

    </td>

</tr>

<?php endforeach; ?>

</tbody>

</table>


<!--
====================================================================
    TOTAUX
====================================================================
-->

<div class="totals-container">

<table class="totals">

<tr>

    <td class="label">
        Prix HT
    </td>

    <td class="right">
        <?= number_format(
            $totalHT,
            0,
            ',',
            ' '
        ) ?>
    </td>

</tr>


<tr>

    <td class="label">
        TVA 20%
    </td>

    <td class="right">
        <?= number_format(
            $tva,
            0,
            ',',
            ' '
        ) ?>
    </td>

</tr>


<tr>

    <td class="label">
        Prix TTC
    </td>

    <td class="right">
        <?= number_format(
            $ttc,
            0,
            ',',
            ' '
        ) ?>
    </td>

</tr>


<tr>

    <td class="label">
        Tissus
    </td>

    <td class="right">
        (
        <?= number_format(
            $totalTissu,
            0,
            ',',
            ' '
        ) ?>
        )
    </td>

</tr>


<tr>

    <td class="label">
        Total TTC
    </td>

    <td class="right">
        <?= number_format(
            $totalFinal,
            0,
            ',',
            ' '
        ) ?>
    </td>

</tr>


<tr>

    <td class="label">
        Avance 40%
    </td>

    <td class="right">
        (
        <?= number_format(
            $avance,
            0,
            ',',
            ' '
        ) ?>
        )
    </td>

</tr>


<tr>

    <td class="label">
        Reste
    </td>

    <td class="right bold">
        <?= number_format(
            $reste,
            0,
            ',',
            ' '
        ) ?>
    </td>

</tr>

</table>

</div>


<!--
====================================================================
    SOMME EN LETTRES
====================================================================
-->

<p class="final-amount">

    Arrêtée la présente à la somme

    <strong>
        <?= ucfirst($resteEnLettres) ?>
        Ariary
        (<?= number_format(
            $reste,
            0,
            ',',
            ' '
        ) ?> Ar)
    </strong>

</p>


<?php endif; ?>


</body>

</html>

<?php

$html = ob_get_clean();


/*
|--------------------------------------------------------------------------
| PDF
|--------------------------------------------------------------------------
*/

$dompdf = new Dompdf();

$dompdf->loadHtml($html);

$dompdf->setPaper(
    'A4',
    'portrait'
);

$dompdf->render();


/*
|--------------------------------------------------------------------------
| Nom du fichier
|--------------------------------------------------------------------------
*/

if ($documentType === 'facture') {

    $filename =
        "Facture_" .
        ($documentNum ?: $date) .
        ".pdf";

}

elseif ($documentType === 'proforma') {

    $filename =
        "Proforma_" .
        $date .
        ".pdf";

}

else {

    $filename =
        "Bon_Commande_" .
        ($documentNum ?: $date) .
        ".pdf";
}


$dompdf->stream(
    $filename,
    [
        "Attachment" => false
    ]
);