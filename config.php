<?php
return [
    'openai_api_key' => 'APIKEY',
    'model'          => 'gpt-4',
    'from_email'     => 'email',
    'from_name'      => 'Chatbot',
    'site_name'      => 'github',

    // Límite de tokens para toda la conversación
    'max_tokens'     => 4000,

    // SMTP
    'smtp_host'      => 'email',
    'smtp_port'      => 465,
    'smtp_secure'    => 'ssl', // tls o ssl
    'smtp_username'  => 'email',
    'smtp_password'  => 'pwd_email'
];