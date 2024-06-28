<?php
require "config.php";

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
function autoRotateImage($image) {
    $orientation = $image->getImageOrientation();

    switch($orientation) {
        case imagick::ORIENTATION_BOTTOMRIGHT: 
            $image->rotateimage("#000", 180); // rotate 180 degrees
            break;

        case imagick::ORIENTATION_RIGHTTOP:
            $image->rotateimage("#000", 90); // rotate 90 degrees CW
            break;

        case imagick::ORIENTATION_LEFTBOTTOM: 
            $image->rotateimage("#000", -90); // rotate 90 degrees CCW
            break;
    }

    // Now that it's auto-rotated, make sure the EXIF data is correct in case the EXIF gets saved with the image!
    $image->setImageOrientation(imagick::ORIENTATION_TOPLEFT);
}
//$file = file_get_contents(FILE_NAME);
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

/* Commented by azhar
$imagick = new Imagick();
$imagick->setBackgroundColor('white');
$imagick->setResolution(288, 288);
$imagick->readImage($project_path . 'cert/' . FILE_NAME);
autoRotateImage($imagick); */

$width = 560;
$height = 1024;
$image = imagecreatetruecolor($width, $height);
$bgColor = imagecolorallocate($image, 255, 255, 255); // white background
imagefill($image, 10, 10, $bgColor);
$captcha_fill_color = imagecolorallocate($image, 5, 5, 5);
imagestring($image, 9, 9, 9, "this is a test product ", $captcha_fill_color);
// Output the image to the browser
//header('Content-Type: image/png');
$imageFileName = $tmp_path . FILE_NAME;
imagepng($image, $imageFileName);

// Free up memory

//get file name only w/o ext.

//print_r(imagepng($image));

$num_pages = 1;
$temp_ext = 'png';
// Convert PDF pages to images


imagedestroy($image);
//die;
// set certificate file
$info = array();
// set document signature
$pdf->my_set_sign('', '', '', '', 2, $info);

//take created images from folder to set esign & create new pdf
for ($i = 0; $i < $num_pages; $i++) {
    $pdf->AddPage();
    $pdf->Image($imageFileName,$x = '', $y = '', $w = 0, $h = 0, $type = '', $link = '', $align = '', $resize = true, $dpi = 300, $palign = '', $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = true, $alt = false, $altimgs = array());
}
//start to add bg image for the 'esigned by' cell on document--- on 03-08-2018
// get the current page break margin
$bMargin = $pdf->getBreakMargin();
// get current auto-page-break mode
$auto_page_break = $pdf->getAutoPageBreak();
// disable auto-page-break
$pdf->SetAutoPageBreak(false, 0);
// set bacground image

$pdf->SetAlpha(0.5);

// restore auto-page-break status
$pdf->SetAutoPageBreak($auto_page_break, $bMargin);
// set the starting point for the page content
$pdf->setPageMark();
//end to add bg image on cell
// define active area for signature appearance
$pdf->setSignatureAppearance(140, 255, 60, 17);

$pdf->SetFont('times', '', 8);
$pdf->setCellPaddings(1, 2, 1, 1);
$pdf->MultiCell(40, 10, 'Digitally Signed by: Deepak Srivastava' . "\n" . 'Date: ' . date('d-m-Y') . "\n" . date('h:i a'), 0, '', 0, 1, 157, 255, true);

$doc_path = $tmp_path . 'unsigned-' . $file_name_wo_ext . '.pdf';
//Close and output PDF document

$file = $pdf->my_output($doc_path, 'F');
//$file = $pdf->my_output($doc_path, 'S');
 
// print_pdf($file_name_wo_ext.'.pdf',$file);
// die(); 

$pdf_byte_range = $pdf->pdf_byte_range;
//$pdf->_destroy();

$file_hash = hash_file('sha256', $doc_path);

