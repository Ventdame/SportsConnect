<?php

namespace App\Controleur;

use App\Core\ControleurBase;
use App\Core\Reponses;
use App\Fabrique\EvenementModele;
use App\Fabrique\UtilisateurModele;
use App\Fabrique\ReservationModele;
use App\Fabrique\SportModele;
use App\Vues\ProfilVue;

/**
 * Contrôleur pour la gestion du profil utilisateur
 */
class ProfilControleur extends ControleurBase
{
    /**
     * Instance du modèle UtilisateurModele
     * 
     * @var UtilisateurModele
     */
    private $utilisateurModele;
    
    /**
     * Instance du modèle ReservationModele
     * 
     * @var ReservationModele
     */
    private $reservationModele;
    
    /**
     * Instance du modèle SportModele
     * 
     * @var SportModele
     */
    private $sportModele;
    
    /**
     * Instance du modèle EvenementModele
     * 
     * @var EvenementModele
     */
    private $evenementModele;

    /**
     * Constructeur du contrôleur ProfilControleur
     * 
     * @param \PDO $pdo Instance de PDO pour la connexion à la base de données
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->utilisateurModele = new UtilisateurModele($pdo);
        $this->reservationModele = new ReservationModele($pdo);
        $this->sportModele = new SportModele($pdo);
        $this->evenementModele = new EvenementModele($pdo);
    }

    /**
     * Méthode principale pour afficher le profil de l'utilisateur
     */
    public function index()
    {
        // Vérifier si l'utilisateur est connecté
        if (!$this->exigerConnexion()) {
            return;
        }

        try {
            // Récupérer les événements réservés par l'utilisateur
            $reservations = $this->reservationModele->getEvenementsParUtilisateur($this->utilisateurConnecte['id_utilisateur']);

            // Récupérer les événements créés par l'utilisateur
            $evenementsCrees = $this->evenementModele->recupererEvenementsParUtilisateur($this->utilisateurConnecte['id_utilisateur']);

            // Récupérer les sports disponibles
            $sportsDisponibles = $this->sportModele->obtenirSports();

            // Récupérer les localisations existantes
            $localisationsExistantes = $this->evenementModele->obtenirLocalisations();

            // Afficher la vue du profil
            $vue = new ProfilVue(
                "Profil - SportConnect",
                $this->utilisateurConnecte,
                $reservations,
                $sportsDisponibles,
                $localisationsExistantes,
                $evenementsCrees
            );
            $vue->afficher();
        } catch (\Exception $e) {
            $this->ajouterMessageErreur("Erreur lors de la récupération des données : " . $e->getMessage());
            Reponses::rediriger('accueil');
        }
    }
}