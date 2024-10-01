<?php
session_start();
include '../ADMIN/log_info.php'; // Inclure le fichier de log

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_id'])) {
    $pseudo = $_SESSION['username']; // Récupérer le pseudo de l'utilisateur

    // Détruire toutes les données de session
    session_unset();
    session_destroy();

    // Logger la déconnexion
    logLogout($pseudo);

    // Redirection vers la page d'accueil après déconnexion
    header("Location: ../index.php");
    exit();
} else {
    // Si l'utilisateur n'est pas connecté, redirection vers l'accueil
    header("Location: ../index.php");
    exit();
}
?>
