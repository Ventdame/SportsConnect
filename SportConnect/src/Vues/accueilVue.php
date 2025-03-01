<?php

namespace App\Vues;

// Importation du contrôleur NavbarControleur pour gérer les liens de la barre de navigation
use App\Controleur\NavbarControleur;

class AccueilVue
{
    // Propriétés privées de la classe
    private $titre;       // Titre de la page
    private $sports;      // Liste des sports à afficher dans la vue
    private $utilisateur; // Informations de l'utilisateur connecté (si connecté)

    /**
     * Constructeur de la classe AccueilVue
     *
     * Initialise le titre de la page, la liste des sports, et les informations de l'utilisateur (optionnelles).
     *
     * @param string $titre Le titre de la page.
     * @param array $sports Liste des sports disponibles (par défaut, tableau vide).
     * @param array|null $utilisateur Informations de l'utilisateur connecté (par défaut, null).
     */
    public function __construct($titre, $sports = [], $utilisateur = null)
    {
        $this->titre = $titre;              // Initialisation du titre de la page
        $this->sports = $sports;            // Initialisation de la liste des sports
        $this->utilisateur = $utilisateur; // Initialisation des informations de l'utilisateur
    }

    /**
     * Affiche la vue Accueil
     *
     * Cette méthode génère la page HTML complète pour l'accueil, incluant la barre de navigation, le contenu principal
     * avec les sports disponibles, et le pied de page. La barre de navigation varie en fonction de si l'utilisateur est connecté.
     */
    public function afficher()
    {
        // Créer l'objet NavbarControleur pour récupérer les liens de navigation dynamiques
        $navbarControleur = new NavbarControleur();
        // Récupération des liens de navigation en fonction de la page active et de l'état de connexion de l'utilisateur
        $liensNavbar = $navbarControleur->obtenirLiens('accueil', $this->utilisateur !== null);

?>
        <!DOCTYPE html>
        <html lang="fr">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($this->titre); ?></title> <!-- Affichage dynamique du titre -->
            <link rel="stylesheet" href="CSS/style.css"> <!-- Lien vers la feuille de style CSS -->
        </head>

        <body>
            <!-- Navbar : Barre de navigation affichée en haut de la page -->
            <nav class="navbar">
                <div class="container">
                    <a href="?page=accueil" class="logo">
                        <img src="assets/logo.png" alt="SportConnect Logo" class="navbar-logo"> <!-- Logo du site -->
                        <span>SportConnect</span>
                    </a>
                    <ul class="nav-links">
                        <!-- Parcours des liens de navigation générés dynamiquement -->
                        <?php foreach ($liensNavbar as $nom => $lien): ?>
                            <li>
                                <!-- Affichage de chaque lien avec une classe 'active' si c'est le lien actuel -->
                                <a href="<?php echo htmlspecialchars($lien['url']); ?>"
                                    class="<?php echo $lien['actif'] ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($nom); ?> <!-- Affichage du nom du lien -->
                                </a>
                            </li>
                        <?php endforeach; ?>

                        <!-- Si l'utilisateur est connecté, affichage de son pseudo -->
                        <?php if ($this->utilisateur): ?>
                            <li class="user-info">Bienvenue, <?php echo htmlspecialchars($this->utilisateur['pseudo']); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>

            <!-- Header : Section en haut de la page avec le titre et une description -->
            <header class="hero">
                <h1><?php echo htmlspecialchars($this->titre); ?></h1> <!-- Affichage du titre dynamique -->
                <p>Découvrez les sports disponibles et participez à nos événements !</p> <!-- Sous-titre de la page -->
            </header>


            <!-- Contenu principal de la page -->
            <main id="sports-section">
                <section class="sports-section">
                    <h2>Sports disponibles</h2>
                    <!-- Si des sports sont disponibles, les afficher sous forme de cartes -->
                    <?php if (!empty($this->sports)): ?>
                        <div class="sports-list">
                            <?php foreach ($this->sports as $sport): ?>
                                <div class="sport-card">
                                    <!-- Affichage de l'image du sport, générée dynamiquement avec un nom de fichier correspondant -->
                                    <img src="assets/sports/<?php echo strtolower(str_replace(' ', '_', $sport)); ?>.png" alt="<?php echo htmlspecialchars($sport); ?>" class="sport-image">
                                    <h3 class="sport-title"><?php echo htmlspecialchars($sport); ?></h3> <!-- Affichage du nom du sport -->
                                    <!-- Lien vers la page de réservation du sport -->
                                    <a href="?page=reservation&sport=<?php echo urlencode($sport); ?>" class="cta-button">Explorer les événements</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Aucun sport disponible pour le moment.</p> <!-- Message si aucun sport n'est disponible -->
                    <?php endif; ?>
                </section>
            </main>

            <!-- Footer : Section en bas de la page avec le copyright -->
            <footer class="footer">
                <p>&copy; 2024 SportConnect - Tous droits réservés, fais avec amour ❤</p>
            </footer>

            
           <!-- A implementer dans script.js juste pour essai -->
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const toggleCheckbox = document.querySelector(".cb");
                    const sportsSection = document.getElementById("sports-section");

                    toggleCheckbox.addEventListener("change", function() {
                        if (this.checked) {
                            sportsSection.style.display = "block";
                        } else {
                            sportsSection.style.display = "none";
                        }
                    });

                    // Initialiser l'affichage selon l'état du toggle
                    if (!toggleCheckbox.checked) {
                        sportsSection.style.display = "none";
                    }
                });
            </script>
        </body>

        </html>
<?php
    }
}
