<?php
namespace App\Controleur;

class DeconnexionControleur
{
    /**
     * Méthode index pour gérer la déconnexion de l'utilisateur.
     *
     * Cette méthode est appelée lorsqu'un utilisateur souhaite se déconnecter.
     * Elle appelle la méthode `deconnecter()` pour effectuer la déconnexion proprement.
     */
    public function index()
    {
        // Appelle la méthode de déconnexion
        $this->deconnecter();
    }

    /**
     * Méthode pour déconnecter l'utilisateur.
     *
     * Cette méthode s'occupe de détruire la session de l'utilisateur, de supprimer 
     * toutes les données de la session et de rediriger l'utilisateur vers la page de connexion.
     * Elle vérifie également si la session est active avant de la détruire.
     */
    public function deconnecter()
    {
        // Démarrer la session si elle n'est pas déjà active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();  // Démarre la session si elle n'est pas déjà active
        }

        // Détruire les données de session
        session_destroy();  // Cette fonction détruit toutes les données de session

        // Rediriger vers la page de connexion après la déconnexion
        header(header: "Location: ?page=connexion&message=deconnecte");  // Redirige l'utilisateur vers la page de connexion avec un message
        exit;  // Arrête le script pour garantir qu'aucune exécution ne continue après la redirection
    }
}
