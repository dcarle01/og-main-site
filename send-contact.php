<?php
// send-contact.php — Contact form handler for opengateways.com
// Uses PHP built-in mail() function (no PHPMailer/Composer required)
// Location: ~/public_html/assets/api/send-contact.php
// Compatible with PHP 5.6+

header('Content-Type: application/json');

// --- Collect and sanitize form input ---
$name    = strip_tags(trim(isset($_POST['name']) ? $_POST['name'] : ''));
$email   = filter_var(trim(isset($_POST['email']) ? $_POST['email'] : ''), FILTER_SANITIZE_EMAIL);
$subject = strip_tags(trim(isset($_POST['subject']) ? $_POST['subject'] : ''));
$message = strip_tags(trim(isset($_POST['message']) ? $_POST['message'] : ''));
$lang    = isset($_POST['lang']) ? $_POST['lang'] : 'en';

// --- Validate ---
if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($message)) {
    $error = ($lang === 'es')
        ? 'Por favor completa todos los campos requeridos.'
        : 'Please fill in all required fields.';
    echo json_encode(array('success' => false, 'message' => $error));
    exit;
}

// --- Configuration ---
$to_email   = 'info@opengateways.com';
$to_name    = 'Open Gateways';
$from_email = 'noreply@opengateways.com';
$from_name  = 'Open Gateways Website';

// --- Build subject line ---
if (empty($subject)) {
    $subject = ($lang === 'es')
        ? "Mensaje de contacto de $name via opengateways.com"
        : "Contact from $name via opengateways.com";
}

// --- Build plain-text body ---
$labelName   = ($lang === 'es') ? 'Nombre' : 'Name';
$labelEmail  = ($lang === 'es') ? 'Correo' : 'Email';
$labelSource = ($lang === 'es')
    ? 'Este mensaje fue enviado desde el formulario de contacto de Open Gateways.'
    : 'This message was sent from the Open Gateways contact form.';

$body  = "$labelName: $name\n";
$body .= "$labelEmail: $email\n";
$body .= str_repeat('-', 40) . "\n\n";
$body .= $message . "\n\n";
$body .= "---\n";
$body .= $labelSource;

// --- Build email headers ---
$headers  = "From: $from_name <$from_email>\r\n";
$headers .= "Reply-To: $name <$email>\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: OpenGateways-ContactForm\r\n";

// --- Send ---
$sent = mail("$to_name <$to_email>", $subject, $body, $headers);

if ($sent) {
    $msg = ($lang === 'es')
        ? 'Mensaje enviado. Gracias por escribirnos.'
        : 'Message sent. Thank you for reaching out.';
    echo json_encode(array('success' => true, 'message' => $msg));
} else {
    error_log("OG Contact form mail() failed for: $email");
    $msg = ($lang === 'es')
        ? 'Error al enviar el mensaje. Intenta de nuevo.'
        : 'Error sending message. Please try again.';
    echo json_encode(array('success' => false, 'message' => $msg));
}
