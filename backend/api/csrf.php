<?php
require_once __DIR__ . '/../lib/api.php';
kprApiAuth();
if (empty($_SESSION['csrf_token']))
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
kprJson(200, ['success' => true, 'token' => $_SESSION['csrf_token']]);
