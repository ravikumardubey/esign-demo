<?php
var_dump(dirname(__FILE__,3));die();
require '../config.php';
$unsigned_file_path = $tmp_path . 'unsigned-' . $file_name_wo_ext . '.pdf';
$signed_file_path = $project_path . 'signed-' . $file_name_wo_ext . '.pdf';


$xmldata = (array) simplexml_load_string(filter_input(INPUT_POST, 'EsignResp')) or die("Failed to load");

if ($xmldata["@attributes"]["errCode"] != 'NA') {
    $msg = $xmldata ["@attributes"]["errMsg"];
    if(empty($msg)){
        $msg ='eSign Request Canceled.[#'.$xmldata["@attributes"]["errCode"].']';
    }
    print($msg);
    exit();
}

$unsigned_file = file_get_contents($unsigned_file_path);

$txn = $xmldata ["@attributes"]["txn"];
$txn_array = explode('----', $txn);
$pdf_byte_range = $txn_array[1];

$pkcs7 = (array) $xmldata['Signatures'];
$pkcs7_value = $pkcs7['DocSignature'];
$cer_value = $xmldata['UserX509Certificate'];


$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$file = $pdf->my_output($signed_file_path, 'S', $unsigned_file, $cer_value, $pkcs7_value, true, $pdf_byte_range);
$pdf->_destroy();
print_pdf($signed_file_path,$file);
//die();
//exit('eSign completed.');



?>