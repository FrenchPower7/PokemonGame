<?php 
session_start(); // Démarrer la session avant toute sortie HTML
$title = 'Pokemon Game';
include 'ADMIN/head.php'; 
?>

<body>

    <!-- Inclusion du header -->
    <?php include 'ADMIN/header.php'; ?>

    <div class="container mt-5 text-center">
        
        <?php
        // Affichage des erreurs s'il y en a
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']); // Effacer le message d'erreur après affichage
        }
        ?>

        <?php
        // Vérifier si l'utilisateur est connecté
        if (isset($_SESSION['user_id'])) {
            echo '<h2>Bienvenue, ' . htmlspecialchars($_SESSION['username']) . '!</h2>';
            echo '<p class="lead">Vous êtes connecté. Prêt à commencer le jeu ?</p>';
            echo '<a href="game.php" class="btn btn-primary btn-lg">Commencer le jeu</a>';
            echo '<br><br>';
            echo '<a href="V2/logout.php" class="btn btn-danger">Se déconnecter</a>';
        } else {
            // Si l'utilisateur n'est pas connecté, afficher les formulaires de connexion et d'inscription
        ?>

        <div class="row justify-content-center">
            <div class="col-md-5">
                <!-- Formulaire de connexion -->
                <h2>Connexion</h2>
                <form method="post" action="V2/login.php">
                    <div class="mb-3">
                        <label for="login_username" class="form-label">Nom d'utilisateur :</label>
                        <input type="text" class="form-control" id="login_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="login_mdp" class="form-label">Mot de passe :</label>
                        <input type="password" class="form-control" id="login_mdp" name="mdp" required>
                    </div>
                    <button type="submit" class="btn btn-success">Se connecter</button>
                </form>
            </div>

            <div class="col-md-1 d-flex align-items-center justify-content-center">
                <div class="vr" style="height: 200px;"></div>
            </div>

            <div class="col-md-5">
                <!-- Formulaire d'inscription -->
                <h2>Inscription</h2>
                <form method="post" action="V2/register.php">
                    <div class="mb-3">
                        <label for="register_username" class="form-label">Nom d'utilisateur :</label>
                        <input type="text" class="form-control" id="register_username" name="username" required minlength="3" maxlength="32">
                    </div>
                    <div class="mb-3">
                        <label for="register_mdp" class="form-label">Mot de passe :</label>
                        <input type="password" class="form-control" id="register_mdp" name="mdp" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="register_mdp_confirm" class="form-label">Confirmer le mot de passe :</label>
                        <input type="password" class="form-control" id="register_mdp_confirm" name="mdp_confirm" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary">S'inscrire</button>
                </form>
            </div>
        </div>

        <?php
        }
        ?>

    </div>

    <footer class="mt-5">
        <div class="container text-center">
            <p>&copy; 2024 FrenchPower7 Game. Tous droits réservés.</p>
        </div>
    </footer>

</body>
</html>
