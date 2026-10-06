<?php
require 'vendor/autoload.php';
$config = require 'config.php';

use Dompdf\Dompdf;
use NumberToWords\NumberToWords;

// Type de document et variables principales
$doc_type = $_POST['doc_type'] ?? 'facture';
$date = $_POST['date'] ?? date('d-m-Y');
$dateFormatee = DateTime::createFromFormat('Y-m-d', $date) ? DateTime::createFromFormat('Y-m-d', $date)->format('d/m/Y') : $date;
$facture_num = $_POST['facture_num'] ?? '';

$codes = $_POST['code'] ?? [];
$quantites = $_POST['quantite'] ?? [];
$designations = $_POST['designation'] ?? [];
$prix = $_POST['prix'] ?? [];

$is_bc = ($doc_type === 'bon_commande');
$is_proforma = ($doc_type === 'proforma');
$is_facture = ($doc_type === 'facture');

// Titre du document
$doc_title = "FACTURE";
if ($is_proforma) {
    $doc_title = "PROFORMA";
} elseif ($is_bc) {
    $doc_title = "BON DE COMMANDE";
}

if ($is_bc) {
    // BON DE COMMANDE
    $remises = $_POST['remise'] ?? [];
    $totalHT = 0;
    $lignes = [];

    for ($i = 0; $i < count($codes); $i++) {
        $qteVal = (float)($quantites[$i] ?? 0);
        $prixVal = (float)($prix[$i] ?? 0);
        $remisePct = (float)($remises[$i] ?? 0);

        // Calcul avec pourcentage : (QTÉ * PU) - (QTÉ * PU * Remise%)
        $sousTotal = $qteVal * $prixVal;
        $montant = $sousTotal * (1 - ($remisePct / 100));
        if ($montant < 0) $montant = 0;
        
        $totalHT += $montant;

        $lignes[] = [
            "code" => $codes[$i] ?? '',
            "quantite" => $qteVal,
            "designation" => $designations[$i] ?? '',
            "prix" => $prixVal,
            "remise" => $remisePct,
            "montant" => $montant
        ];
    }

    $tva = $totalHT * ($config['tva'] ?? 0.20);
    $ttc = $totalHT + $tva;

} else {
    // FACTURE ET PROFORMA
    $bcs = $_POST['bc'] ?? [];
    $tissus = $_POST['tissu'] ?? [];

    $totalHT = 0;
    $totalTissu = 0;
    $lignes = [];

    for ($i = 0; $i < count($codes); $i++) {
        $qteVal = (float)($quantites[$i] ?? 0);
        $prixVal = (float)($prix[$i] ?? 0);
        $tissuVal = (float)($tissus[$i] ?? 0);

        $montant = $qteVal * $prixVal;
        $totalHT += $montant;

        $tissuCost = $tissuVal * $qteVal * ($config['prix_tissu_m'] ?? 0);
        $totalTissu += $tissuCost;

        $lignes[] = [
            "bc" => $bcs[$i] ?? '',
            "code" => $codes[$i] ?? '',
            "quantite" => $qteVal,
            "designation" => $designations[$i] ?? '',
            "prix" => $prixVal,
            "montant" => $montant
        ];
    }

    $tva = $totalHT * ($config['tva'] ?? 0.20);
    $ttc = $totalHT + $tva;
    $totalFinal = $ttc - $totalTissu;
    $avance = $totalFinal * 0.40;
    $reste = $totalFinal - $avance;

    $numberToWords = new NumberToWords();
    $transformer = $numberToWords->getNumberTransformer('fr');
    $resteEnLettres = $transformer->toWords($reste);
}

