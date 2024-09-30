<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$userId = $_SESSION['user_id'];

try {
    // Connexion à la base de données MySQL
    $db = new PDO('mysql:host=localhost;dbname=pokemon;charset=utf8', 'root', 'root');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête pour récupérer les statistiques de l'utilisateur
    // Modification de fav_game_win à fav_game_num
    $stmt = $db->prepare("SELECT pseudo, game_num, win_num, fav, fav_game_num FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    // Récupération des données utilisateur
    $profilStats = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profilStats) {
        throw new Exception("Utilisateur non trouvé.");
    }

    // Calculs des ratios
    $gameNum = (int)$profilStats['game_num'];
    $winNum = (int)$profilStats['win_num'];
    $favGameNum = (int)$profilStats['fav_game_num']; // Utilisation du nombre de jeux avec le Pokémon préféré

    $ratioVictoire = $gameNum > 0 ? ($winNum / $gameNum) * 100 : 0;
    $ratioPokemon = $gameNum > 0 ? ($favGameNum / $gameNum) * 100 : 0; // Modification ici pour le ratio

} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
    exit();
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}
?>

<script>
    function getColor(ratio) {
        if (ratio === 100) return '#4caf50'; // Vert foncé
        if (ratio === 0) return '#f44336'; // Rouge sang
        if (ratio === 50) return '#ffeb3b'; // Jaune
        const green = Math.round((ratio / 100) * 255);
        const red = 255 - green;
        return `rgb(${red}, ${green}, 0)`; // Couleur interpolée
    }

    document.addEventListener("DOMContentLoaded", function() {
        const ratioVictoireElement = document.getElementById('ratioVictoire');
        const ratioPokemonElement = document.getElementById('ratioPokemon');

        const ratioVictoireColor = getColor(<?php echo round($ratioVictoire, 2); ?>);
        const ratioPokemonColor = getColor(<?php echo round($ratioPokemon, 2); ?>);

        ratioVictoireElement.style.color = ratioVictoireColor;
        ratioPokemonElement.style.color = ratioPokemonColor;
    });
</script>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Pokémon Game</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'ADMIN/header.php'; ?>
    <div class="container mt-5">
        <h1 class="text-center">Profil du Joueur - <?php echo htmlspecialchars($profilStats['pseudo']); ?></h1>
        <div class="mt-4">
            <h3>Statistiques</h3>
            <ul class="list-group">
                <li class="list-group-item">Nombre de parties jouées: <?php echo $profilStats['game_num']; ?></li>
                <li class="list-group-item">Nombre de victoires: <?php echo $profilStats['win_num']; ?></li>
                <li class="list-group-item">Pokémon préféré (ID): <?php echo htmlspecialchars($profilStats['fav']); ?></li>
                <li class="list-group-item">Nombre de parties jouées avec le Pokémon préféré: <?php echo $profilStats['fav_game_num']; ?></li> <!-- Modification ici -->
                <li class="list-group-item">Ratio de victoires: <span id="ratioVictoire"><?php echo round($ratioVictoire, 2); ?>%</span></li>
                <li class="list-group-item">Ratio de parties gagnées avec le Pokémon préféré: <span id="ratioPokemon"><?php echo round($ratioPokemon, 2); ?>%</span></li>
            </ul>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <h3>Ratio Victoires/Parties Jouées</h3>
                <canvas id="victoiresChart"></canvas>
            </div>
            <div class="col-md-6">
                <h3>Ratio Parties Jouées avec le Pokémon Préféré</h3>
                <canvas id="pokemonChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const ctxVictoires = document.getElementById('victoiresChart').getContext('2d');
        const victoiresChart = new Chart(ctxVictoires, {
            type: 'doughnut',
            data: {
                labels: ['Victoires', 'Défaites'],
                datasets: [{
                    data: [<?php echo $winNum; ?>, <?php echo $gameNum - $winNum; ?>],
                    backgroundColor: ['#4caf50', '#f44336'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ' + context.raw;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });

        const ctxPokemon = document.getElementById('pokemonChart').getContext('2d');
        const pokemonChart = new Chart(ctxPokemon, {
            type: 'doughnut',
            data: {
                labels: ['Avec Pokémon Préféré', 'Autres'],
                datasets: [{
                    data: [<?php echo $favGameNum; ?>, <?php echo $gameNum - $favGameNum; ?>], // Modification ici
                    backgroundColor: ['#4caf50', '#f44336'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ' + context.raw;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
