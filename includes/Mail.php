<?php
namespace Alkane\Mail;

use \Alkane\AlkaneAPI\AlkaneAPI;
use \PHPMailer\PHPMailer\PHPMailer;

/**
 * Mail Class
 *
 * @category  Mailing
 * @package   Mail
 * @author    Sadiq <sadiq.com.bd@gmail.com>
 * @copyright Copyright (c) 2022
 * @version   1.0.0
 * @package   Alkane\Mail
 */


class Mail extends AlkaneAPI {

    private static $smtp_host = '';

    private static $smtp_user = '';

    private static $smtp_pass = '';

    private static $smtp_encrypt = '';

    private static $smtp_port = '';

    private static $smtp_from = '';


    private $recipient;

    private $subject = '';

    private $body = '';

    private $reply = '';


    private $error = '';


    public function __construct($recipient_mail = null) {
        if ($recipient_mail != null) {
            $this->set_recipient($recipient_mail);
        }
    }

    public static function set_smtp_host(string $host) {
        self::$smtp_host = $host;
    }

    public static function set_smtp_user(string $user) {
        self::$smtp_user = $user;
    }

    public static function set_smtp_password(string $pwd) {
        self::$smtp_pass = $pwd;
    }

    public static function set_smtp_encryption(string $encrypt) {
        self::$smtp_encrypt = $encrypt;
    }

    public static function set_smtp_port(int $port) {
        self::$smtp_port = $port;
    }

    public static function set_smtp_from(string $from) {
        self::$smtp_from = $from;
    }


    public function set_subject(string $sub) {
        $this->subject = $sub;
    }

    public function set_body(string $body) {
        $this->body = $body;
    }

    public function set_recipient($mail) {
        if (is_array($mail) || is_string($mail)) {
            $this->recipient = $mail;
        } else {
            $this->error .= "\nError: Invalid Recipient\n";
        }
    }

    public function set_reply(string $mail) {
        $this->reply = $mail;
    }

    public function send() {

        // include phpmailer dependencies
        require_once __DIR__ . '/phpmailer/Exception.php';
        require_once __DIR__ . '/phpmailer/PHPMailer.php';
        require_once __DIR__ . '/phpmailer/SMTP.php';
        
        $mail = new PHPMailer(true);                              
        try {
            //Server settings
            $mail->SMTPDebug = 0;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = $this->smtp_host;                       // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = $this->smtp_user;                   // SMTP username
            $mail->Password = $this->smtp_pass;                   // SMTP password
            $mail->SMTPSecure = $this->smtp_encrypt;              // Enable TLS encryption, `ssl` also accepted
            $mail->Port = $this->smtp_port;                       // TCP port to connect to
        
            //Recipients
            $mail->setFrom($this->smtp_from);

            if (is_array($this->recipient)) {
                foreach ($this->recipient as $recipient) {
                    $mail->addAddress($recipient);                 // Add a recipient
                }
            } else {
                $mail->addAddress($this->recipient);
            }

            if ($this->reply != '' && $this->reply != null) {
                $mail->addReplyTo($this->reply);
            }
  
            //Mail Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $this->subject;
            $mail->Body    = $this->body;
        
            return $mail->send();

        } catch (PHPMailer\PHPMailer\Exception $e) {
            $this->error .= $mail->ErrorInfo;
            return false;
        }
    }

    public function errorInfo() {
        return $this->error;
    }
}


