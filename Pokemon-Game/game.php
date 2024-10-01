<?php
session_start();
include 'ADMIN/BDD.php';

// Vérifier si un Pokémon a été sélectionné
if (!isset($_GET['pokemonId'])) {
    header('Location: selection.php'); // Rediriger vers la sélection si aucun Pokémon n'est choisi
    exit;
}

$pokemonId = $_GET['pokemonId']; // Récupérer l'ID du Pokémon choisi

// Récupérer les informations de l'utilisateur
$user_id = $_SESSION['user_id'];
$query = $bdd->prepare('SELECT pseudo, game_num, win_num, fav FROM users WHERE id = :user_id');
$query->execute(['user_id' => $user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

// Charger le Pokémon choisi via l'API externe
$pokemon_api_url = 'https://tyradex.vercel.app/api/v1/pokemon/' . $pokemonId;
$pokemon_data = file_get_contents($pokemon_api_url);
$pokemonJoueur = json_decode($pokemon_data, true);

if (!$pokemonJoueur) {
    // Si aucune donnée de Pokémon n'est trouvée, rediriger vers la sélection
    header('Location: selection.php');
    exit;
}

// Récupérer les stats du Pokémon choisi
$attaque = isset($pokemonJoueur['stats']['atk']) ? $pokemonJoueur['stats']['atk'] : 0;
$defense = isset($pokemonJoueur['stats']['def']) ? $pokemonJoueur['stats']['def'] : 0;
$pv = isset($pokemonJoueur['stats']['hp']) ? $pokemonJoueur['stats']['hp'] : 0;

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

        .recap-item {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease-out, transform 0.5s ease-out;
        }

        .recap-item.show {
            opacity: 1;
            transform: translateY(0);
        }

        .btn-disabled {
            background-color: #6c757d; /* Couleur grise */
            border-color: #6c757d; /* Couleur grise pour la bordure */
            color: white; /* Couleur du texte */
            cursor: not-allowed; /* Curseur en mode "non autorisé" */
        }
    </style>
</head>
<body>

    <?php include 'ADMIN/header.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center">Bienvenue, <?php echo htmlspecialchars($user['pseudo']); ?> !</h1>
        <p class="text-center">Parties jouées : <?php echo $user['game_num']; ?> | Victoires : <?php echo $user['win_num']; ?></p>

        <div class="row">
            <div class="col-md-6">
                <div id="pokemonJoueurCard" class="pokemon-card text-center">
                    <h3>Votre Pokémon</h3>
                    <img class="pokemon-img" src="<?php echo htmlspecialchars($pokemonJoueur['sprites']['regular']); ?>" alt="<?php echo htmlspecialchars($pokemonJoueur['name']['fr']); ?>" />
                    <p><?php echo htmlspecialchars($pokemonJoueur['name']['fr']); ?></p>
                    <p>Attaque: <?php echo $attaque; ?></p>
                    <p>Défense: <?php echo $defense; ?></p>
                    <p>PV: <?php echo $pv; ?></p>
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
        let pokemonJoueur = {
            name: "<?php echo htmlspecialchars($pokemonJoueur['name']['fr']); ?>",
            attaque: <?php echo $attaque; ?>,
            defense: <?php echo $defense; ?>,
            pv: <?php echo $pv; ?>
        };
        const pokemonId = "<?php echo $pokemonId; ?>"; // Définir pokemonId ici

        async function fetchPokemons() {
            const response = await fetch('https://tyradex.vercel.app/api/v1/pokemon');
            const data = await response.json();
            return data.map(pokemon => ({
                id: pokemon.pokedex_id,
                name: pokemon.name.fr,
                attaque: pokemon.stats ? pokemon.stats.atk || 0 : 0,
                defense: pokemon.stats ? pokemon.stats.def || 0 : 0,
                pv: pokemon.stats ? pokemon.stats.hp || 0 : 0,
                image: pokemon.sprites.regular
            }));
        }

        async function genererAdversaire() {
            const pokemons = await fetchPokemons();
            const randomIndex = Math.floor(Math.random() * pokemons.length);
            const adversaire = pokemons[randomIndex];
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

            manchesGagnees.push(`${result} - (${statChoisie.charAt(0).toUpperCase() + statChoisie.slice(1)})<br>
${pokemonJoueur.name} (Toi) - ${pokemonJoueur[statChoisie]} VS ${adversaire.name} - ${adversaire[statChoisie]}`);

            currentManche++;
            if (currentManche < 3) {
                genererAdversaire(); 
            } else {
                afficherRecapitulatif();
            }
        }

        function afficherRecapitulatif() {
            const recap = document.getElementById("recapitulatifManches");
            recap.innerHTML = "";
            let victoires = 0; // Compteur de victoires
            const nombreDeCombats = 3; // Toujours 3 combats

            manchesGagnees.forEach((manche) => {
                const listItem = document.createElement("li");
                listItem.className = "list-group-item recap-item";
                listItem.innerHTML = manche;
                recap.appendChild(listItem);
                // Compter les victoires
                if (manche.startsWith("Gagné")) {
                    victoires++;
                }
            });

            // Affichage du résultat final
            const resultatFinal = document.createElement("h4");
            resultatFinal.innerHTML = `Résultat Final: ${victoires} / ${nombreDeCombats} manches gagnées`;
            recap.appendChild(resultatFinal);

            // Animation des éléments du récapitulatif
            setTimeout(() => {
                const items = document.querySelectorAll('.recap-item');
                items.forEach((item, index) => {
                    setTimeout(() => {
                        item.classList.add('show');
                    }, index * 200); // Délai pour chaque élément
                });
            }, 100);

            // Désactiver les boutons
            document.getElementById("btnAttaque").classList.add('btn-disabled');
            document.getElementById("btnDefense").classList.add('btn-disabled');
            document.getElementById("btnPV").classList.add('btn-disabled');
            document.getElementById("btnAttaque").disabled = true;
            document.getElementById("btnDefense").disabled = true;
            document.getElementById("btnPV").disabled = true;

            // Affichage des boutons "Recommencer" et "Quitter"
            document.getElementById("btnRecommencer").style.display = "inline-block";
            document.getElementById("btnQuitter").style.display = "inline-block";

            // Envoi des données au serveur
            updateProfile(victoires, nombreDeCombats, pokemonId);

        }




        async function updateProfile(victoires, nombreDeCombats, idpokemon, user) {
            try {
                const response = await fetch('updateProfile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        victoires: victoires,
                        combats: nombreDeCombats,
                        idpokemon: idpokemon,
                        user: user // Ajout du nom d'utilisateur ici si nécessaire
                    })
                });

                if (!response.ok) {
                    throw new Error('Erreur lors de la mise à jour du profil');
                }

                const data = await response.json();
                console.log(data.message); // Afficher un message de succès ou d'erreur
            } catch (error) {
                console.error('Erreur:', error);
            }
        }






        function recommencer() {
            // Réinitialiser les variables
            manchesGagnees = [];
            currentManche = 0;

            // Réinitialiser l'affichage
            document.getElementById("recapitulatifManches").innerHTML = "";
            document.getElementById("btnRecommencer").style.display = "none";
            document.getElementById("btnQuitter").style.display = "none";

            // Réactiver les boutons et les rendre actifs
            document.getElementById("btnAttaque").classList.remove('btn-disabled');
            document.getElementById("btnDefense").classList.remove('btn-disabled');
            document.getElementById("btnPV").classList.remove('btn-disabled');
            document.getElementById("btnAttaque").disabled = false;
            document.getElementById("btnDefense").disabled = false;
            document.getElementById("btnPV").disabled = false;

            // Régénérer l'adversaire
            genererAdversaire();
        }

        function quitter() {
            window.location.href = "index.php"; // Rediriger vers la page d'accueil ou la page souhaitée
        }

        document.getElementById("btnRecommencer").onclick = recommencer;
        document.getElementById("btnQuitter").onclick = quitter;

        window.onload = () => {
            genererAdversaire();
        };
    </script>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
