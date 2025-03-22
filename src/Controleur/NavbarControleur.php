<?php

namespace App\Controleur;

use App\Core\ControleurBase;

/**
 * Contrôleur pour la gestion de la barre de navigation
 */
class NavbarControleur extends ControleurBase
{
    /**
     * Méthode par défaut qui ne fait rien, car ce contrôleur n'a pas de vue associée
     */
    public function index()
    {
        // Cette méthode n'a pas d'implémentation car la navbar n'est pas une page en soi
    }

    /**
     * Retourne un tableau des liens de navigation pour la navbar.
     * 
     * Cette méthode génère dynamiquement les liens de la barre de navigation
     * en fonction de la page actuellement active et de l'état de connexion de l'utilisateur.
     *
     * @param string $pageActive La page actuellement affichée pour marquer le lien actif.
     * @param bool $estConnecte Un indicateur pour savoir si l'utilisateur est connecté.
     * @return array Tableau associatif des liens de navigation. 
     *               Les clés sont les noms des liens (ex: 'Accueil', 'Profil', etc.) et les valeurs sont des tableaux contenant 
     *               l'URL et un booléen indiquant si le lien doit être marqué comme actif.
     */
    public function obtenirLiens($pageActive, $estConnecte = null)
    {
        // Si l'état de connexion n'est pas explicitement fourni, on le détermine à partir de l'utilisateur connecté
        if ($estConnecte === null) {
            $estConnecte = $this->estConnecte();
        }
    
        // Initialisation des liens de navigation de base
        $liens = [
            // Lien vers la page d'accueil, marque 'active' si c'est la page actuelle
            'Accueil' => ['url' => '?page=accueil', 'actif' => $pageActive === 'accueil'],
            // Lien vers la page des réservations, marque 'active' si c'est la page actuelle
            'Réservations' => ['url' => '?page=reservation', 'actif' => $pageActive === 'reservation']
        ];
    
        // Si l'utilisateur est connecté, on ajoute les liens "Profil" et "Déconnexion"
        if ($estConnecte) {
            // Vérifier si l'utilisateur est administrateur
            if (isset($this->utilisateurConnecte['role']) && $this->utilisateurConnecte['role'] === 'admin') {
                $liens['Administration'] = ['url' => '?page=admin', 'actif' => $pageActive === 'admin'];
            }
            
            // Lien vers la page de profil, marque 'active' si c'est la page actuelle
            $liens['Profil'] = ['url' => '?page=profil', 'actif' => $pageActive === 'profil'];
            // Lien vers la page de déconnexion, marque 'active' si c'est la page actuelle
            $liens['Déconnexion'] = ['url' => '?page=deconnexion', 'actif' => $pageActive === 'deconnexion'];
        } else {
            // Si l'utilisateur n'est pas connecté, on ajoute les liens "Inscriptions" et "Connexion"
            // Lien vers la page d'inscription, marque 'active' si c'est la page actuelle
            $liens['Inscriptions'] = ['url' => '?page=inscriptions', 'actif' => $pageActive === 'inscriptions'];
            // Lien vers la page de connexion, marque 'active' si c'est la page actuelle
            $liens['Connexion'] = ['url' => '?page=connexion', 'actif' => $pageActive === 'connexion'];
        }
    
        // Retourne le tableau des liens
        return $liens;
    }
}