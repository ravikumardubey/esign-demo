<?php
require 'config.php';
$unsigned_file_path = $tmp_path . 'unsigned-' . $file_name_wo_ext . '.pdf';
$signed_file_path = $project_path . 'signed-' . $file_name_wo_ext . '.pdf';
 // Get the URL-encoded data from the query string
$urlEncodedData = $_POST['respon'];  // Assuming it's from a POST request, adjust accordingly

// Decode URL-encoded data
$xmlString = urldecode($urlEncodedData);

// Parse XML data
$xml = simplexml_load_string($xmlString);


$userX509Certificate = (string) $xml->UserX509Certificate;
$docSignatureError = (string) $xml->Signatures->DocSignature['error'];
$docSignatureId = (string) $xml->Signatures->DocSignature['id'];
$docSignatureSigHashAlgorithm = (string) $xml->Signatures->DocSignature['sigHashAlgorithm'];

// Print or use the values as needed
// echo "UserX509Certificate: $userX509Certificate\n";
// echo "DocSignature Error: $docSignatureError\n";
// echo "DocSignature Id: $docSignatureId\n";
// echo "DocSignature SigHashAlgorithm: $docSignatureSigHashAlgorithm\n";
// Access the elements you need
$errCode = $xml['errCode'];
$errMsg = $xml['errMsg'];
$resCode = $xml['resCode'];
$status = $xml['status'];
$ts = $xml['ts'];
$txn = $xml['txn'];
 
$txn_array = explode('-', $txn);
$pdf_byte_range = $txn_array[3]; 

$pkcs7 = $xml->Signatures;
$pkcs7_value = $xml->Signatures->DocSignature;
$cer_value =$xml->UserX509Certificate;
// ... and so on

// Print or use the parsed data as needed
echo "errCode: $errCode\n";
echo "errMsg: $errMsg\n";
echo "resCode: $resCode\n";
echo "status: $status\n";
echo "ts: $ts\n";
echo "txn: $txn\n";

if ($errCode != 'NA') {
    $msg = $errMsg;
    if(empty($msg)){
        $msg ='eSign Request Canceled.[#'.$errCode.']';
    }
    print($msg);
    exit();
}
$unsigned_file = file_get_contents($unsigned_file_path);
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$file = $pdf->my_output($signed_file_path, 'F', $unsigned_file, $cer_value, $pkcs7_value, true, $pdf_byte_range);
$pdf->_destroy();

exit('eSign completed.');
// ... and so on
exit;

// echo $xmlRequest = file_get_contents('php://input');
    
// // You can now parse and process the $xmlRequest as needed
// // For example, you can use SimpleXML to parse it
// $xml = simplexml_load_string($xmlRequest);

// echo "<pre>";print_r($xml);exit;

// $xmldata = (array) simplexml_load_string(filter_input(INPUT_POST, 'EsignResp')) or die("Failed to load");
// if ($xmldata["@attributes"]["errCode"] != 'NA') {
//     $msg = $xmldata ["@attributes"]["errMsg"];
//     if(empty($msg)){
//         $msg ='eSign Request Canceled.[#'.$xmldata["@attributes"]["errCode"].']';
//     }
//     print($msg);
//     exit();
// }

// $unsigned_file = file_get_contents($unsigned_file_path);

// // $txn = $xmldata ["@attributes"]["txn"];
// // $txn_array = explode('----', $txn);
// // $pdf_byte_range = $txn_array[1];

// $pkcs7 = (array) $xmldata['Signatures'];
// $pkcs7_value = $pkcs7['DocSignature'];
// $cer_value = $xmldata['UserX509Certificate'];


// $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// $file = $pdf->my_output($signed_file_path, 'F', $unsigned_file, $cer_value, $pkcs7_value, true, $pdf_byte_range);
// $pdf->_destroy();

// exit('eSign completed.');



?>