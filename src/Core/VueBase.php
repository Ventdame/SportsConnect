<?php

namespace App\Core;

use App\Controleur\NavbarControleur;

/**
 * Classe VueBase
 * 
 * Classe de base pour toutes les vues de l'application.
 * Fournit des fonctionnalités communes à toutes les vues.
 */
abstract class VueBase
{
    /**
     * Titre de la page
     * 
     * @var string
     */
    protected $titre;
    
    /**
     * Données à afficher dans la vue
     * 
     * @var array
     */
    protected $donnees;
    
    /**
     * Liste des erreurs à afficher
     * 
     * @var array
     */
    protected $erreurs;

    /**
     * Utilisateur connecté
     * 
     * @var array|null
     */
    protected $utilisateur;

    /**
     * Constructeur de la vue de base
     * 
     * @param string $titre Titre de la page
     * @param array $donnees Données à afficher dans la vue
     * @param array $erreurs Liste des erreurs à afficher
     */
    public function __construct($titre, $donnees = [], $erreurs = [])
    {
        $this->titre = $titre;
        $this->donnees = $donnees;
        $this->erreurs = $erreurs;
        
        // Récupération de l'utilisateur connecté
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->utilisateur = $_SESSION['utilisateur'] ?? null;
    }

    /**
     * Génère et affiche l'en-tête HTML commun à toutes les pages
     * 
     * @param array $styles Liste des fichiers CSS supplémentaires à inclure
     * @param array $scripts Liste des fichiers JavaScript supplémentaires à inclure
     * @return void
     */
    protected function afficherEntete($styles = [], $scripts = [])
    {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($this->titre); ?></title>
            
            <!-- Styles de base -->
            <link rel="stylesheet" href="CSS/style.css">
            
            <!-- Styles supplémentaires -->
            <?php foreach ($styles as $style): ?>
                <link rel="stylesheet" href="<?php echo htmlspecialchars($style); ?>">
            <?php endforeach; ?>
            
            <!-- Font Awesome -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            
            <!-- Scripts JavaScript en-tête -->
            <?php foreach ($scripts as $script): ?>
                <script src="<?php echo htmlspecialchars($script); ?>"></script>
            <?php endforeach; ?>
        </head>
        <body<?php echo isset($_SESSION['utilisateur']['pmr']) && $_SESSION['utilisateur']['pmr'] ? ' data-pmr="oui"' : ' data-pmr="non"'; ?>>
        <?php
        $this->afficherNavbar();
        $this->afficherHero();
        $this->afficherMessages();
    }

    /**
     * Génère et affiche la barre de navigation
     * 
     * @return void
     */
    protected function afficherNavbar()
    {
        // Récupérer la page active
        $pageActive = $_GET['page'] ?? 'accueil';
        
        // Créer l'objet NavbarControleur
        $navbarControleur = new NavbarControleur();
        
        // Récupérer les liens de navigation
        $liensNavbar = $navbarControleur->obtenirLiens($pageActive, $this->utilisateur !== null);
        
        ?>
        <nav class="navbar">
            <div class="container">
                <a href="?page=accueil" class="logo">
                    <img src="assets/logo.png" alt="SportConnect Logo" class="navbar-logo">
                    <span>SportConnect</span>
                </a>
                <ul class="nav-links">
                    <?php foreach ($liensNavbar as $nom => $lien): ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($lien['url']); ?>"
                               class="<?php echo $lien['actif'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($nom); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>

                    <?php if ($this->utilisateur): ?>
                        <li class="user-info">
                            <i class="fa-solid fa-user"></i> 
                            <?php echo htmlspecialchars($this->utilisateur['pseudo']); ?>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
        <?php
    }

    /**
     * Génère et affiche la section hero avec le titre et le sous-titre
     * 
     * @param string|null $sousTitre Sous-titre à afficher (null pour utiliser le sous-titre par défaut)
     * @return void
     */
    protected function afficherHero($sousTitre = null)
    {
        // Sous-titre par défaut
        if ($sousTitre === null) {
            $sousTitre = "Explorez et rejoignez des événements sportifs près de chez vous !";
        }
        
        ?>
        <header class="hero">
            <h1><?php echo htmlspecialchars($this->titre); ?></h1>
            <p><?php echo htmlspecialchars($sousTitre); ?></p>
        </header>
        <?php
    }

    /**
     * Affiche les messages de succès et d'erreur s'ils existent
     * 
     * @return void
     */
    protected function afficherMessages()
    {
        ?>
        <div class="container">
            <?php
            // Affichage du message de réussite
            if (isset($_SESSION['messageReussite'])):
                ?>
                <div class="messageReussite">
                    <i class="fa-solid fa-circle-check"></i> 
                    <?php echo htmlspecialchars($_SESSION['messageReussite']); ?>
                </div>
                <?php
                unset($_SESSION['messageReussite']);
            endif;

            // Affichage du message d'erreur
            if (isset($_SESSION['messageErreur'])):
                ?>
                <div class="error-message">
                    <i class="fa-solid fa-circle-exclamation"></i> 
                    <?php echo htmlspecialchars($_SESSION['messageErreur']); ?>
                </div>
                <?php
                unset($_SESSION['messageErreur']);
            endif;

            // Affichage des erreurs spécifiques à la vue
            if (!empty($this->erreurs)):
                ?>
                <div class="erreurs">
                    <ul>
                        <?php foreach ($this->erreurs as $erreur): ?>
                            <li class="erreur-item"><?php echo htmlspecialchars($erreur); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php
            endif;
            ?>
        </div>
        <?php
    }

    /**
     * Génère et affiche le pied de page
     * 
     * @param array $scripts Liste des fichiers JavaScript à inclure avant la fermeture du body
     * @return void
     */
    protected function afficherPiedDePage($scripts = [])
    {
        ?>
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> SportConnect - Tous droits réservés, fait avec amour ❤</p>
        </footer>

        <!-- Scripts JavaScript -->
        <script src="JS/script.js"></script>
        
        <?php foreach ($scripts as $script): ?>
            <script src="<?php echo htmlspecialchars($script); ?>"></script>
        <?php endforeach; ?>
        </body>
        </html>
        <?php
    }

    /**
     * Méthode à implémenter dans chaque vue pour afficher le contenu spécifique
     * 
     * @return void
     */
    abstract protected function afficherContenu();

    /**
     * Méthode principale pour afficher la vue complète
     * 
     * @param array $styles Liste des fichiers CSS supplémentaires à inclure
     * @param array $scripts Liste des fichiers JavaScript supplémentaires à inclure
     * @param array $scriptsFooter Liste des fichiers JavaScript à inclure avant la fermeture du body
     * @return void
     */
    public function afficher($styles = [], $scripts = [], $scriptsFooter = [])
    {
        $this->afficherEntete($styles, $scripts);
        $this->afficherContenu();
        $this->afficherPiedDePage($scriptsFooter);
    }

    /**
     * Méthode utilitaire pour échapper les caractères spéciaux dans une chaîne
     * 
     * @param string $chaine Chaîne à échapper
     * @return string Chaîne échappée
     */
    protected function e($chaine)
    {
        return htmlspecialchars($chaine, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Méthode utilitaire pour formater une date
     * 
     * @param string $date Date au format SQL
     * @param string $format Format de sortie (par défaut : 'd/m/Y')
     * @return string Date formatée
     */
    protected function formaterDate($date, $format = 'd/m/Y')
    {
        $dateObj = new \DateTime($date);
        return $dateObj->format($format);
    }
}