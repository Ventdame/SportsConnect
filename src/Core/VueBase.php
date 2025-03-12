<?php
namespace App\Core;

use App\Controleur\NavbarControleur;
use App\Securite\CSRFProtection;

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
        // Ajouter script de protection contre la manipulation des formulaires
        echo $this->ajouterScriptsSecurite();
        
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
    
    /**
     * Génère les champs cachés pour la protection CSRF
     * 
     * @param string $formName Nom du formulaire
     * @return string HTML des champs cachés
     */
    protected function genererChampsCSRF($formName) {
        $token = CSRFProtection::genererToken($formName);
        return '<input type="hidden" name="csrf_token" value="' . $this->e($token) . '">' .
               '<input type="hidden" name="form_name" value="' . $this->e($formName) . '">';
    }
    
    /**
     * Génère un formulaire sécurisé
     * 
     * @param string $action URL d'action
     * @param string $formName Nom du formulaire
     * @param string $method Méthode HTTP
     * @param string $content Contenu du formulaire
     * @param array $attributes Attributs supplémentaires
     * @return string HTML du formulaire
     */
    protected function genererFormulaire($action, $formName, $method = 'POST', $content = '', $attributes = []) {
        $attributesStr = '';
        foreach ($attributes as $key => $value) {
            $attributesStr .= ' ' . $this->e($key) . '="' . $this->e($value) . '"';
        }
        
        $html = '<form action="' . $this->e($action) . '" method="' . $this->e($method) . '"' . $attributesStr . '>';
        
        if (strtoupper($method) === 'POST') {
            $html .= $this->genererChampsCSRF($formName);
        }
        
        $html .= $content . '</form>';
        
        return $html;
    }
    
    /**
     * Génère un input pour un ID sécurisé
     * 
     * @param string $token Token sécurisé
     * @return string HTML de l'input caché
     */
    protected function genererInputIDSecurise($token) {
        return '<input type="hidden" name="secure_token" value="' . $this->e($token) . '">';
    }
    
    /**
     * Ajoute des scripts JavaScript de sécurité
     * 
     * @return string Balises script pour les protections côté client
     */
    protected function ajouterScriptsSecurite() {
        return '<script>
// Empêcher la modification des formulaires via la console
            document.addEventListener("DOMContentLoaded", function() {
                // Protection des formulaires
                const forms = document.querySelectorAll("form");
                forms.forEach(function(form) {
                    // Créer une copie des champs du formulaire pour vérifier les modifications
                    const originalFields = Array.from(form.elements).map(function(el) {
                        return {
                            name: el.name,
                            type: el.type,
                            required: el.required,
                            value: el.value
                        };
                    });
                    
                    // Vérifier l\'intégrité du formulaire avant soumission
                    form.addEventListener("submit", function(e) {
                        let formValid = true;
                        
                        // Vérifier que les champs n\'ont pas été modifiés en structure
                        Array.from(form.elements).forEach(function(el, index) {
                            if (index < originalFields.length) {
                                if (el.name !== originalFields[index].name || 
                                    el.type !== originalFields[index].type ||
                                    el.required !== originalFields[index].required) {
                                    formValid = false;
                                }
                            }
                        });
                        
                        // Vérifier que le formulaire a le même nombre de champs
                        if (form.elements.length !== originalFields.length) {
                            formValid = false;
                        }
                        
                        // Si le formulaire a été altéré, annuler la soumission
                        if (!formValid) {
                            e.preventDefault();
                            alert("Le formulaire a été modifié de manière incorrecte. La page va être rechargée.");
                            window.location.reload();
                        }
                    });
                });
                
                // Protection contre la modification des inputs
                const inputs = document.querySelectorAll("input[type=\'hidden\']");
                inputs.forEach(function(input) {
                    // Surveiller les changements d\'attributs
                    const observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.attributeName === "type" || 
                                mutation.attributeName === "value" || 
                                mutation.attributeName === "name") {
                                console.error("Tentative de modification d\'un champ caché détectée");
                                // Restaurer l\'état original
                                input.type = "hidden";
                                
                                // Recharger la page pour empêcher toute manipulation
                                window.location.reload();
                            }
                        });
                    });
                    
                    // Configurer l\'observation des attributs
                    observer.observe(input, { attributes: true });
                    
                    // Empêcher de modifier la valeur via JavaScript
                    const originalValue = input.value;
                    const originalType = input.type;
                    
                    Object.defineProperty(input, "value", {
                        get: function() {
                            return originalValue;
                        },
                        set: function(newValue) {
                            console.error("Tentative de modification de la valeur d\'un champ caché");
                            return originalValue;
                        }
                    });
                    
                    Object.defineProperty(input, "type", {
                        get: function() {
                            return originalType;
                        },
                        set: function(newType) {
                            if (newType !== "hidden") {
                                console.error("Tentative de modification du type d\'un champ caché");
                                window.location.reload();
                            }
                            return originalType;
                        }
                    });
                });
                
                // Empêcher la surcharge des méthodes standard de manipulation du DOM
                const originalQuerySelector = document.querySelector;
                const originalGetElementById = document.getElementById;
                
                document.querySelector = function(selector) {
                    if (selector.includes("input[type=\'hidden\']") || 
                        selector.includes("[name=\'secure_token\']") || 
                        selector.includes("[name=\'csrf_token\']")) {
                        console.error("Tentative de sélection de champs sécurisés");
                        return null;
                    }
                    return originalQuerySelector.call(document, selector);
                };
                
                document.getElementById = function(id) {
                    const element = originalGetElementById.call(document, id);
                    if (element && element.type === "hidden" && 
                        (element.name === "secure_token" || element.name === "csrf_token" || element.name === "form_name")) {
                        console.error("Tentative d\'accès à un élément sécurisé");
                        return null;
                    }
                    return element;
                };
                
                // Protection contre le debug et les outils de développement
                function detectDevTools() {
                    const threshold = 160;
                    const widthThreshold = window.outerWidth - window.innerWidth > threshold;
                    const heightThreshold = window.outerHeight - window.innerHeight > threshold;
                    
                    if (widthThreshold || heightThreshold) {
                        // Si les outils de développement sont détectés, ajouter une protection supplémentaire
                        const securityInterval = setInterval(function() {
                            const hiddenInputs = document.querySelectorAll("input[type=\'hidden\']");
                            hiddenInputs.forEach(function(input) {
                                if (input.type !== "hidden") {
                                    input.type = "hidden";
                                    window.location.reload();
                                }
                            });
                        }, 1000);
                    }
                }
                
                window.addEventListener("resize", detectDevTools);
                detectDevTools();
            });
        </script>';
    }
}