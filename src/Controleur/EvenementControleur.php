<?php

namespace App\Controleur;

use App\Core\ControleurBase;
use App\Core\Reponses;
use App\Fabrique\EvenementModele;
use App\Fabrique\NotificationModele;

/**
 * Contrôleur pour la gestion des événements
 */
class EvenementControleur extends ControleurBase
{
    /**
     * Instance du modèle EvenementModele
     * 
     * @var EvenementModele
     */
    private $evenementModele;
    
    /**
     * Instance du modèle NotificationModele
     * 
     * @var NotificationModele
     */
    private $notificationModele;

    /**
     * Constructeur du contrôleur EvenementControleur
     *
     * @param \PDO $pdo Connexion PDO
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->evenementModele = new EvenementModele($pdo);
        $this->notificationModele = new NotificationModele($pdo);
    }

    /**
     * Méthode par défaut
     */
    public function index()
    {
        // Redirection vers la page des réservations
        Reponses::rediriger('reservation');
    }

    /**
     * Méthode pour créer un nouvel événement
     */
    public function creer_evenement()
    {
        // Vérifier si l'utilisateur est connecté
        if (!$this->exigerConnexion()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Récupération des données du formulaire
                $donnees = $this->obtenirDonneesFormulaire([
                    'nom_evenement', 'date_evenement', 'id_sport', 'max_participants'
                ]);
                
                if ($donnees) {
                    $nomEvenement = $donnees['nom_evenement'];
                    $dateEvenement = $donnees['date_evenement'];
                    $description = $donnees['description'] ?? '';
                    $localisationType = $donnees['localisation_type'] ?? 'existante';
                    $idLocalisation = $donnees['id_localisation'] ?? null;
                    $idSport = $donnees['id_sport'];
                    $pmrAccessible = $donnees['pmr_accessible'] ?? 0;
                    $montant = $donnees['montant'] ?? 0.0;
                    $maxParticipants = $donnees['max_participants'] ?? 10;
                    
                    // Si l'utilisateur choisit une nouvelle localisation, la créer
                    if ($localisationType === 'nouvelle') {
                        $nomLocalisation = $donnees['nom_localisation_evenement'] ?? '';
                        $ville = $donnees['ville'] ?? '';
                        $adresse = $donnees['adresse'] ?? '';
                        $codePostal = $donnees['code_postal'] ?? '';
                        
                        if (empty($nomLocalisation) || empty($ville)) {
                            throw new \Exception("Les champs Nom et Ville pour la localisation doivent être remplis.");
                        }
                        
                        $idLocalisation = $this->evenementModele->creerLocalisation($nomLocalisation, $ville, $adresse, $codePostal, $pmrAccessible);
                    }
                    
                    // Créer l'événement
                    $idEvenement = $this->evenementModele->creerEvenement(
                        $nomEvenement,
                        $dateEvenement,
                        $description,
                        $idLocalisation,
                        $idSport,
                        $pmrAccessible,
                        $montant,
                        $this->utilisateurConnecte['id_utilisateur'],
                        $maxParticipants
                    );
                    
                    // Créer une notification pour le nouvel événement
                    $this->notificationModele->creerNotificationCreation(
                        $this->utilisateurConnecte['id_utilisateur'],
                        $idEvenement,
                        $donnees['nom_evenement']
                    );
                    
                    $this->ajouterMessageReussite("L'événement a été créé avec succès !");
                    Reponses::rediriger('profil');
                } else {
                    // En cas d'erreur de validation des données du formulaire
                    Reponses::rediriger('profil', [], "Tous les champs obligatoires doivent être remplis.", 'erreur');
                }
            } catch (\Exception $e) {
                // En cas d'erreur lors de la création
                $this->ajouterMessageErreur("Erreur lors de la création de l'événement : " . $e->getMessage());
                Reponses::rediriger('profil');
            }
        } else {
            // Si la méthode n'est pas POST
            Reponses::rediriger('profil');
        }
    }

    /**
     * Modifie un événement existant
     */
    public function modifier_evenement()
    {
        if (!$this->exigerConnexion()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $donnees = $this->obtenirDonneesFormulaire(['id_evenement', 'nom_evenement', 'date_evenement']);
                
                if ($donnees) {
                    $evenement = $this->evenementModele->obtenirEvenementParId($donnees['id_evenement']);
                    
                    if (!$evenement) {
                        throw new \Exception("Événement introuvable");
                    }
                    
                    if ($evenement['id_utilisateur'] != $this->utilisateurConnecte['id_utilisateur']) {
                        throw new \Exception("Vous n'êtes pas autorisé à modifier cet événement");
                    }

                    $this->evenementModele->mettreAJourEvenement($donnees['id_evenement'], $donnees);
                    
                    // Notification pour les participants
                    $this->notificationModele->creerNotificationsModification(
                        $this->utilisateurConnecte['id_utilisateur'],
                        $donnees['id_evenement'],
                        $donnees['nom_evenement']
                    );

                    $this->ajouterMessageReussite("Événement modifié avec succès");
                    Reponses::rediriger('profil');
                } else {
                    $this->ajouterMessageErreur("Données manquantes pour la modification");
                    Reponses::rediriger('profil');
                }
            } catch (\Exception $e) {
                $this->ajouterMessageErreur("Erreur de modification : " . $e->getMessage());
                Reponses::rediriger('profil');
            }
        } else {
            Reponses::rediriger('profil');
        }
    }

    /**
     * Supprime un événement créé par un utilisateur
     */
    public function supprimer_evenement_creer_utilisateur()
    {
        // Vérifier si l'utilisateur est connecté
        if (!$this->exigerConnexion()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Récupérer l'ID de l'événement
                $idEvenement = isset($_POST['id_evenement']) ? intval($_POST['id_evenement']) : null;

                if (empty($idEvenement)) {
                    throw new \Exception("ID de l'événement requis pour la suppression.");
                }

                $idUtilisateur = $this->utilisateurConnecte['id_utilisateur'];

                // Vérifier d'abord si l'événement appartient à l'utilisateur
                $evenement = $this->evenementModele->obtenirEvenementParId($idEvenement);
                
                if (!$evenement) {
                    throw new \Exception("Événement introuvable.");
                }
                
                if ($evenement['id_utilisateur'] != $idUtilisateur) {
                    throw new \Exception("Vous n'êtes pas autorisé à supprimer cet événement.");
                }

                // Récupérer le nom de l'événement pour les notifications
                $nomEvenement = $evenement['nom_evenement'];
                
                // Envoyer des notifications aux participants avant de supprimer l'événement
                $this->notificationModele->creerNotificationsSuppressionEvenement(
                    $idUtilisateur,
                    $idEvenement,
                    $nomEvenement
                );
                
                // Supprime l'événement s'il appartient à l'utilisateur connecté
                $resultat = $this->evenementModele->supprimerEvenementCreerParUtilisateur($idEvenement, $idUtilisateur);

                if ($resultat) {
                    $this->ajouterMessageReussite("L'événement a été supprimé avec succès.");
                } else {
                    $this->ajouterMessageErreur("Impossible de supprimer l'événement.");
                }
            } catch (\Exception $e) {
                $this->ajouterMessageErreur("Erreur : " . $e->getMessage());
            }
        } else {
            $this->ajouterMessageErreur("Méthode non autorisée pour cette action.");
        }

        // Redirection vers le profil
        Reponses::rediriger('profil');
    }
}