//after pdf done using images, delete that temp images from folder.
// for ($i = 0; $i < $num_pages; $i++) {
//     unlink($tmp_path . $file_name_wo_ext . '-' . $i . '.' . $temp_ext);
// }
$doc = new DOMDocument(); 
$txn = "999-SWIK-" .date('Ymd') .rand(111111111111, 999999999999) . '-' . $pdf_byte_range;

$ts = date('Y-m-d\TH:i:s');

$doc_info = FILE_NAME;

   $xmlstr = '<Esign AuthMode="1" aspId="' . ASPID . '" ekycId="" ekycIdType="A" responseSigType="pkcs7" responseUrl="' . RESPONSE_URL . '" sc="y" ts="' . $ts . '" txn="' . $txn . '" ver="2.1"><Docs><InputHash docInfo="' . $txn . '" hashAlgorithm="SHA256" id="1">' . $file_hash . '</InputHash></Docs></Esign>';


$doc->loadXML($xmlstr);

// Create a new Security object 
$objDSig = new RobRichards\XMLSecLibs\XMLSecurityDSig();
// Use the c14n exclusive canonicalization
$objDSig->setCanonicalMethod(RobRichards\XMLSecLibs\XMLSecurityDSig::C14N);
// Sign using SHA-256
$objDSig->addReference(
        $doc,
        RobRichards\XMLSecLibs\XMLSecurityDSig::SHA1,
        array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'),
        array('force_uri' => true)
);



// // Create a new Security key for the certificate (public key)
// $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type' => 'public'));
 

// //$objKey = new RobRichards\XMLSecLibs\XMLSecurityKey(RobRichards\XMLSecLibs\XMLSecurityKey::RSA_SHA1, array('type' => 'public'));
// $objKey->loadKey(CERTIFICATE, TRUE);
// // If key has a passphrase, set it using
// $objKey->passphrase = '';
// // Set the key for reference verification
// $objDSig->add509Cert($objKey);


// Create a new (private) Security key
$objKey = new RobRichards\XMLSecLibs\XMLSecurityKey(RobRichards\XMLSecLibs\XMLSecurityKey::RSA_SHA1, array('type' => 'private'));

//If key has a passphrase, set it using
$objKey->passphrase = '';

// Load the private key
$objKey->loadKey(PRIVATEKEY, TRUE); 
//$objKey->loadKey(PRIVATEKEY, TRUE,'cert/999-SWIK.crt'); 

// Sign the XML file
$objDSig->sign($objKey);
// Load the public key (certificate)
//$objDSig->add509Cert(file_get_contents('cert/999-SWIK.crt'));
$certContent = file_get_contents('cert/999-SWIK.crt');
$certData = "-----BEGIN CERTIFICATE-----\n" . chunk_split(base64_encode($certContent), 64, "\n") . "-----END CERTIFICATE-----";
 
$certData = trim($certData);
// Add the associated public key to the signature
$objDSig->add509Cert($certData);
 
// Append the signature to the XML
$objDSig->appendSignature($doc->documentElement);

$signXML = $doc->saveXML();
ob_end_clean();
//echo $signXML ;die();
?>
<form action="<?php echo ESIGN_URL; ?>" method="post" id="formid">
    <!-- <input type="hidden" id="eSignRequest" name="eSignRequest" value='<?php echo $signXML; ?>'/>
    <input type="hidden" id="aspTxnID" name="aspTxnID" value="<?php echo $txn; ?>"/> -->
    <input type="hidden" id="Content-Type" name="Content-Type" value="application/xml"/>
    <input type="hidden" id="xml" name="xml" value='<?php echo $signXML; ?>'/>
    <input type="hidden" id="clientrequestURL" name="clientrequestURL" value="<?php echo RESPONSE_URL; ?>"/>
    <input type="hidden" id="username" name="username" value="Deepak"/>
    <input type="hidden" id="userId" name="userId" value="1"/>
    <input type="hidden" id="clientId" name="clientId" value="2"/>
</form>
<script>

   document.getElementById("formid").submit();
</script>



