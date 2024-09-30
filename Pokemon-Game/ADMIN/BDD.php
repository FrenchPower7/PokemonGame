<?php

try {
    $bdd = new PDO('mysql:host=localhost;dbname=pokemon;charset=utf8', 'root', 'root');

    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (PDOException $e){
	echo "Database Error: " . $e->getMessage();
	die();
}

?>