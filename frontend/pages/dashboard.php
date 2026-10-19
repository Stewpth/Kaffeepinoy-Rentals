<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.html');
    exit;
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard | KaffeePinoy Rentals</title>
    <link rel="stylesheet" href="../css/main.css">
    <script defer src="../js/data/sample-clients.js"></script>
    <script defer src="../js/services/storage-service.js"></script>
    <script defer src="../js/components/notifications.js"></script>
    <script defer src="../js/components/view-helpers.js"></script>
    <script defer src="../js/components/sidebar.js"></script>
    <script defer src="../js/pages/dashboard.js"></script>
    <script defer src="../js/main.js"></script>
</head>

<body data-page="dashboard"></body>

</html>