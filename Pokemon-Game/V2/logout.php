<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_id'])) {
    // Détruire toutes les données de session
    session_unset();
    session_destroy();

    // Redirection vers la page d'accueil après déconnexion
    header("Location: ../index.php");
    exit();
} else {
    // Si l'utilisateur n'est pas connecté, redirection vers l'accueil
    header("Location: ../index.php");
    exit();
}
?>
