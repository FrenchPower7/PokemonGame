<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Statistiques du Pokémon</title>
    <link href="http://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="http://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container {
            display: flex;
            flex-direction: column;
        }
        .comparison-table, .top-users-table, .top-pokemons-table {
            flex: 1;
            margin-top: 20px; /* Ajout d'un espace entre les sections */
        }
        .chart-container {
            position: relative;
            margin: auto;
            width: 80%; /* Largeur des graphiques */
            height: 300px; /* Hauteur des graphiques */
        }
    </style>
</head>
<body>

<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
include 'Admin/header.php';
include 'Admin/BDD.php'; 

// Récupérer l'ID du Pokémon depuis l'URL
$pokemonId = isset($_GET['pokemonId']) ? intval($_GET['pokemonId']) : 0;
if ($pokemonId <= 0) {
    header('Location: stats/selection_stats.php');
    exit;
}

$pokemon_api_url = 'http://tyradex.vercel.app/api/v1/pokemon';

// Récupérer les données du Pokémon sélectionné
$pokemonResponse = file_get_contents($pokemon_api_url . '/' . $pokemonId);
if ($pokemonResponse === FALSE) {
    echo "Erreur lors de la récupération des données du Pokémon.";
    exit;
}
$pokemonData = json_decode($pokemonResponse, true);

if (!isset($pokemonData['stats'])) {
    echo "Erreur lors de la récupération des statistiques du Pokémon.";
    exit;
}

// Récupérer les statistiques du Pokémon sélectionné
$attaque = $pokemonData['stats']['atk'] ?? 0;
$defense = $pokemonData['stats']['def'] ?? 0;
$pv = $pokemonData['stats']['hp'] ?? 0;

// Récupérer tous les Pokémon pour comparaison
$allPokemonsResponse = file_get_contents($pokemon_api_url);
$allPokemonsData = json_decode($allPokemonsResponse, true);

if (empty($allPokemonsData)) {
    echo "Aucun Pokémon trouvé.";
    exit;
}

// Comparaison des stats avec tous les Pokémon
$attaqueSuperieure = 0;
$defenseSuperieure = 0;
$pvSuperieur = 0;
$totalPokemons = 0;

foreach ($allPokemonsData as $pokemon) {
    if (isset($pokemon['stats'])) {
        $totalPokemons++;
        
        // Comparaison des statistiques pour les supériorités
        if ($pokemon['stats']['atk'] > $attaque) {
            $attaqueSuperieure++;
        }
        if ($pokemon['stats']['def'] > $defense) {
            $defenseSuperieure++;
        }
        if ($pokemon['stats']['hp'] > $pv) {
            $pvSuperieur++;
        }
    }
}

// Récupérer le top 5 des utilisateurs par victoires
$topWinnersStmt = $bdd->query("SELECT pseudo, win_num FROM users ORDER BY win_num DESC LIMIT 5");
$topWinners = $topWinnersStmt->fetchAll(PDO::FETCH_ASSOC);



// Récupérer le top 10 des Pokémon par attaque, défense et PV
usort($allPokemonsData, function ($a, $b) {
    // Vérifiez si les deux Pokémon ont des statistiques d'attaque
    $atkA = $a['stats']['atk'] ?? 0; // Utiliser 0 si 'atk' est inexistant
    $atkB = $b['stats']['atk'] ?? 0; // Utiliser 0 si 'atk' est inexistant
    return $atkB <=> $atkA; // Trier par attaque
});
$topAttackPokemons = array_slice($allPokemonsData, 0, 10);

usort($allPokemonsData, function ($a, $b) {
    // Vérifiez si les deux Pokémon ont des statistiques de défense
    $defA = $a['stats']['def'] ?? 0; // Utiliser 0 si 'def' est inexistant
    $defB = $b['stats']['def'] ?? 0; // Utiliser 0 si 'def' est inexistant
    return $defB <=> $defA; // Trier par défense
});
$topDefensePokemons = array_slice($allPokemonsData, 0, 10);

usort($allPokemonsData, function ($a, $b) {
    // Vérifiez si les deux Pokémon ont des statistiques de PV
    $hpA = $a['stats']['hp'] ?? 0; // Utiliser 0 si 'hp' est inexistant
    $hpB = $b['stats']['hp'] ?? 0; // Utiliser 0 si 'hp' est inexistant
    return $hpB <=> $hpA; // Trier par PV
});
$topHpPokemons = array_slice($allPokemonsData, 0, 10);

// Récupérer le top 5 des Pokémon favoris
$topFavoritesStmt = $bdd->query("SELECT fav, COUNT(*) AS count FROM users GROUP BY fav ORDER BY count DESC LIMIT 5");
$topFavorites = $topFavoritesStmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les noms des Pokémon favoris
$topFavoriteNames = [];
foreach ($topFavorites as $favorite) {
    $favoriteId = $favorite['fav'];
    $favoriteResponse = file_get_contents($pokemon_api_url . '/' . $favoriteId);
    if ($favoriteResponse !== FALSE) {
        $favoriteData = json_decode($favoriteResponse, true);
        $topFavoriteNames[] = [
            'name' => htmlspecialchars($favoriteData['name']['fr']),
            'count' => $favorite['count']
        ];
    }
}
?>

