<?php

namespace App\Vues;

use App\Core\VueBase;

/**
 * Classe ConnexionVue
 * 
 * Vue pour la page de connexion
 */
class ConnexionVue extends VueBase
{
    /**
     * Constructeur de ConnexionVue
     * 
     * @param string $titre Le titre de la page
     * @param array $erreurs Liste des erreurs à afficher
     */
    public function __construct($titre, $erreurs = [])
    {
        parent::__construct($titre, [], $erreurs);
    }

    /**
     * Affiche le contenu spécifique de la page de connexion
     */
    protected function afficherContenu()
    {
        ?>
        <main>
            <div class="connexion-container">
                <h2 class="connexion-title">Se connecter</h2>

                <form action="?page=connexion&action=traiterConnexion" method="POST" class="connexion-form">
                    <input type="text" name="identifiant" class="connexion-input" placeholder="Email ou Pseudo" required>
                    <input type="password" name="mot_de_passe" class="connexion-input" placeholder="Mot de passe" required>
                    <button type="submit" class="connexion-button">Se connecter</button>
                </form>

                <p class="inscription-link">
                    Pas encore inscrit ? <a href="?page=inscriptions">Créez un compte ici</a>
                </p>
                <p class="mot-de-passe-oublie">
                    <a href="?page=mot_de_passe_oublie">Mot de passe oublié ?</a>
                </p>
            </div>
        </main>
        <?php
    }

    /**
     * Surcharge de la méthode afficherHero pour personnaliser le sous-titre
     */
    protected function afficherHero($sousTitre = null)
    {
        // Si aucun sous-titre n'est fourni, on utilise celui spécifique à la connexion
        if ($sousTitre === null) {
            $sousTitre = "Connectez-vous pour accéder à votre profil et réserver vos événements sportifs !";
        }
        
        // Appel de la méthode parente avec le sous-titre personnalisé
        parent::afficherHero($sousTitre);
    }
}