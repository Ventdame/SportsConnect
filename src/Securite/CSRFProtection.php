<?php
// src/Securite/CSRFProtection.php

namespace App\Securite;

class CSRFProtection
{
    /**
     * Génère un token CSRF unique
     * 
     * @param string $formName Nom du formulaire pour lequel générer le token
     * @return string Le token généré
     */
    public static function genererToken($formName)
    {
        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }

        $token = bin2hex(random_bytes(32));

        $_SESSION['csrf_tokens'][$formName] = [
            'token' => $token,
            'expires' => time() + 3600
        ];


        return $token;
    }

    /**
     * Vérifie si un token CSRF est valide
     * 
     * @param string $formName Nom du formulaire
     * @param string $token Token à vérifier
     * @return bool True si le token est valide
     */
    public static function verifierToken($formName, $token)
    {
        if (!isset($_SESSION['csrf_tokens'][$formName])) {
            return false;
        }

        $stored = $_SESSION['csrf_tokens'][$formName];

        // Vérifier si le token est expiré
        if ($stored['expires'] < time()) {
            unset($_SESSION['csrf_tokens'][$formName]);
            return false;
        }

        // Vérifier si le token correspond
        if (hash_equals($stored['token'], $token)) {
            // Utilisation unique - supprimer après vérification
            unset($_SESSION['csrf_tokens'][$formName]);
            return true;
        }

        return false;
    }
}
