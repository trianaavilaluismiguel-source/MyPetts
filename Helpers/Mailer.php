<?php
require_once __DIR__ . '/../Libraries/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../Libraries/PHPMailer/SMTP.php';
require_once __DIR__ . '/../Libraries/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    /**
     * Envía un correo. Retorna true si se envió, false si falló
     * (nunca lanza excepción hacia afuera: un fallo de correo no debe romper el flujo principal).
     */
    public static function enviar(string $destinatario, string $asunto, string $cuerpoHtml): bool
    {
        $config = require __DIR__ . '/../Config/mail.php';

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['usuario'];
            $mail->Password   = $config['contrasena'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $config['puerto'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($config['usuario'], $config['remitente_nombre']);
            $mail->addAddress($destinatario);

            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $cuerpoHtml;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Error al enviar correo: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
