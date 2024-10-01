<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$userId = $_SESSION['user_id'];
include 'Admin/BDD.php'; // Connexion à la base de données

// Si un Pokémon est sélectionné
if (isset($_GET['pokemonId'])) {
    $pokemonId = (int)$_GET['pokemonId'];
    
    // Mettre à jour le Pokémon favori dans la base de données
    $updateFavStmt = $bdd->prepare("UPDATE users SET fav = :fav WHERE id = :user_id");
    $updateFavStmt->bindParam(':fav', $pokemonId, PDO::PARAM_INT);
    $updateFavStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $updateFavStmt->execute();

    // Rediriger vers le profil après la mise à jour
    header("Location: profil.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sélectionner un Pokémon</title>
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

    <?php include 'ADMIN/header.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center">Choisissez votre Pokémon favori</h1>

        <!-- Barre de recherche -->
        <div class="text-center mb-4">
            <input type="text" id="searchBar" class="form-control" placeholder="Rechercher un Pokémon..." onkeyup="filtrerPokemon()">
        </div>

        <!-- Section pour afficher les Pokémon -->
        <div id="pokemonSelection" class="text-center mb-4"></div>
    </div>

    <script>
        const pokemonData = [];

        async function fetchPokemons() {
            const response = await fetch('https://tyradex.vercel.app/api/v1/pokemon');
            const data = await response.json();
            data.forEach(pokemon => {
                pokemonData.push({
                    id: pokemon.pokedex_id, // Utiliser l'ID pokédex
                    name: pokemon.name.fr, // Nom en français
                    image: pokemon.sprites.regular // Image du sprite
                });
            });
            afficherPokemon(pokemonData); // Affiche tous les Pokémon initialement
        }

        function afficherPokemon(pokemonList) {
            const selection = document.getElementById("pokemonSelection");
            selection.innerHTML = ""; // Vide la sélection avant d'afficher les nouveaux Pokémon
            pokemonList.forEach(pokemon => {
                const card = document.createElement("div");
                card.classList.add("pokemon-card", "m-2");
                card.onclick = () => choisirPokemon(pokemon.id);

                const img = document.createElement("img");
                img.src = pokemon.image;
                img.alt = pokemon.name;
                img.classList.add("pokemon-img");

                const name = document.createElement("p");
                name.innerText = pokemon.name;

                card.appendChild(img);
                card.appendChild(name);
                selection.appendChild(card);
            });
        }

        function choisirPokemon(id) {
            // Rediriger vers cette même page pour mettre à jour le Pokémon favori dans la base de données
            window.location.href = `pokemon_fav_selector.php?pokemonId=${id}`;
        }

        // Fonction pour filtrer les Pokémon en fonction de la recherche
        function filtrerPokemon() {
            const searchTerm = document.getElementById("searchBar").value.toLowerCase();
            const filteredPokemon = pokemonData.filter(pokemon => pokemon.name.toLowerCase().includes(searchTerm));
            afficherPokemon(filteredPokemon); // Affiche uniquement les Pokémon filtrés
        }

        // Initialisation
        fetchPokemons();
    </script>

</body>
</html>
