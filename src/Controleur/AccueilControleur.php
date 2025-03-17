<?php

namespace App\Controleur;

use App\Core\ControleurBase;
use App\Core\Reponses;
use App\Fabrique\SportModele;
use App\Vues\AccueilVue;

/**
 * Contrôleur pour la page d'accueil
 */
class AccueilControleur extends ControleurBase
{
    /**
     * Instance du modèle SportModele
     * 
     * @var SportModele
     */
    private $sportModele;

    /**
     * Constructeur du contrôleur AccueilControleur
     * 
     * @param \PDO|null $pdo Instance de PDO pour la connexion à la base de données
     */
    public function __construct($pdo = null)
    {
        parent::__construct($pdo);
        $this->sportModele = new SportModele($pdo);
    }

    /**
     * Méthode pour afficher la page d'accueil
     */
    public function index()
    {
        try {
            // Vérifier si l'utilisateur est PMR
            $statutPmr = isset($this->utilisateurConnecte['pmr']) && $this->utilisateurConnecte['pmr'] == 1 ? 'oui' : 'non';

            // Ici on récupère le sexe de l'utilisateur
            $sexeUtilisateur = $this->utilisateurConnecte['sexe'] ?? 'A';
            
            // Récupérer la liste des sports appropriés selon le statut PMR et le sexe
            $sports = $this->sportModele->obtenirSportsSelonUtilisateur($statutPmr, $sexeUtilisateur);
            
            // Création et affichage de la vue
            $vue = new AccueilVue(
                "Bienvenue sur SportsConnect", 
                $sports, 
                $this->utilisateurConnecte,
                [],
                ($statutPmr === 'oui')
            );
            $vue->afficher();
        } catch (\Exception $e) {
            // En cas d'erreur, on ajoute un message d'erreur et on redirige vers l'accueil
            $this->ajouterMessageErreur("Erreur lors de la récupération des sports : " . $e->getMessage());
            Reponses::rediriger('accueil');
        }
    }
}