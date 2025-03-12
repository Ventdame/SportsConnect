<?php
// src/Securite/ValidateurDonnees.php

namespace App\Securite;

class ValidateurDonnees {
    /**
     * Valide un ID d'entité
     * 
     * @param mixed $id L'ID à valider
     * @return bool True si l'ID est valide
     */
    public static function estIdValide($id) {
        return is_numeric($id) && $id > 0 && filter_var($id, FILTER_VALIDATE_INT) !== false;
    }
    
    /**
     * Nettoie une chaîne de caractères
     * 
     * @param string $input La chaîne à nettoyer
     * @return string La chaîne nettoyée
     */
    public static function nettoyerChaine($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Valide un email
     * 
     * @param string $email L'email à valider
     * @return bool True si l'email est valide
     */
    public static function estEmailValide($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Vérifie que les valeurs reçues correspondent aux types attendus
     * 
     * @param array $donnees Les données à vérifier
     * @param array $schema Le schéma définissant les types attendus
     * @return array Tableau des erreurs, vide si tout est correct
     */
    public static function validerSchema($donnees, $schema) {
        $erreurs = [];
        
        foreach ($schema as $champ => $config) {
            // Vérifier si le champ est requis
            if (isset($config['required']) && $config['required'] && (!isset($donnees[$champ]) || $donnees[$champ] === '')) {
                $erreurs[] = "Le champ $champ est requis";
                continue;
            }
            
            // Si le champ n'est pas présent mais pas requis, passer au suivant
            if (!isset($donnees[$champ])) {
                continue;
            }
            
            // Valider selon le type
            switch ($config['type']) {
                case 'int':
                    if (!self::estIdValide($donnees[$champ])) {
                        $erreurs[] = "Le champ $champ doit être un entier valide";
                    }
                    break;
                case 'email':
                    if (!self::estEmailValide($donnees[$champ])) {
                        $erreurs[] = "Le champ $champ doit être un email valide";
                    }
                    break;
                case 'string':
                    if (isset($config['min']) && strlen($donnees[$champ]) < $config['min']) {
                        $erreurs[] = "Le champ $champ doit contenir au moins {$config['min']} caractères";
                    }
                    if (isset($config['max']) && strlen($donnees[$champ]) > $config['max']) {
                        $erreurs[] = "Le champ $champ ne doit pas dépasser {$config['max']} caractères";
                    }
                    break;
            }
        }
        
        return $erreurs;
    }
}