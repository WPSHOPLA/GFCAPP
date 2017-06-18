<?php

class mailClass
{
    public function __construct()
    {
        include ROOT_DIR . '/lib/classes/PHPMailer/class.smtp.php';
        include ROOT_DIR . '/lib/classes/PHPMailer/class.phpmailer.php';
    }

    public function sendMail($to, $subject, $message, $message_html)
    {
        $mail = new PHPMailer;

        /*$mail->isSMTP();
        $mail->Host = 'localhost';
        $mail->SMTPAuth = true;
        $mail->Username = 'info@gofetchcode.com';
        $mail->Password = '';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 25;*/

        $mail->isMail();
        $mail->setFrom('info@gofetchcode.com', 'GoFetchCode');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message_html;
        $mail->AltBody = $message;

        if (!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }

    public function getMail($from, $to = null, $subject, $message, $message_html)
    {
        $mail = new PHPMailer;

        $mail->isMail();

        if (!$to) {
            $to = "info@gofetchcode.com";
        }

        $mail->setFrom($from, 'GoFetchCode Customer');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message_html;
        $mail->AltBody = $message;

        if (!$mail->send()) {
            return false;
        }
        return true;
    }


    public function send_mail($to, $subject, $message)
    {
        // To send HTML mail, the Content-type header must be set
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';

        // Additional headers
        mail($to, $subject, $message, implode("\r\n", $headers));

    }
}

?>