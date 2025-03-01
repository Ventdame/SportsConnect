<?php
namespace App\Controleur;

class NavbarControleur
{
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
    public function obtenirLiens($pageActive, $estConnecte)
    {
        // Initialisation des liens de navigation de base
        $liens = [
            // Lien vers la page d'accueil, marque 'active' si c'est la page actuelle
            'Accueil' => ['url' => '?page=accueil', 'actif' => $pageActive === 'accueil'],
            // Lien vers la page des réservations, marque 'active' si c'est la page actuelle
            'Réservations' => ['url' => '?page=reservation', 'actif' => $pageActive === 'reservation']
        ];

        // Si l'utilisateur est connecté, on ajoute les liens "Profil" et "Déconnexion"
        if (isset($_SESSION['utilisateur']) && !empty($_SESSION['utilisateur'])) {
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
