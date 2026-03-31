<?php
/**
 * Servicio de envío de correos con PHPMailer
 *
 * INSTALACIÓN (una sola vez, en terminal dentro de admin/):
 *   composer require phpmailer/phpmailer
 *
 * Luego configura tus datos SMTP en admin/config.php agregando:
 *   define('MAIL_HOST',     'smtp.gmail.com');
 *   define('MAIL_PORT',     587);
 *   define('MAIL_USER',     'tucorreo@gmail.com');
 *   define('MAIL_PASS',     'tu_app_password');
 *   define('MAIL_FROM',     'tucorreo@gmail.com');
 *   define('MAIL_FROM_NAME','Taller Refaccionaria');
 */

require_once __DIR__ . '/../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

function cargarPHPMailer(): bool {
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (!file_exists($autoload)) return false;
    require_once $autoload;
    return true;
}

/**
 * Envía el correo de bienvenida al cliente recién registrado.
 * Devuelve true si se envió, false si PHPMailer no está instalado.
 * Lanza Exception si hay error de envío.
 */
function enviarBienvenida(string $nombre, string $email, string $contrasena): bool {
    if (!cargarPHPMailer()) return false;

    $mail = new PHPMailer(true);

    // Configuración SMTP
    $mail->isSMTP();
    $mail->Host       = defined('MAIL_HOST') ? MAIL_HOST : 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = defined('MAIL_USER') ? MAIL_USER : '';
    $mail->Password   = defined('MAIL_PASS') ? MAIL_PASS : '';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = defined('MAIL_PORT') ? MAIL_PORT : 587;
    $mail->CharSet    = 'UTF-8';

    // Remitente y destinatario
    $fromEmail = defined('MAIL_FROM')      ? MAIL_FROM      : $mail->Username;
    $fromName  = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'Taller Refaccionaria';

    $mail->setFrom($fromEmail, $fromName);
    $mail->addAddress($email, $nombre);
    $mail->addReplyTo($fromEmail, $fromName);

    // Contenido HTML
    $mail->isHTML(true);
    $mail->Subject = '¡Bienvenido a Taller Refaccionaria!';
    $mail->Body    = plantillaBienvenida($nombre, $email, $contrasena);
    $mail->AltBody = "Hola $nombre, tu cuenta ha sido creada. Correo: $email | Contraseña: $contrasena";

    $mail->send();
    return true;
}

/**
 * Plantilla HTML del correo de bienvenida.
 */
function plantillaBienvenida(string $nombre, string $email, string $contrasena): string {
    $anio = date('Y');
    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:30px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">

        <!-- Header -->
        <tr>
          <td style="background:#1a1a2e;padding:32px 40px;text-align:center;">
            <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:700;letter-spacing:1px;">
              🔧 Taller Refaccionaria
            </h1>
            <p style="margin:8px 0 0;color:#aaaacc;font-size:14px;">Sistema de Gestión</p>
          </td>
        </tr>

        <!-- Bienvenida -->
        <tr>
          <td style="padding:40px 40px 24px;">
            <h2 style="margin:0 0 12px;color:#1a1a2e;font-size:22px;">¡Bienvenido, {$nombre}!</h2>
            <p style="margin:0 0 24px;color:#555;line-height:1.6;font-size:15px;">
              Tu cuenta ha sido creada exitosamente. A continuación encontrarás
              tus credenciales de acceso al sistema.
            </p>

            <!-- Credenciales -->
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="background:#f0f4ff;border:1px solid #d0d9f0;border-radius:8px;margin-bottom:28px;">
              <tr>
                <td style="padding:20px 24px;">
                  <p style="margin:0 0 14px;font-size:13px;color:#888;text-transform:uppercase;letter-spacing:.5px;font-weight:700;">
                    Tus credenciales de acceso
                  </p>
                  <table width="100%">
                    <tr>
                      <td style="padding:6px 0;color:#555;font-size:14px;width:120px;">Correo:</td>
                      <td style="padding:6px 0;font-weight:700;color:#1a1a2e;font-size:14px;">{$email}</td>
                    </tr>
                    <tr>
                      <td style="padding:6px 0;color:#555;font-size:14px;">Contraseña:</td>
                      <td style="padding:6px 0;">
                        <span style="background:#1a1a2e;color:#ffffff;font-family:monospace;
                                     font-size:15px;padding:4px 12px;border-radius:4px;
                                     letter-spacing:1px;">{$contrasena}</span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            <!-- Recomendación -->
            <div style="background:#fff8e1;border-left:4px solid #ffc107;padding:14px 18px;
                        border-radius:0 6px 6px 0;margin-bottom:28px;">
              <p style="margin:0;color:#856404;font-size:13px;line-height:1.5;">
                <strong>Recomendación de seguridad:</strong> Te sugerimos cambiar tu contraseña
                después de iniciar sesión por primera vez.
              </p>
            </div>

            <p style="margin:0;color:#555;font-size:14px;line-height:1.6;">
              Ahora puedes <strong>agendar citas</strong>, registrar tus vehículos
              y consultar el estado de tus órdenes desde el sistema.
            </p>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#f8f9fa;padding:20px 40px;border-top:1px solid #eee;text-align:center;">
            <p style="margin:0;color:#aaa;font-size:12px;">
              © {$anio} Taller Refaccionaria &mdash; Este correo fue generado automáticamente.
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
}
?>
