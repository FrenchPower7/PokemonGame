<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Interdit si non connecté
    echo json_encode(['error' => 'Vous devez être connecté pour mettre à jour votre profil.']);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    // Connexion à la base de données MySQL
    $db = new PDO('mysql:host=localhost;dbname=pokemon;charset=utf8', 'root', 'root');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Recevoir les données du match
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['result']) || !isset($data['pokemonName'])) {
        http_response_code(400); // Mauvaise requête
        echo json_encode(['error' => 'Données du match manquantes ou incorrectes.']);
        exit();
    }

    $result = $data['result'];
    $pokemonName = $data['pokemonName'];

    // Récupérer les statistiques de l'utilisateur
    $stmt = $db->prepare("SELECT game_num, win_num, fav, fav_game_win FROM user WHERE id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $profilStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profilStats) {
        http_response_code(404); // Non trouvé
        echo json_encode(['error' => 'Profil utilisateur non trouvé.']);
        exit();
    }

    // Mettre à jour le nombre de parties jouées
    $profilStats['game_num']++;

    // Si le résultat est "Gagné", mettre à jour le nombre de victoires
    if ($result === "Gagné") {
        $profilStats['win_num']++;
    }

    // Vérifier si le Pokémon préféré est le même que celui joué
    if ($profilStats['fav'] == $pokemonName) {
        $profilStats['fav_game_win']++; // Incrémenter si c'est le Pokémon préféré
    } elseif ($profilStats['fav'] === null || $profilStats['fav'] === '0') {
        // Si aucun Pokémon préféré n'est défini, le définir
        $profilStats['fav'] = $pokemonName;
        $profilStats['fav_game_win'] = $result === "Gagné" ? 1 : 0;
    }

    // Mise à jour des statistiques dans la base de données
    $stmt = $db->prepare("UPDATE user 
                          SET game_num = :game_num, 
                              win_num = :win_num, 
                              fav = :fav, 
                              fav_game_win = :fav_game_win 
                          WHERE id = :user_id");

    $stmt->bindParam(':game_num', $profilStats['game_num'], PDO::PARAM_INT);
    $stmt->bindParam(':win_num', $profilStats['win_num'], PDO::PARAM_INT);
    $stmt->bindParam(':fav', $profilStats['fav'], PDO::PARAM_STR);
    $stmt->bindParam(':fav_game_win', $profilStats['fav_game_win'], PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

    $stmt->execute();

    // Répondre avec succès
    http_response_code(200);
    echo json_encode(['message' => 'Profil mis à jour avec succès.']);

} catch (PDOException $e) {
    // En cas d'erreur, envoyer une réponse d'erreur
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de la base de données : ' . $e->getMessage()]);
    exit();
}
?>
