<?php

namespace App\Controleur;

use App\Core\ControleurBase;
use App\Core\Reponses;

/**
 * Contrôleur pour la gestion de la déconnexion des utilisateurs
 */
class DeconnexionControleur extends ControleurBase
{
    /**
     * Méthode par défaut pour gérer la déconnexion de l'utilisateur
     */
    public function index()
    {
        // Appel de la méthode de déconnexion
        $this->deconnecter();
    }

    /**
     * Méthode pour déconnecter l'utilisateur
     */
    public function deconnecter()
    {
        // Destruction des données de session
        session_destroy();

        // Redirection vers la page de connexion avec un message de confirmation
        Reponses::rediriger('connexion', [], "Vous avez été déconnecté avec succès.", 'reussite');
    }
}