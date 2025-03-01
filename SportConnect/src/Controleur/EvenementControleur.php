<?php

namespace App\Controleur;

use App\Modeles\EvenementModele;

class EvenementControleur
{
    private $evenementModele;

    /**
     * Constructeur du contrôleur des événements
     *
     * @param \PDO $pdo Connexion PDO
     */
    public function __construct($pdo)
    {
        $this->evenementModele = new EvenementModele($pdo);
    }

        /**
     * Méthode pour créer un nouvel événement
     */
    public function creer_evenement()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['utilisateur'])) {
            header("Location: ?page=connexion");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $nomEvenement = $_POST['nom_evenement'] ?? '';
                $dateEvenement = $_POST['date_evenement'] ?? '';
                $description = $_POST['description'] ?? '';
                $localisationType = $_POST['localisation_type'] ?? 'existante';
                $idLocalisation = $_POST['id_localisation'] ?? null;
                $idSport = $_POST['id_sport'] ?? '';
                $pmrAccessible = $_POST['pmr_accessible'] ?? 0;
                $montant = $_POST['montant'] ?? 0.0;

                // Validation des champs requis
                if (empty($nomEvenement) || empty($dateEvenement) || empty($idSport)) {
                    throw new \Exception("Tous les champs obligatoires doivent être remplis.");
                }

                // Si l'utilisateur choisit une nouvelle localisation, la créer dans la base de données
                if ($localisationType === 'nouvelle') {
                    $nomLocalisation = $_POST['nom_localisation_evenement'] ?? '';
                    $ville = $_POST['ville'] ?? '';
                    $adresse = $_POST['adresse'] ?? '';
                    $codePostal = $_POST['code_postal'] ?? '';

                    if (empty($nomLocalisation) || empty($ville)) {
                        throw new \Exception("Les champs Nom et Ville pour la localisation doivent être remplis.");
                    }

                    $idLocalisation = $this->evenementModele->creerLocalisation($nomLocalisation, $ville, $adresse, $codePostal);
                }

                // Récupérer l'utilisateur connecté
                $idUtilisateur = $_SESSION['utilisateur']['id_utilisateur'];

                // Créer l'événement
                $this->evenementModele->creerEvenement(
                    $nomEvenement,
                    $dateEvenement,
                    $description,
                    $idLocalisation,
                    $idSport,
                    $pmrAccessible,
                    $montant,
                    $idUtilisateur
                );

                $_SESSION['messageReussite'] = "L'événement a été créé avec succès !";
                header("Location: ?page=profil");
                exit;
            } catch (\Exception $e) {
                $_SESSION['messageErreur'] = "Erreur lors de la création de l'événement : " . $e->getMessage();
                header("Location: ?page=profil");
                exit;
            }
        } else {
            header("Location: ?page=profil");
            exit;
        }
    }

    /**
     * Supprime un événement créé par un utilisateur
     */
    public function supprimer_evenement_creer_utilisateur()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['utilisateur'])) {
            header("Location: ?page=connexion");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $idEvenement = $_POST['id_evenement'] ?? null;

                if (empty($idEvenement)) {
                    throw new \Exception("ID de l'événement requis pour la suppression.");
                }

                $idUtilisateur = $_SESSION['utilisateur']['id_utilisateur'];

                // Supprime l'événement s'il appartient à l'utilisateur connecté
                $resultat = $this->evenementModele->supprimerEvenementCreerParUtilisateur($idEvenement, $idUtilisateur);

                if ($resultat) {
                    $_SESSION['messageReussite'] = "L'événement a été supprimé avec succès.";
                } else {
                    $_SESSION['messageErreur'] = "Impossible de supprimer l'événement.";
                }
            } catch (\Exception $e) {
                $_SESSION['messageErreur'] = "Erreur : " . $e->getMessage();
            }
        }

        // Redirection vers le profil
        header("Location: ?page=profil");
        exit;
    }
}
