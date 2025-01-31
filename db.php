<?php
try {
    // Bağlantı denemesi öncesi hata raporlamayı açalım
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    $host = "localhost";
    $dbname = "database";
    $username = "root";
    $password = "password";
    
    // Önce sunucuya bağlanmayı deneyelim
    $db = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 15,
            PDO::ATTR_PERSISTENT => true
        )
    );
    
} catch(PDOException $e) {
    die("Error connecting to database: " . $e->getMessage() . 
        "<br>Host: $host" .
        "<br>Error code: " . $e->getCode() . 
        "<br>Time: " . date('Y-m-d H:i:s'));
}
?> 
