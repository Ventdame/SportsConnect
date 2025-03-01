<?php
namespace App\Vues;

use App\Controleur\NavbarControleur;

class ConnexionVue
{
    // Propriétés privées de la classe ConnexionVue
    private $titre;   // Le titre de la page
    private $erreurs; // Liste des erreurs (par exemple, lors de la connexion)

    /**
     * Constructeur de la classe ConnexionVue
     *
     * Initialise les propriétés avec les paramètres reçus.
     * Le titre est requis, tandis que les erreurs sont optionnelles.
     *
     * @param string $titre Le titre de la page (généralement "Connexion").
     * @param array $erreurs Liste des erreurs (par défaut, un tableau vide).
     */
    public function __construct($titre, $erreurs = [])
    {
        $this->titre = $titre;   // Initialise le titre de la page
        $this->erreurs = $erreurs; // Initialise les erreurs (si présentes)
    }

    /**
     * Affiche la vue de connexion
     *
     * Cette méthode génère le HTML pour afficher la page de connexion,
     * y compris la barre de navigation, les erreurs (le cas échéant),
     * le formulaire de connexion, et le pied de page.
     */
    public function afficher()
    {
        // Créer un objet NavbarControleur pour obtenir les liens de navigation dynamiques
        $navbarControleur = new NavbarControleur();
        // Appel à la méthode obtenirLiens pour récupérer les liens de navigation en fonction de l'état de connexion
        $liensNavbar = $navbarControleur->obtenirLiens('connexion', $this->erreurs === null);

        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8"> <!-- Définit le jeu de caractères utilisé -->
            <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Permet un affichage responsive -->
            <title><?php echo htmlspecialchars($this->titre); ?></title> <!-- Affiche le titre de la page -->
            <link rel="stylesheet" href="CSS/style.css"> <!-- Lien vers le fichier de style CSS -->
        </head>
        <body>
            <!-- Navbar : barre de navigation en haut de la page -->
            <nav class="navbar">
                <div class="container">
                    <!-- Logo du site -->
                    <a href="?page=accueil" class="logo">
                        <img src="assets/logo.png" alt="SportConnect Logo" class="navbar-logo">
                        <span>SportConnect</span> <!-- Nom du site -->
                    </a>
                    <ul class="nav-links">
                        <!-- Boucle sur les liens de la barre de navigation pour afficher chaque élément -->
                        <?php foreach ($liensNavbar as $nom => $lien): ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($lien['url']); ?>"
                                   class="<?php echo $lien['actif'] ? 'active' : ''; ?>"> <!-- Classe active si le lien est actuellement actif -->
                                    <?php echo htmlspecialchars($nom); ?> <!-- Affichage du nom du lien -->
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </nav>

            <!-- Header : section contenant le titre de la page -->
            <header class="hero">
                <h1 class="hero-title"><?php echo htmlspecialchars($this->titre); ?></h1> <!-- Affichage du titre de la page -->
                <p class="hero-subtitle">Connectez-vous pour accéder à votre profil et réserver vos événements sportifs !</p> <!-- Sous-titre -->
            </header>

            <!-- Contenu principal : formulaire de connexion -->
            <main>
                <div class="connexion-container">
                    <h2 class="connexion-title">Se connecter</h2> <!-- Titre du formulaire de connexion -->

                    <!-- Affichage des erreurs (si présentes) -->
                    <?php if (!empty($this->erreurs)): ?>
                        <div class="erreurs">
                            <ul>
                                <!-- Affichage de chaque erreur dans une liste -->
                                <?php foreach ($this->erreurs as $erreur): ?>
                                    <li class="erreur-item"><?php echo htmlspecialchars($erreur); ?></li> <!-- Affichage de l'erreur -->
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Formulaire de connexion -->
                    <form action="?page=connexion&action=traiterConnexion" method="POST" class="connexion-form">
                        <input type="email" name="email" class="connexion-input" placeholder="Email ou pseudo" required> <!-- Champ pour l'email -->
                        <input type="password" name="mot_de_passe" class="connexion-input" placeholder="Mot de passe" required> <!-- Champ pour le mot de passe -->
                        <button type="submit" class="connexion-button">Se connecter</button> <!-- Bouton de soumission -->
                    </form>

                    <!-- Lien vers la page d'inscription si l'utilisateur n'a pas encore de compte -->
                    <p class="inscription-link">
                        Pas encore inscrit ? <a href="?page=inscriptions">Créez un compte ici</a> <p>Mot de passe oublié</p> .
                    </p>
                </div>
            </main>

            <!-- Footer : section en bas de la page -->
            <footer class="footer">
                <p>&copy; 2024 SportConnect - Tous droits réservés, fais avec amour ❤</p> <!-- Copyright -->
            </footer>
        </body>
        </html>
        <?php
    }
}