<div class="container mt-5">
    <!-- Section pour la sélection du Pokémon -->
    <div class="mb-4 text-center"> <!-- Centrage du texte -->
        <h2>Statistiques de votre Pokémon choisi:</h2>
        <p>Nom: <?php echo htmlspecialchars($pokemonData['name']['fr']); ?></p>
        <p>Attaque: <?php echo $attaque; ?></p>
        <p>Défense: <?php echo $defense; ?></p>
        <p>PV: <?php echo $pv; ?></p>
        <p>Nombre de Pokémon avec une attaque supérieure: <?php echo $attaqueSuperieure; ?></p>
        <p>Nombre de Pokémon avec une défense supérieure: <?php echo $defenseSuperieure; ?></p>
        <p>Nombre de Pokémon avec des PV supérieurs: <?php echo $pvSuperieur; ?></p>
        
        <!-- Formulaire pour choisir un Pokémon à comparer -->
        <form action="stats/selection_stats.php" method="get" class="mt-4">
            <button type="submit" class="btn btn-primary">Choisir un autre Pokémon</button>
        </form>
    </div>

    <div class="row">
        <!-- Div gauche pour les graphiques -->
        <div class="col-md-6 comparison-table">
            <h3>Comparaison des Ratios:</h3>

            <!-- Graphique pour le ratio d'attaque -->
            <div class="chart-container">
                <canvas id="attackRatioChart"></canvas>
            </div>

            <!-- Graphique pour le ratio de défense -->
            <div class="chart-container">
                <canvas id="defenseRatioChart"></canvas>
            </div>

            <!-- Graphique pour le ratio de PV -->
            <div class="chart-container">
                <canvas id="hpRatioChart"></canvas>
            </div>
        </div>

        <!-- Div droite pour le top 5 des utilisateurs -->
        <div class="col-md-6 top-users-table">
            <h3>Top 5 Utilisateurs par Victoires:</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Pseudo</th>
                        <th>Nombre de Victoires</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topWinners as $winner): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($winner['pseudo']); ?></td>
                        <td><?php echo $winner['win_num']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section pour le top 10 des Pokémon -->
    <div class="row top-pokemons-table">
        <div class="col-md-4">
            <h3>Top 10 Pokémon par Attaque:</h3>
            <ul class="list-group">
                <?php foreach ($topAttackPokemons as $pokemon): ?>
                <li class="list-group-item"><?php echo htmlspecialchars($pokemon['name']['fr']) . " - " . $pokemon['stats']['atk']; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="col-md-4">
            <h3>Top 10 Pokémon par Défense:</h3>
            <ul class="list-group">
                <?php foreach ($topDefensePokemons as $pokemon): ?>
                <li class="list-group-item"><?php echo htmlspecialchars($pokemon['name']['fr']) . " - " . $pokemon['stats']['def']; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="col-md-4">
            <h3>Top 10 Pokémon par PV:</h3>
            <ul class="list-group">
                <?php foreach ($topHpPokemons as $pokemon): ?>
                <li class="list-group-item"><?php echo htmlspecialchars($pokemon['name']['fr']) . " - " . $pokemon['stats']['hp']; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Section pour le top 5 des Pokémon favoris -->
    <div class="row top-favorites-table mt-4">
        <div class="col-md-12">
            <h3>Top 5 Pokémon Favoris:</h3>
            <ul class="list-group">
                <?php foreach ($topFavoriteNames as $favorite): ?>
                <li class="list-group-item"><?php echo htmlspecialchars($favorite['name']) . " - " . $favorite['count']; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<script>
// Données pour les graphiques
const totalPokemons = <?php echo $totalPokemons; ?>;
const attackSuperiorCount = <?php echo $attaqueSuperieure; ?>;
const defenseSuperiorCount = <?php echo $defenseSuperieure; ?>;
const hpSuperiorCount = <?php echo $pvSuperieur; ?>;

// Fonction pour créer le graphique
function createDonutChart(ctx, label, superiorCount) {
    const inferiorCount = totalPokemons - superiorCount;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Inférieur', 'Supérieur ou Égal'],
            datasets: [{
                data: [inferiorCount, superiorCount],
                backgroundColor: ['#ff6384', '#36a2eb'],
                hoverBackgroundColor: ['#ff6384', '#36a2eb']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: label
                }
            }
        }
    });
}

// Création des graphiques
const attackRatioCtx = document.getElementById('attackRatioChart').getContext('2d');
const defenseRatioCtx = document.getElementById('defenseRatioChart').getContext('2d');
const hpRatioCtx = document.getElementById('hpRatioChart').getContext('2d');

createDonutChart(attackRatioCtx, 'Ratio d\'Attaque', attackSuperiorCount);
createDonutChart(defenseRatioCtx, 'Ratio de Défense', defenseSuperiorCount);
createDonutChart(hpRatioCtx, 'Ratio de PV', hpSuperiorCount);
</script>

<?php include 'Admin/footer.php'; ?>
</body>
</html>
