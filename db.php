<?php
$host = "localhost";
$dbname = "postgres";
$user = "postgres";
$pass = "maslo555";

try{
    $conn = new PDO("pgsql:host=$host; dbname=$dbname", $user, $pass);
    $conn-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e){
    die ("Blad polaczenia z baza danych: " . $e->getMessage());
}
?>