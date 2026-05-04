<?php
/**
 * Simple SMTP Mailer Class
 * A lightweight alternative to PHPMailer for basic SMTP needs
 */
class SimpleMailer {
    private $host;
    private $port;
    private $username;
    private $password;
    private $fromEmail;
    private $fromName;
    private $debug = false;

    public function __construct($host, $port, $username, $password, $fromEmail, $fromName) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function send($to, $subject, $body, $isHTML = true) {
        try {
            if ($this->port == 465) {
                // Port 465 uses Implicit SSL which drastically cuts down connection time
                $socket = fsockopen("ssl://{$this->host}", $this->port, $errno, $errstr, 10);
                if (!$socket) {
                    throw new Exception("Connection failed: $errstr ($errno)");
                }
                
                $serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
                
                // For SSL, we get the welcome banner instantly, then say EHLO and we're ready for AUTH
                $this->serverCmd($socket, "220"); // Welcome
                $this->serverCmd($socket, "EHLO " . $serverName, "250");
            } else {
                // Port 587 uses explicit TLS (requires an extra round trip for STARTTLS)
                $socket = fsockopen("tcp://{$this->host}", $this->port, $errno, $errstr, 10);
                if (!$socket) {
                    throw new Exception("Connection failed: $errstr ($errno)");
                }
                
                $serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
                
                $this->serverCmd($socket, "220"); // Welcome
                $this->serverCmd($socket, "EHLO " . $serverName, "250");
                $this->serverCmd($socket, "STARTTLS", "220");
                
                // Enable crypto
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                
                $this->serverCmd($socket, "EHLO " . $serverName, "250");
            }
            
            $this->serverCmd($socket, "AUTH LOGIN", "334");
            $this->serverCmd($socket, base64_encode($this->username), "334");
            $this->serverCmd($socket, base64_encode($this->password), "235");
            
            $this->serverCmd($socket, "MAIL FROM: <{$this->fromEmail}>", "250");
            $this->serverCmd($socket, "RCPT TO: <$to>", "250");
            $this->serverCmd($socket, "DATA", "354");
            
            // Headers
            $headers = "MIME-Version: 1.0\r\n";
            if ($isHTML) {
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            } else {
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            }
            $headers .= "From: =?UTF-8?B?" . base64_encode($this->fromName) . "?= <{$this->fromEmail}>\r\n";
            $headers .= "To: <$to>\r\n";
            $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
            $headers .= "Date: " . date("r") . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
            
            // Send Headers & Body
            fwrite($socket, $headers . "\r\n" . $body . "\r\n.\r\n");
            $this->getResponse($socket, "250");
            
            // Quit
            fwrite($socket, "QUIT\r\n");
            fclose($socket);
            
            return true;
        } catch (Exception $e) {
            error_log("SMTP Error: " . $e->getMessage());
            return false;
        }
    }

    private function serverCmd($socket, $cmd, $expectedCode = null) {
        fwrite($socket, $cmd . "\r\n");
        $this->getResponse($socket, $expectedCode);
    }

    private function getResponse($socket, $expectedCode) {
        $response = "";
        while (substr($response, 3, 1) != ' ') {
            if (!($line = fgets($socket, 512))) {
                return false;
            }
            $response .= $line;
        }
        
        if ($this->debug) {
            error_log("SMTP: $response");
        }

        if ($expectedCode && substr($response, 0, 3) != $expectedCode) {
            throw new Exception("SMTP Error: Expected $expectedCode, got $response");
        }
    }
}
?>
