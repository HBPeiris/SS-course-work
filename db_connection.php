<?php
$host = 'localhost'; 
$dbname = 'school'; 
$username = 'root'; 
$password = ''; 

try {
  
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    $conn->exec("SET NAMES 'utf8'");

} catch (PDOException $e) {

    die("Connection failed: " . $e->getMessage());
}
?>