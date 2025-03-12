<?php
// src/Securite/SecurityMiddleware.php

namespace App\Securite;

class SecurityMiddleware {
    /**
     * Effectue les vérifications de sécurité sur chaque requête
     * 
     * @return bool True si la requête est valide
     */
    public static function verifierRequete() {
        // Vérifier l'origine de la requête pour les requêtes POST (CSRF protection)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            $host = $_SERVER['HTTP_HOST'];
            
            // Vérifier que la requête vient bien du même site
            if (!$referer || strpos($referer, $host) === false) {
                return false;
            }
            
            // Vérifier le token CSRF pour les formulaires (sauf AJAX)
            if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
                if (!isset($_POST['csrf_token']) || !isset($_POST['form_name'])) {
                    return false;
                }
                
                $token = $_POST['csrf_token'];
                $formName = $_POST['form_name'];
                
                if (!CSRFProtection::verifierToken($formName, $token)) {
                    return false;
                }
            }
        }
        
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
        
        // Content Security Policy pour limiter les sources de contenu
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data:;");
    }
}