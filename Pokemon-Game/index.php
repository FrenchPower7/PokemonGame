<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Pokémon Game</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <!-- Inclusion du header -->
    <?php include 'header.php'; ?>

    <div class="container mt-5 text-center">
        <h1>Bienvenue dans le Pokémon Game !</h1>
        <p class="lead">Affrontez d'autres Pokémon et montrez votre talent en choisissant la meilleure stat !</p>
        <p>Êtes-vous prêt à relever le défi ?</p>
        <a href="game.php" class="btn btn-primary btn-lg">Commencer le jeu</a>
    </div>

    <footer class="mt-5">
        <div class="container text-center">
            <p>&copy; 2024 FrenchPower7 Game. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
