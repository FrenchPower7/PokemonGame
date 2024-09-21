<?php
// Chemin vers le fichier de profil
$profilFile = 'profil.txt';

// Lire le contenu du fichier
$profilData = file($profilFile, FILE_IGNORE_NEW_LINES);
$profilStats = [];
foreach ($profilData as $line) {
    list($key, $value) = explode(': ', $line);
    $profilStats[$key] = $value;
}

// Recevoir les données du match
$data = json_decode(file_get_contents('php://input'), true);
$result = $data['result'];
$pokemonName = $data['pokemonName'];

// Mettre à jour les statistiques
$profilStats['Nombre de parties jouées']++;
if ($result === "Gagné") {
    $profilStats['Nombre de victoires']++;
}

// Vérifier si le Pokémon préféré est le même que celui joué
if ($profilStats['Pokémon préféré'] === $pokemonName) {
    $profilStats['Nombre de fois joué avec le Pokémon']++;
} elseif ($profilStats['Pokémon préféré'] === 'Aucun') {
    // Si aucun Pokémon préféré n'est défini, le définir
    $profilStats['Pokémon préféré'] = $pokemonName;
}

// Écrire les données mises à jour dans le fichier
$updatedProfilData = "";
foreach ($profilStats as $key => $value) {
    $updatedProfilData .= "$key: $value\n";
}
file_put_contents($profilFile, $updatedProfilData);

http_response_code(200); // Répondre avec succès
?>
