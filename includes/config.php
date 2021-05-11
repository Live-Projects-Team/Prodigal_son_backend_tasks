<?php
//start a session

session_start();

$errors = [];

//database access parameters


    $host='localhost';
    $username='root';
    $password='';
    $database_name='livePesa_DB';


//connecting to Database

require('db.php');

//adding user class

require('../classes/Normal_User.php');

//making use of database with users

$normal_user = new Normal_User($db_connection);

