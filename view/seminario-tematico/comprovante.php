<?php
header('Content-Type: text/html; charset=utf-8');

$cpf = base64_decode($_GET['1']);
$nome = base64_decode($_GET['2']);

require_once ('../../vendor/dompdf/dompdf_config.inc.php');

$dompdf = new DOMPDF();

$dompdf->load_html(
    '<!DOCTYPE html>
        <html lang="pt-br">
            <head>
                <meta charset="ISO-8859-1">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style type="text/css">
                img { position: relative }
                </style>
                <title>Certificado do Participante</title>
            </head>
            <body>
                <img src="'. $_SERVER['DOCUMENT_ROOT'] .'/wp-content/uploads/2018/02/seminario-tematico-da-graduacao.jpeg" alt="Seminário Temático da Graduação" style="width: 100%;">
                <h3><strong>' . $nome . '</strong>,</h3>
                <p>Sua inscrição no <strong>IV Seminário Temático da Graduação</strong> foi realizada com sucesso!</p>
                <br>
                <p><strong>Local</strong>: Campus Timon – MA</p>
                06, 07 e 08 de março de 2018</p>
                <br><br>
                <p><strong>Realização:</strong></p>
                <p>PRÓ-REITORIA DE GRADUAÇÃO – PROG</p>
                <p>NÚCLEO DE ACESSIBILIDADE – NAU</p>
                <p>CAMPUS DA UEMA EM TIMON - MA</p>
            </body>
        </html>'
);

$dompdf->set_paper('A4');
$dompdf->render();
$dompdf->stream('comprovante-de-inscricao-iv-seminario-tematico-graduacao.pdf', ['Attachment' => false]);