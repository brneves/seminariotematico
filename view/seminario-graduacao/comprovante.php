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
                <img src="'. $_SERVER['DOCUMENT_ROOT'] .'/wp-content/uploads/2018/04/3536c127-0aa5-4ff5-a42d-2948f4172c12.jpg" alt="Seminário Caminhos da Graduação" style="width: 100%;">
                <h3><strong>' . $nome . '</strong>,</h3>
                <p>Sua inscrição no <strong>Seminário Caminhos da Graduação: A busca pela Excelência & II Seminário Formação de Gestores. </strong> foi realizada com sucesso!</p>
                <br>
                <p><strong>Local</strong>: Auditório do curso de Arquitetura e Urbanismo</p>
                23 a 25 de abril de 2018</p>
                <br><br>
                <p><strong>Realização:</strong></p>
                <p>PRÓ-REITORIA DE GRADUAÇÃO – PROG</p>
                <p>PRÓ-REITORIA DE PLANEJAMENTO – PROPLAN</p>
            </body>
        </html>'
);

$dompdf->set_paper('A4');
$dompdf->render();
$dompdf->stream('comprovante-de-inscricao-seminario-caminhos-graduacao.pdf', ['Attachment' => false]);