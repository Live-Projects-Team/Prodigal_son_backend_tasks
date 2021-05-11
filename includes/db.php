<?php

//Establishing new database connection
try{

    $db_connection = new PDO("mysql:host=$host;dbname=$database_name", $username, $password);
        // set the PDO error mode to exception
    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {

    array_push($errors, $e->getMessage());
    exit();
}