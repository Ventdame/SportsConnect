<?php

namespace App\Core;

/**
 * Classe Reponses
 * 
 * Gère les réponses HTTP de l'application de manière uniforme
 */
class Reponses 
{
    /**
     * Envoie une réponse JSON
     *
     * @param mixed $donnees Les données à envoyer au format JSON
     * @param int $statut Le code de statut HTTP (200 par défaut)
     * @return void
     */
    public static function json($donnees, $statut = 200) 
    {
        http_response_code($statut);
        header('Content-Type: application/json');
        echo json_encode($donnees);
        exit;
    }

    /**
     * Redirige vers une autre page
     *
     * @param string $page La page de destination
     * @param array $parametres Les paramètres à ajouter à l'URL
     * @param string|null $message Message à afficher sur la page de destination
     * @param string $typeMessage Type de message ('reussite' ou 'erreur')
     * @return void
     */
    public static function rediriger($page, $parametres = [], $message = null, $typeMessage = 'reussite') 
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Ajout du message dans la session si présent
        if ($message !== null) {
            if ($typeMessage === 'reussite') {
                $_SESSION['messageReussite'] = $message;
            } else {
                $_SESSION['messageErreur'] = $message;
            }
        }
        
        // Construction de l'URL
        $url = "?page=" . urlencode($page);
        foreach ($parametres as $cle => $valeur) {
            $url .= "&" . urlencode($cle) . "=" . urlencode($valeur);
        }
        
        // Redirection
        header("Location: " . $url);
        exit;
    }

    /**
     * Génère une vue avec les données fournies
     *
     * @param string $cheminVue Chemin vers le fichier de vue
     * @param array $donnees Données à passer à la vue
     * @return void
     */
    public static function vue($cheminVue, $donnees = []) 
    {
        // Extraction des données pour les rendre disponibles dans la vue
        extract($donnees);
        
        // Inclusion de la vue
        require_once $cheminVue;
        exit;
    }
}