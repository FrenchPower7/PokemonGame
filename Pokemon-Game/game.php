<?php
// Chemin vers le fichier de profil
$profilFile = 'profil.txt';

// Fonction pour mettre à jour les statistiques dans le fichier de profil
function updateProfilStats($result, $pokemonJoueurName) {
    global $profilFile;

    // Lire le contenu actuel du fichier
    $profilData = file($profilFile, FILE_IGNORE_NEW_LINES);
    $profilStats = [];
    foreach ($profilData as $line) {
        list($key, $value) = explode(': ', $line);
        $profilStats[$key] = $value;
    }

    // Mettre à jour les statistiques
    $profilStats['Nombre de parties jouées'] = intval($profilStats['Nombre de parties jouées']) + 1;
    if ($result === "Gagné") {
        $profilStats['Nombre de victoires'] = intval($profilStats['Nombre de victoires']) + 1;
    }

    // Mettre à jour le Pokémon préféré
    if ($profilStats['Pokémon préféré'] === "Aucun" || $profilStats['Nombre de fois joué avec le Pokémon'] == 0) {
        $profilStats['Pokémon préféré'] = $pokemonJoueurName;
    }
    $profilStats['Nombre de fois joué avec le Pokémon'] = intval($profilStats['Nombre de fois joué avec le Pokémon']) + 1;

    // Écrire les nouvelles statistiques dans le fichier
    $newContent = "";
    foreach ($profilStats as $key => $value) {
        $newContent .= "$key: $value\n";
    }
    file_put_contents($profilFile, $newContent);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeu - Pokémon Game</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .pokemon-card {
            border: 1px solid #007bff;
            border-radius: 10px;
            padding: 10px;
            margin: 10px;
            background-color: #f8f9fa;
            display: inline-block;
            width: 150px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .pokemon-card:hover {
            transform: scale(1.05);
        }
        .pokemon-img {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>

    <?php include 'header.html'; ?>

    <div class="container mt-5">
        <h1 class="text-center">Votre Pokémon</h1>
        <div class="row">
            <div class="col-md-6">
                <div id="pokemonJoueurCard" class="pokemon-card text-center">
                    <h3>Votre Pokémon</h3>
                    <img id="pokemonJoueurImage" class="pokemon-img" src="" alt="Votre Pokémon" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="pokemon-card text-center">
                    <h3>Adversaire</h3>
                    <img id="adversaireImage" class="pokemon-img" src="" alt="Adversaire" />
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <button id="btnAttaque" class="btn btn-danger" disabled>⚔️ Attaque ⚔️</button>
            <button id="btnDefense" class="btn btn-warning" disabled>🛡️ Défense 🛡️</button>
            <button id="btnPV" class="btn btn-success" disabled>🌿 PV 🌿</button>
            <button id="btnQuitter" class="btn btn-secondary" style="display:none;">Quitter</button>
            <button id="btnRecommencer" class="btn btn-primary" style="display:none;">Recommencer</button>
        </div>

        <div class="mt-5">
            <h3>Récapitulatif des manches</h3>
            <ul id="recapitulatifManches" class="list-group"></ul>
        </div>
    </div>

    <script>
        const pokemonData = [];
        let manchesGagnees = [];
        let currentManche = 0;
        let pokemonJoueur;

        // Vérifier si un Pokémon a déjà été sélectionné
        const urlParams = new URLSearchParams(window.location.search);
        const pokemonId = urlParams.get('pokemonId');

        if (!pokemonId) {
            window.location.href = 'selection.php'; // Rediriger vers la sélection si aucun Pokémon n'est choisi
        }

        async function fetchPokemons() {
            const response = await fetch('https://tyradex.vercel.app/api/v1/pokemon');
            const data = await response.json();
            data.forEach(pokemon => {
                if (pokemon.stats) {
                    pokemonData.push({
                        id: pokemon.pokedex_id,
                        name: pokemon.name.fr,
                        attaque: pokemon.stats.atk || 0,
                        defense: pokemon.stats.def || 0,
                        pv: pokemon.stats.hp || 0,
                        image: pokemon.sprites.regular
                    });
                } else {
                    console.warn(`Les stats pour le Pokémon ID ${pokemon.pokedex_id} sont manquantes.`);
                }
            });
            afficherPokemon();
        }

        function afficherPokemon() {
            pokemonJoueur = pokemonData.find(p => p.id == pokemonId);
            if (pokemonJoueur) {
                document.getElementById("pokemonJoueurImage").src = pokemonJoueur.image;
                genererAdversaire(); // Générer le premier adversaire
            } else {
                console.error("Aucun Pokémon trouvé avec l'ID spécifié.");
            }
        }

        async function genererAdversaire() {
            const randomIndex = Math.floor(Math.random() * pokemonData.length);
            const adversaire = pokemonData[randomIndex];
            document.getElementById("adversaireImage").src = adversaire.image;

            setupBoutons(adversaire);
        }

        function setupBoutons(adversaire) {
            document.getElementById("btnAttaque").onclick = () => jouerManche('attaque', pokemonJoueur, adversaire);
            document.getElementById("btnDefense").onclick = () => jouerManche('defense', pokemonJoueur, adversaire);
            document.getElementById("btnPV").onclick = () => jouerManche('pv', pokemonJoueur, adversaire);
            document.getElementById("btnAttaque").disabled = false;
            document.getElementById("btnDefense").disabled = false;
            document.getElementById("btnPV").disabled = false;
        }

        function jouerManche(statChoisie, pokemonJoueur, adversaire) {
            const result = pokemonJoueur[statChoisie] >= adversaire[statChoisie] ? "Gagné" : "Perdu";
            
            // Ajouter le résultat de la manche
            manchesGagnees.push(`${result} - (${statChoisie.charAt(0).toUpperCase() + statChoisie.slice(1)})<br>
                ${pokemonJoueur.name} (Toi) - ${pokemonJoueur[statChoisie]} VS ${adversaire.name} - ${adversaire[statChoisie]}`);
            
            // Mettre à jour les statistiques dans profil.txt
            updateProfileStats(result, pokemonJoueur.name);

            currentManche++;
            if (currentManche < 3) {
                genererAdversaire(); // Générer un nouvel adversaire
            } else {
                afficherRecapitulatif();
            }
        }

        function updateProfileStats(result, pokemonName) {
            fetch('updateProfile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ result, pokemonName })
            });
        }

        function afficherRecapitulatif() {
            const recap = document.getElementById("recapitulatifManches");
            recap.innerHTML = "";
            manchesGagnees.forEach((manche) => {
                const listItem = document.createElement("li");
                listItem.classList.add("list-group-item");
                listItem.innerHTML = manche; // Utiliser innerHTML pour afficher le texte formaté
                recap.appendChild(listItem);
            });

            // Bloquer les boutons
            document.getElementById("btnAttaque").disabled = true;
            document.getElementById("btnDefense").disabled = true;
            document.getElementById("btnPV").disabled = true;

            // Afficher les boutons Quitter et Recommencer
            document.getElementById("btnQuitter").style.display = "inline-block";
            document.getElementById("btnRecommencer").style.display = "inline-block";
        }

        document.getElementById("btnQuitter").onclick = () => {
            window.location.href = 'index.php'; // Rediriger vers la page d'accueil ou une autre page
        };

        document.getElementById("btnRecommencer").onclick = () => {
            location.reload(); // Recharger la page pour recommencer
        };

        // Initialisation
        fetchPokemons();
    </script>

</body>
</html>
