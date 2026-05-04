<?php
require_once 'includes/config.php';
$_SESSION['csrf_token'] = 'testtoken';

$ch2 = curl_init('http://localhost/website/ajax/forgot-password.php');
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, http_build_query([
    'csrf_token' => 'testtoken',
    'email' => 'tushar09250@gmail.com'
]));
$headers = [
    'Cookie: PHPSESSID=' . session_id()
];
curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch2);
echo "RESPONSE FROM AJAX:\n";
echo $result;
?>
