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
  <meta charset="utf-8" />
  <meta http-equiv="refresh" content="0;url=client-list.html" />
  <title>Clients</title>
</head>

<body>
  <a href="client-list.html">Open Client Master List</a>
</body>

</html>