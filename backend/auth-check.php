<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: /Kaffepinoy-project/frontend/pages/login.html');
    exit;
}
