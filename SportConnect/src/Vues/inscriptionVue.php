<?php

namespace App\Vues;

use App\Controleur\NavbarControleur;

class InscriptionVue
{
    // Propriétés de la classe InscriptionVue
    private $titre;   // Le titre de la page, généralement "Inscription"

    private $messageReussite; // Message réussite
    private $erreurs; // Liste des erreurs éventuelles, par exemple, des erreurs de validation de formulaire

    /**
     * Constructeur de la classe InscriptionVue
     *
     * Initialise les propriétés `titre` et `erreurs`. Le titre est requis et représente le titre de la page.
     * Les erreurs sont optionnelles et définies par défaut comme un tableau vide.
     *
     * @param string $titre Le titre de la page (généralement "Inscription").
     * @param array $erreurs Liste des erreurs à afficher (par défaut, un tableau vide).
     */
    public function __construct($titre, $erreurs = [], $messageReussite = null)
    {
        $this->titre = $titre;
        $this->erreurs = is_array($erreurs) ? $erreurs : []; // Toujours initialiser comme tableau
        $this->messageReussite = $messageReussite;
    }




    /**
     * Affiche la vue d'inscription
     *
     * Cette méthode génère le code HTML de la page d'inscription, y compris :
     * - la barre de navigation dynamique
     * - le formulaire d'inscription
     * - l'affichage des erreurs de saisie
     * - le pied de page
     */
    public function afficher()
    {
        // Créer l'objet NavbarControleur pour obtenir les liens de navigation dynamiques
        $navbarControleur = new NavbarControleur();
        // Appel à la méthode obtenirLiens pour récupérer les liens de navigation en fonction de l'état de connexion
        $liensNavbar = $navbarControleur->obtenirLiens('inscriptions', $this->erreurs === null);

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
                    <a href="?page=accueil" class="logo">
                        <img src="assets/logo.png" alt="SportConnect Logo" class="navbar-logo"> <!-- Logo du site -->
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
                <p class="hero-subtitle">Rejoignez-nous et accédez à des événements exclusifs !</p> <!-- Sous-titre -->
            </header>

            <!-- Contenu principal : formulaire d'inscription -->
            <main>
                <div class="inscription-container">
                    <h2 class="inscription-title">Créer un compte</h2> <!-- Titre du formulaire d'inscription -->

                    <!-- Affichage du message de succès si l'inscription est réussie -->
                    <?php if (!empty($_SESSION['messageReussite'])): ?>
                        <div class="messageReussite">
                            <?php echo htmlspecialchars($_SESSION['messageReussite']); ?>
                        </div>
                        <?php unset($_SESSION['messageReussite']); // Supprime le message après affichage 
                        ?>
                    <?php endif; ?>



                    <!-- Affichage des erreurs (si présentes) -->
                    <?php if (!empty($this->erreurs) && is_array($this->erreurs)): ?>
                        <div class="erreurs">
                            <ul>
                                <?php foreach ($this->erreurs as $erreur): ?>
                                    <li><?php echo htmlspecialchars($erreur); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>



                    <!-- Formulaire d'inscription -->
                    <form action="?page=inscriptions&action=traiter" method="POST" class="inscription-form">
                        <!-- Champ pour le pseudo -->
                        <div class="form-group">
                            <label for="pseudo" class="form-label">Pseudo</label>
                            <input type="text" id="pseudo" name="pseudo" class="inscription-input" placeholder="Votre pseudo" value="<?php echo empty($this->erreurs) ? '' : htmlspecialchars($_POST['pseudo'] ?? ''); ?>" required>
                        </div>
                        <!-- Champ pour le prénom -->
                        <div class="form-group">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" id="prenom" name="prenom" class="inscription-input" placeholder="Votre prénom" value="<?php echo empty($this->erreurs) ? '' : htmlspecialchars($_POST['prenom'] ?? ''); ?>" required>
                        </div>
                        <!-- Champ pour l'email -->
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="inscription-input" placeholder="Votre email" value="<?php echo empty($this->erreurs) ? '' : htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                        <!-- Champ pour le mot de passe -->
                        <div class="form-group">
                            <label for="mot_de_passe" class="form-label">Mot de passe</label>
                            <input type="password" id="mot_de_passe" name="mot_de_passe" class="inscription-input" placeholder="Votre mot de passe" required>
                        </div>
                        <!-- Champ pour confirmer le mot de passe -->
                        <div class="form-group">
                            <label for="mot_de_passe_confirmation" class="form-label">Confirmation du mot de passe</label>
                            <input type="password" id="mot_de_passe_confirmation" name="mot_de_passe_confirmation" class="inscription-input" placeholder="Veuillez répéter votre mot de passe" required>
                        </div>
                        <!-- Case à cocher PMR (Personne à mobilité réduite) -->
                        <div class="form-group-checkbox">
                            <label class="inscription-label">
                                <input type="checkbox" name="pmr" class="inscription-checkbox" <?php echo isset($_POST['pmr']) && !empty($this->erreurs) ? 'checked' : ''; ?>> PMR (Personne à mobilité réduite)
                            </label>
                        </div>
                        <!-- Bouton pour soumettre le formulaire -->
                        <button type="submit" class="inscription-button">S'inscrire</button>
                    </form>
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
