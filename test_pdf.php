<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();
$dompdf->loadHtml("<h1>Test PDF OK</h1>");
$dompdf->render();
$dompdf->stream("test.pdf");
