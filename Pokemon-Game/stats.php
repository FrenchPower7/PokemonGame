<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Statistiques du Pokémon</title>
    <link href="http://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container {
            display: flex;
            flex-direction: column;
        }
        .comparison-table, .top-users-table {
            flex: 1;
            margin-top: 20px; /* Ajout d'un espace entre les sections */
        }
        .top-users-table {
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
$attaqueInferieure = 0;
$defenseInferieure = 0;
$pvInferieure = 0;
$totalPokemons = 0;

foreach ($allPokemonsData as $pokemon) {
    if (isset($pokemon['stats'])) {
        $totalPokemons++;
        
        // Comparaison des statistiques pour les ratios
        if ($pokemon['stats']['atk'] < $attaque) {
            $attaqueInferieure++;
        }
        if ($pokemon['stats']['def'] < $defense) {
            $defenseInferieure++;
        }
        if ($pokemon['stats']['hp'] < $pv) {
            $pvInferieure++;
        }
    }
}

// Récupérer les 5 meilleurs utilisateurs par win_num
$stmt = $bdd->query("SELECT pseudo, win_num FROM users ORDER BY win_num DESC LIMIT 5");
$topWinners = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer les ratios
$ratioAttaque = ($totalPokemons > 0) ? ($attaqueInferieure / $totalPokemons) * 100 : 0;
$ratioDefense = ($totalPokemons > 0) ? ($defenseInferieure / $totalPokemons) * 100 : 0;
$ratioPv = ($totalPokemons > 0) ? ($pvInferieure / $totalPokemons) * 100 : 0;

?>

<div class="container mt-5">
    <!-- Section pour la sélection du Pokémon -->
    <div class="mb-4">
        <h2>Statistiques de votre Pokémon choisi:</h2>
        <p>Nom: <?php echo htmlspecialchars($pokemonData['name']['fr']); ?></p>
        <p>Attaque: <?php echo $attaque; ?></p>
        <p>Défense: <?php echo $defense; ?></p>
        <p>PV: <?php echo $pv; ?></p>
        <p>Ratio d'attaque (pourcentage de Pokémon avec une attaque inférieure): <?php echo number_format($ratioAttaque, 2); ?>%</p>
        <p>Ratio de défense (pourcentage de Pokémon avec une défense inférieure): <?php echo number_format($ratioDefense, 2); ?>%</p>
        <p>Ratio de PV (pourcentage de Pokémon avec des PV inférieurs): <?php echo number_format($ratioPv, 2); ?>%</p>
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
</div>

<script>
// Données pour les graphiques
const attackRatio = <?php echo $ratioAttaque; ?>;
const defenseRatio = <?php echo $ratioDefense; ?>;
const hpRatio = <?php echo $ratioPv; ?>;

// Fonction pour créer le graphique
function createDonutChart(ctx, label, data) {
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Inférieur', 'Supérieur ou Égal'],
            datasets: [{
                data: [data, 100 - data],
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

createDonutChart(attackRatioCtx, 'Ratio d\'Attaque', attackRatio);
createDonutChart(defenseRatioCtx, 'Ratio de Défense', defenseRatio);
createDonutChart(hpRatioCtx, 'Ratio de PV', hpRatio);

</script>

<?php include 'Admin/footer.php'; ?>
</body>
</html>
