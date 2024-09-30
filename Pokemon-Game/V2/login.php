<?php
session_start();
include '../ADMIN/BDD.php'; // Connexion à la base de données

// Vérifier si le formulaire de connexion est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = htmlspecialchars($_POST['username']); // Utiliser pseudo au lieu de username
    $password = $_POST['mdp'];

    // Récupérer l'utilisateur correspondant au pseudo
    $stmt = $bdd->prepare("SELECT * FROM users WHERE pseudo = ?");
    $stmt->execute([$pseudo]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si l'utilisateur existe et si le mot de passe est correct
    if ($user && password_verify($password, $user['mdp'])) {
        // Connexion réussie
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['pseudo'];
        header('Location: ../game.php');
        exit();
    } else {
        $_SESSION['error'] = "Nom d'utilisateur ou mot de passe incorrect.";
        header('Location: ../index.php');
        exit();
    }
}
?>
