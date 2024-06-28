<?php
require "library/XMLSecurityDSig.php";
require "library/XMLSecurityKey.php";
require 'TCPDF/tcpdf.php';
const FILE_NAME = 'sample6.png';
$project_path = __DIR__.'/';
$tmp_path = $project_path.'temp/';

const ASPID = 'TNIC-001';
const RESPONSE_URL = 'https://nic-esigngateway.nic.in/eSign21/response?rs=http://10.202.34.205:8080/esign-working/resp.php';
const PRIVATEKEY = 'cert/noap.key';
const CERTIFICATE = 'cert/nicesign.crt';
const ESIGN_URL = 'https://nic-esigngateway.nic.in/eSign21/acceptClient';

function print_pdf($name, $file) {
    header('Content-Type: application/pdf');
    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
    header('Pragma: public');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Content-Disposition: inline; filename="' . basename($name) . '"');
    header('Content-Length: ' . strlen($file));
    echo $file;
}

$file_name_array = explode('.', FILE_NAME);
$ext = end($file_name_array);
unset($file_name_array[count($file_name_array) - 1]);
$file_name_wo_ext = implode('.', $file_name_array);




