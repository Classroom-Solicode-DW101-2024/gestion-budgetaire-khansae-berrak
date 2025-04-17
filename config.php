<?php

if(session_status() == PHP_SESSION_NONE){
    session_start();
}


$db_host = 'localhost';
$db_name = 'gestion_budget';
$db_username = 'root';
$db_password = '';

try{

    $pdo = new PDO("mysql:localhost=$db_host;dbname=$db_name",$db_username,$db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

}catch(PDOException $ex){
    die("Database Connection Probleme : " . $ex->getMessage());
}
?>