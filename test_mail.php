<?php
$to = "tushar09250@gmail.com";
$subject = "PHP mail() Test";
$message = "This is a test of the PHP mail() function.";
$headers = "From: webmaster@example.com";

if (mail($to, $subject, $message, $headers)) {
    echo "SUCCESS: mail() accepted the email.\n";
} else {
    echo "FAILURE: mail() failed.\n";
}
?>
