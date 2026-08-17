<?php
session_start();
require_once 'newiine.php';

$makeToken = new newiineToken;

// ページロードごとにリセット
unset($_SESSION['csrf_token']);
$token = $makeToken->makeToken();

header('Content-Type: application/json');
echo json_encode(['token' => $token]);
