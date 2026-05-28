<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';
require __DIR__ . '/smtp_mailer.php';

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$subject = trim($_POST['subject'] ?? 'Conversación con el chatbot');

if (!$email) {
    echo json_encode([
        'success' => false,
        'message' => 'Introduce un email válido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$history = $_SESSION['chat_history'] ?? [];

if (empty($history)) {
    echo json_encode([
        'success' => false,
        'message' => 'No hay conversación para enviar.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$body = $config['site_name'] . "\n\n";
$body .= "Conversación exportada:\n\n";

foreach ($history as $item) {
    $role = $item['role'] === 'assistant' ? 'Asistente' : 'Usuario';
    $body .= $role . ': ' . $item['content'] . "\n\n";
}

$error = null;
$sent = smtp_send_mail($config, $email, $subject, $body, $error);

if ($sent) {
    echo json_encode([
        'success' => true,
        'message' => 'Conversación enviada correctamente.'
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo enviar el email.',
        'error' => $error
    ], JSON_UNESCAPED_UNICODE);
}
