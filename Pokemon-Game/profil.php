<?php
// Chemin vers le fichier de profil
$profilFile = 'profil.txt';

// Vérifier si le fichier existe, sinon le créer
if (!file_exists($profilFile)) {
    file_put_contents($profilFile, "Nombre de parties jouées: 0\nNombre de victoires: 0\nPokémon préféré: Aucun\nNombre de fois joué avec le Pokémon: 0\n");
}

// Lire le contenu du fichier de profil
$profilData = file($profilFile, FILE_IGNORE_NEW_LINES);
$profilStats = [];
foreach ($profilData as $line) {
    list($key, $value) = explode(': ', $line);
    $profilStats[$key] = $value;
}

// Calculer les ratios
$nombreParties = (int)$profilStats['Nombre de parties jouées'];
$nombreVictoire = (int)$profilStats['Nombre de victoires'];
$nombreFoisPokemon = (int)$profilStats['Nombre de fois joué avec le Pokémon'];

$ratioVictoire = $nombreParties > 0 ? ($nombreVictoire / $nombreParties) * 100 : 0;
$ratioPokemon = $nombreParties > 0 ? ($nombreFoisPokemon / $nombreParties) * 100 : 0;
?>
<script>
    function getColor(ratio) {
        if (ratio === 100) return '#4caf50'; // Vert foncé
        if (ratio === 0) return '#f44336'; // Rouge sang
        if (ratio === 50) return '#ffeb3b'; // Jaune
        // Interpolation entre rouge et vert
        const green = Math.round((ratio / 100) * 255);
        const red = 255 - green;
        return `rgb(${red}, ${green}, 0)`; // Couleur interpolée
    }

    // Afficher les ratios avec la couleur appropriée
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
    <?php include 'header.php'; ?>
    <div class="container mt-5">
        <h1 class="text-center">Profil du Joueur</h1>
        <div class="mt-4">
            <h3>Statistiques</h3>
            <ul class="list-group">
                <li class="list-group-item">Nombre de parties jouées: <?php echo $profilStats['Nombre de parties jouées']; ?></li>
                <li class="list-group-item">Nombre de victoires: <?php echo $profilStats['Nombre de victoires']; ?></li>
                <li class="list-group-item">Pokémon préféré: <?php echo $profilStats['Pokémon préféré']; ?></li>
                <li class="list-group-item">Nombre de fois joué avec le Pokémon: <?php echo $profilStats['Nombre de fois joué avec le Pokémon']; ?></li>
                <li class="list-group-item">Ratio de victoire: <span id="ratioVictoire"><?php echo round($ratioVictoire, 2); ?>%</span></li>
                <li class="list-group-item">Ratio de parties jouées avec le Pokémon préféré: <span id="ratioPokemon"><?php echo round($ratioPokemon, 2); ?>%</span></li>

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
        // Données pour le graphique des victoires
        const ctxVictoires = document.getElementById('victoiresChart').getContext('2d');
        const victoiresChart = new Chart(ctxVictoires, {
            type: 'doughnut',
            data: {
                labels: ['Victoires', 'Défaites'],
                datasets: [{
                    data: [<?php echo $nombreVictoire; ?>, <?php echo $nombreParties - $nombreVictoire; ?>],
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

        // Données pour le graphique des parties jouées avec le Pokémon préféré
        const ctxPokemon = document.getElementById('pokemonChart').getContext('2d');
        const pokemonChart = new Chart(ctxPokemon, {
            type: 'doughnut',
            data: {
                labels: ['Avec Pokémon Préféré', 'Autres'],
                datasets: [{
                    data: [<?php echo $nombreFoisPokemon; ?>, <?php echo $nombreParties - $nombreFoisPokemon; ?>],
                    backgroundColor: ['#2196f3', '#ff9800'],
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
