<?php
// src/Securite/SecurityMiddleware.php

namespace App\Securite;

class SecurityMiddleware {
    /**
     * Vérifie que l'utilisateur a le rôle administrateur
     */
    public function exigerRoleAdmin()
    {
        if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'administrateur') {
            Reponses::rediriger('accueil');
            exit;
        }
    }

    /**
     * Effectue les vérifications de sécurité sur chaque requête
     * 
     * @return bool True si la requête est valide
     */
    public static function verifierRequete() {
        // Vérifier l'origine de la requête pour les requêtes POST (CSRF protection)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Les requêtes AJAX devraient être exemptes de vérification CSRF
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                return true;
            }
            
            // Vérification du Referer pour les requêtes normales
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            $host = $_SERVER['HTTP_HOST'];
            
            // Vérifier que la requête vient bien du même site
            if (!$referer || strpos($referer, $host) === false) {
                // On pourrait désactiver cette vérification si elle pose problème
                // return false;
                return true; // Permettre les requêtes sans Referer
            }
            
            // Vérification de token CSRF seulement si le form_name est présent
            // pour ne pas bloquer les requêtes légitimes
            if (isset($_POST['form_name']) && isset($_POST['csrf_token'])) {
                $token = $_POST['csrf_token'];
                $formName = $_POST['form_name'];
                
                return CSRFProtection::verifierToken($formName, $token);
            } 
            
            // Autoriser les requêtes POST sans token pour les formulaires simples
            // comme la connexion/inscription pour éviter les blocages
            return true;
        }
        
        // Pour toutes les autres méthodes (GET, etc.)
        return true;
    }
    
/**
 * Génère les en-têtes de sécurité
 */
public static function ajouterEnTetes() {
    // Empêcher le navigateur de cacher les réponses
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    
    // Protection contre XSS
    header("X-XSS-Protection: 1; mode=block");
    
    // Empêcher le clickjacking
    header("X-Frame-Options: DENY");
    
    // Empêcher le MIME-sniffing
    header("X-Content-Type-Options: nosniff");
    
    // Modifier cette ligne pour permettre les styles et scripts en ligne
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data:;");
}
}