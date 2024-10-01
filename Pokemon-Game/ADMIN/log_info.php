<?php
function logConnectionAttempt($pseudo, $success) {
    // Chemin vers le fichier de log
    $logFile = '../ADMIN/log.txt'; 

    // Obtenir l'heure actuelle
    $currentDateTime = date('Y-m-d H:i:s');
    $userIP = $_SERVER['REMOTE_ADDR'];

    // Déterminer le message en fonction du succès
    $status = $success ? "Connexion réussie" : "Échec de la connexion";

    // Construire le message de log
    $logEntry = "/*              *\\\n" .
                "----- " . $pseudo . " -----\n" .
                "Date de connexion : $currentDateTime\n" .
                "IP : $userIP\n" .
                "Statut : $status\n" . 
                "----- [Login] -----\n\n"
                ;

    // Écrire dans le fichier log
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}


function logLogout($pseudo) {
    // Chemin vers le fichier de log
    $logFile = '../ADMIN/log.txt'; 

    // Obtenir l'heure actuelle
    $currentDateTime = date('Y-m-d H:i:s');
    $userIP = $_SERVER['REMOTE_ADDR'];

    // Construire le message de log
    $logEntry = "/*              *\\\n" .
                "----- " . $pseudo . " -----\n" .
                "Date de déconnexion : $currentDateTime\n" .
                "IP : $userIP\n" .
                "Statut : Déconnexion\n" . 
                "----- [Logout] -----\n\n";

    // Écrire dans le fichier log
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}



function logRegisterAttempt($pseudo, $success) {
    // Chemin vers le fichier de log
    $logFile = '../ADMIN/log.txt'; 

    // Obtenir l'heure actuelle
    $currentDateTime = date('Y-m-d H:i:s');
    $userIP = $_SERVER['REMOTE_ADDR'];

    // Déterminer le message en fonction du succès
    $status = $success ? "Inscription réussie" : "Échec de l'inscription";

    // Construire le message de log
    $logEntry = "/*              *\\\n" .
                "----- " . $pseudo . " -----\n" .
                "Date d'inscription : $currentDateTime\n" .
                "IP : $userIP\n" .
                "Statut : $status\n" . 
                "----- [Register] -----\n\n";

    // Écrire dans le fichier log
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}



?>

