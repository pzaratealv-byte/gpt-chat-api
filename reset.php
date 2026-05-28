<?php
session_start();
$_SESSION['chat_history'] = [];
$_SESSION['tokens_remaining'] = 4000;
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);