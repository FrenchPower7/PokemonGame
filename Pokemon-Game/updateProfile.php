<?php
session_start();
include 'ADMIN/BDD.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Rediriger vers la page de connexion si non connecté
    exit;
}

// Récupérer les données JSON envoyées par la requête AJAX
$data = json_decode(file_get_contents("php://input"), true);

$user_id = $_SESSION['user_id'];
$victoires = isset($data['victoires']) ? (int)$data['victoires'] : 0;
$nombreDeCombats = isset($data['combats']) ? (int)$data['combats'] : 0;
$idPokemon = isset($data['idpokemon']) ? (int)$data['idpokemon'] : 0;

// Obtenir le Pokémon préféré de l'utilisateur
$stmt = $bdd->prepare('SELECT fav, pseudo FROM users WHERE id = :user_id');
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$favPokemonId = $result['fav'];
$pseudo = $result['pseudo']; // Récupérer le pseudo pour les logs

// Vérifier si le Pokémon sélectionné est le même que le Pokémon préféré
$fav_game_num = 0;
if ($idPokemon === (int)$favPokemonId) {
    $fav_game_num = 3; // Ajoute 3 si le Pokémon sélectionné est le même que le préféré
}

// Mettre à jour le nombre de jeux et de victoires de l'utilisateur
$query = $bdd->prepare('UPDATE users SET game_num = game_num + :combats, win_num = win_num + :victoires, fav_game_num = fav_game_num + :fav_game_num WHERE id = :user_id');
$query->execute([
    'combats' => $nombreDeCombats,
    'victoires' => $victoires,
    'fav_game_num' => $fav_game_num,
    'user_id' => $user_id
]);

// Ajouter une entrée dans le fichier de log
$currentDateTime = date('Y-m-d H:i:s');
$userIP = $_SERVER['REMOTE_ADDR']; // Récupérer l'adresse IP de l'utilisateur
$logFile = 'ADMIN/log.txt';

// Construire l'entrée du log
$logEntry = "/*              *\\\n" .
            "----- Pseudo : " . $pseudo . " -----\n" .
            "Date de jeu : $currentDateTime\n" .
            "Pokemon ID : $idPokemon\n" .
            "IP : $userIP\n" .
            "Victoires : $victoires / $nombreDeCombats\n" . 
            "----- [Game] -----\n\n";

// Écrire dans le fichier log
file_put_contents($logFile, $logEntry, FILE_APPEND);

// Retourner une réponse
echo json_encode(['success' => true, 'message' => 'Profil mis à jour avec succès.']);
?>
