<?php

$host = getenv('KPR_DB_HOST');
$dbname = getenv('KPR_DB_NAME');
$username = getenv('KPR_DB_USER');
$password = getenv('KPR_DB_PASSWORD');

if ($host === false || $dbname === false || $username === false || $password === false) {
    error_log('Wrong Host/DBNAME/Username or Password, please place the real password');
    http_response_code(500);
    exit('Database configuration is unavailable.');
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Can't connect to database. Tingnan ang PHP error log.");
}
