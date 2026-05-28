<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';

$userMessage = trim($_POST['message'] ?? '');

if ($userMessage === '') {
    echo json_encode([
        'success' => false,
        'reply' => 'Mensaje vacío.',
        'tokens_remaining' => $_SESSION['tokens_remaining'] ?? ($config['max_tokens'] ?? 4000)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$systemPrompt = <<<PROMPT
Aqui se teclea la preconfiguracion del chatbot--- tematica--- filtrado--- entrenamiento de la IA avanzada
PROMPT;

if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// --- LIMITADOR DE TOKENS ---
function approximateTokens(string $text): int {
    // Aproximación conservadora: ~3.5 caracteres por token en español
    return (int) ceil(mb_strlen($text, 'UTF-8') / 3.5);
}

$maxTokens = $config['max_tokens'] ?? 4000;

// Calcular tokens usados en el historial + system prompt
$usedTokens = approximateTokens($systemPrompt);
foreach ($_SESSION['chat_history'] as $item) {
    $usedTokens += approximateTokens($item['content']);
}

// Añadir tokens del mensaje actual
$usedTokens += approximateTokens($userMessage);

$tokensRemaining = max(0, $maxTokens - $usedTokens);
$_SESSION['tokens_remaining'] = $tokensRemaining;

if ($tokensRemaining <= 0) {
    echo json_encode([
        'success' => false,
        'reply' => 'Has alcanzado el límite de tokens para esta conversación. Pulsa "Nueva conversación" para reiniciar.',
        'tokens_remaining' => 0
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$_SESSION['chat_history'][] = [
    'role' => 'user',
    'content' => $userMessage
];

$inputText = '';
foreach ($_SESSION['chat_history'] as $item) {
    $role = $item['role'] === 'assistant' ? 'Asistente' : 'Usuario';
    $inputText .= $role . ': ' . $item['content'] . "
";
}

$payload = [
    'model' => $config['model'] ?? 'gpt-4o-mini',
    'instructions' => $systemPrompt,
    'input' => $inputText
];

$ch = curl_init('https://api.openai.com/v1/responses');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . ($config['openai_api_key'] ?? '')
    ],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT => 60
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlError) {
    echo json_encode([
        'success' => false,
        'reply' => 'Error de conexión: ' . $curlError,
        'tokens_remaining' => $tokensRemaining
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode($response, true);

if ($httpCode < 200 || $httpCode >= 300) {
    echo json_encode([
        'success' => false,
        'reply' => $data['error']['message'] ?? 'Error al consultar la API.',
        'tokens_remaining' => $tokensRemaining
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$assistantReply = 'No se pudo obtener una respuesta.';

if (!empty($data['output_text'])) {
    $assistantReply = $data['output_text'];
} elseif (!empty($data['output'][0]['content'])) {
    foreach ($data['output'][0]['content'] as $contentItem) {
        if (($contentItem['type'] ?? '') === 'output_text' && !empty($contentItem['text'])) {
            $assistantReply = $contentItem['text'];
            break;
        }
    }
}

// Actualizar tokens restantes tras la respuesta
$usedTokens += approximateTokens($assistantReply);
$_SESSION['tokens_remaining'] = max(0, $maxTokens - $usedTokens);

$_SESSION['chat_history'][] = [
    'role' => 'assistant',
    'content' => $assistantReply
];

echo json_encode([
    'success' => true,
    'reply' => $assistantReply,
    'tokens_remaining' => $_SESSION['tokens_remaining']
], JSON_UNESCAPED_UNICODE);