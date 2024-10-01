<?php
session_start();
include '../ADMIN/BDD.php'; // Connexion à la base de données
include '../ADMIN/log_info.php'; // Inclure le fichier de log

// Vérifier si le formulaire d'inscription est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = htmlspecialchars($_POST['username']); // Utiliser pseudo au lieu de username
    $password = $_POST['mdp'];
    $passwordConfirm = $_POST['mdp_confirm'];

    // Vérifier si les mots de passe correspondent
    if ($password !== $passwordConfirm) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
        logRegisterAttempt($pseudo, false); // Logger l'échec de l'inscription
        header('Location: ../index.php');
        exit();
    }

    // Vérifier si le pseudo est déjà pris
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM users WHERE pseudo = ?");
    $stmt->execute([$pseudo]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['error'] = "Nom d'utilisateur déjà pris.";
        logRegisterAttempt($pseudo, false); // Logger l'échec de l'inscription
        header('Location: ../index.php');
        exit();
    }

    // Hashage du mot de passe
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insertion dans la base de données
    $stmt = $bdd->prepare("INSERT INTO users (pseudo, mdp) VALUES (?, ?)");
    if ($stmt->execute([$pseudo, $hashedPassword])) {
        $_SESSION['user_id'] = $bdd->lastInsertId();
        $_SESSION['username'] = $pseudo;
        logRegisterAttempt($pseudo, true); // Logger la réussite de l'inscription
        header('Location: ../game.php');
        exit();
    } else {
        $_SESSION['error'] = "Erreur lors de l'inscription.";
        logRegisterAttempt($pseudo, false); // Logger l'échec de l'inscription
        header('Location: ../index.php');
        exit();
    }
}
?>