// Vue HTML pour PDF
ob_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #2c3e50; line-height: 1.4; }
        .header-table { width: 100%; margin-bottom: 25px; border-collapse: collapse; }
        .header-table td { vertical-align: top; }
        .company-info { font-size: 11px; line-height: 1.5; }
        .company-name { font-size: 14px; font-weight: bold; color: #1a252f; text-transform: uppercase; margin-bottom: 4px; }
        
        .title-box { text-align: center; margin: 20px 0 30px 0; padding: 10px; background: #f8f9fa; border-top: 2px solid #2c3e50; border-bottom: 2px solid #2c3e50; }
        .doc-title { font-size: 18px; font-weight: bold; letter-spacing: 1px; color: #1a252f; }
        .doc-num { font-size: 12px; color: #555; margin-top: 4px; }
        
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th { background-color: #2c3e50; color: #ffffff; font-weight: bold; padding: 8px; font-size: 10px; text-transform: uppercase; border: 1px solid #2c3e50; }
        .table td { border: 1px solid #bdc3c7; padding: 7px; vertical-align: middle; }
        
        .right { text-align: right; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        
        .totals-table { width: 100%; border-collapse: collapse; }
        .totals-table td { border: 1px solid #bdc3c7; padding: 6px 10px; }
        .totals-table .bold-row { background-color: #f8f9fa; font-weight: bold; }

        .summary-text { text-align: center; margin-top: 30px; font-size: 11px; padding: 12px; background: #fdfdfd; border: 1px dashed #cccccc; }
    </style>
</head>
<body>

<table class="header-table">
    <tr>
        <td width="55%">
            <div class="company-name"><?= htmlspecialchars($config['fournisseur']['nom'] ?? '') ?></div>
            <div class="company-info">
                Tél : <?= htmlspecialchars($config['fournisseur']['tel'] ?? '') ?><br>
                NIF : <?= htmlspecialchars($config['fournisseur']['nif'] ?? '') ?><br>
                STAT : <?= htmlspecialchars($config['fournisseur']['stat'] ?? '') ?><br>
                <?= htmlspecialchars($config['fournisseur']['adresse'] ?? '') ?>
            </div>
        </td>
        <td width="45%">
            <div style="text-align: right;">
                Antananarivo, le <strong><?= $dateFormatee ?></strong>
            </div>
            <div style="margin-top: 15px; background: #fdfdfd; padding: 10px; border: 1px solid #eee;">
                <strong>Doit : <?= htmlspecialchars($config['client']['nom'] ?? '') ?></strong><br>
                Adresse : <?= htmlspecialchars($config['client']['adresse'] ?? '') ?><br>
                NIF : <?= htmlspecialchars($config['client']['nif'] ?? '') ?><br>
                STAT : <?= htmlspecialchars($config['client']['stat'] ?? '') ?>
            </div>
        </td>
    </tr>
</table>

<div class="title-box">
    <div class="doc-title"><?= $doc_title ?></div>
    <?php if (!$is_proforma && !empty($facture_num)): ?>
        <div class="doc-num">N° <?= htmlspecialchars($facture_num) ?></div>
    <?php endif; ?>
</div>

<?php if ($is_bc): ?>
    <!-- BON DE COMMANDE -->
    <table class="table">
        <thead>
            <tr>
                <th width="12%">CODE</th>
                <th width="38%">DÉSIGNATION</th>
                <th width="10%">QTÉ</th>
                <th width="15%">PRIX UNIT.<br>(Ar)</th>
                <th width="10%">REMISE<br>(%)</th>
                <th width="15%">MONTANT<br>(Ar)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lignes as $l): ?>
            <tr>
                <td class="center"><?= htmlspecialchars($l['code']) ?></td>
                <td><?= str_replace('Couleur', '<br>Couleur', htmlspecialchars($l['designation'])) ?></td>
                <td class="center"><?= str_pad($l['quantite'], 2, "0", STR_PAD_LEFT) ?></td>
                <td class="right"><?= number_format($l['prix'], 0, ',', ' ') ?></td>
                <td class="center"><?= ($l['remise'] > 0) ? $l['remise'] . '%' : '-' ?></td>
                <td class="right"><?= number_format($l['montant'], 0, ',', ' ') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table width="100%">
        <tr>
            <td width="55%"></td>
            <td width="45%">
                <table class="totals-table">
                    <tr>
                        <td class="bold">Total HT</td>
                        <td class="right"><?= number_format($totalHT, 0, ',', ' ') ?> Ar</td>
                    </tr>
                    <tr>
                        <td class="bold">TVA 20%</td>
                        <td class="right"><?= number_format($tva, 0, ',', ' ') ?> Ar</td>
                    </tr>
                    <tr class="bold-row">
                        <td>Total TTC</td>
                        <td class="right"><?= number_format($ttc, 0, ',', ' ') ?> Ar</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

<?php else: ?>
    <!-- FACTURE ET PROFORMA -->
    <table class="table">
        <thead>
            <tr>
                <th width="15%">CODE</th>
                <th width="12%">QUANTITÉ</th>
                <th width="43%">DÉSIGNATION</th>
                <th width="15%">PRIX UNITAIRE<br>(Ar)</th>
                <th width="15%">MONTANT<br>(Ar)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lignes as $l): ?>
            <tr>
                <td class="center"><?= htmlspecialchars($l['code']) ?></td>
                <td class="center"><?= str_pad($l['quantite'], 2, "0", STR_PAD_LEFT) ?></td>
                <td>
                    <?php if (!empty($l['bc'])): ?><strong>BC :</strong> <?= htmlspecialchars($l['bc']) ?><br><?php endif; ?>
                    <?= str_replace('Couleur', '<br>Couleur', htmlspecialchars($l['designation'])) ?>
                </td>
                <td class="right"><?= number_format($l['prix'], 0, ',', ' ') ?></td>
                <td class="right"><?= number_format($l['montant'], 0, ',', ' ') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table width="100%">
        <tr>
            <td width="55%"></td>
            <td width="45%">
                <table class="totals-table">
                    <tr>
                        <td class="bold">Prix HT</td>
                        <td class="right"><?= number_format($totalHT, 0, ',', ' ') ?> Ar</td>
                    </tr>
                    <tr>
                        <td class="bold">TVA 20%</td>
                        <td class="right"><?= number_format($tva, 0, ',', ' ') ?> Ar</td>
                    </tr>
                    <tr>
                        <td class="bold">Prix TTC</td>
                        <td class="right"><?= number_format($ttc, 0, ',', ' ') ?> Ar</td>
                    </tr>
                    <tr>
                        <td class="bold">Tissus</td>
                        <td class="right">(<?= number_format($totalTissu, 0, ',', ' ') ?> Ar)</td>
                    </tr>
                    <tr class="bold-row">
                        <td>Total TTC</td>
                        <td class="right"><?= number_format($totalFinal, 0, ',', ' ') ?> Ar</td>
                    </tr>
                    <tr>
                        <td class="bold">Avance 40%</td>
                        <td class="right">(<?= number_format($avance, 0, ',', ' ') ?> Ar)</td>
                    </tr>
                    <tr class="bold-row">
                        <td>Reste à payer</td>
                        <td class="right"><?= number_format($reste, 0, ',', ' ') ?> Ar</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="summary-text">
        Arrêtée la présente à la somme de <strong><?= ucfirst($resteEnLettres) ?> Ariary (<?= number_format($reste, 0, ',', ' ') ?> Ar)</strong>.
    </div>
<?php endif; ?>

</body>
</html>
<?php
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = str_replace(' ', '_', $doc_title) . "_" . $date . ".pdf";
$dompdf->stream($filename, ["Attachment" => true]);
?>