<?php
require_once __DIR__ . '/vendor/autoload.php';

$g = new \PHPGangsta_GoogleAuthenticator();
$secret = $g->createSecret();
echo "Secret: " . $secret . "<br>";
$qrCodeUrl = $g->getQRCodeGoogleUrl('YourAppName', $secret);
echo "QR Code URL: <a href='$qrCodeUrl' target='_blank'>$qrCodeUrl</a><br>";

$otp = readline("Enter OTP: ");
$check = $g->verifyCode($secret, trim($otp), 2);
echo $check ? "✅ Success" : "❌ Failed";
?>

