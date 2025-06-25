<?php
//wherethe serv is beinghosted
$host    = 'localhost';
//whatis the name of thedb
$db      = 'cmssecl';   
//username in thedb  
$user    = 'root';    
//passwordfor theuser in thedatabase(not rec if local)       
$pass    = '';                
//cuz it supportunicodeand isused in newver
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    //using pdacuz otheralternatives suck
    //when dberror occursthrows anexcept
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    //def fetchmode, ret dataas anassociativearray
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    //use thedb server'spreparedstatement capabilities,improvedsecurityagainst SQLinjection.
    PDO::ATTR_EMULATE_PREPARES   => false,
];
//error show
ini_set('display_errors', 1);
error_reporting(E_ALL);

//tocatchanyexcepthrown and deal withit
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Database connection failed.');
}
?>