<?php

namespace App\Vues;

use App\Core\VueBase;

/**
 * Classe InscriptionVue
 * 
 * Vue pour la page d'inscription
 */
class InscriptionVue extends VueBase
{
    /**
     * Message de réussite à afficher
     * 
     * @var string|null
     */
    private $messageReussite;

    /**
     * Constructeur de InscriptionVue
     * 
     * @param string $titre Le titre de la page
     * @param array $erreurs Liste des erreurs à afficher
     * @param string|null $messageReussite Message de réussite à afficher
     */
    public function __construct($titre, $erreurs = [], $messageReussite = null)
    {
        parent::__construct($titre, [], $erreurs);
        $this->messageReussite = $messageReussite;
    }

    /**
     * Affiche le contenu spécifique de la page d'inscription
     */
    protected function afficherContenu()
    {
        ?>
        <main>
            <div class="inscription-container">
                <h2 class="inscription-title">Créer un compte</h2>

                <?php if ($this->messageReussite): ?>
                    <div class="messageReussite">
                        <?php echo $this->e($this->messageReussite); ?>
                    </div>
                <?php endif; ?>

                <form action="?page=inscriptions&action=traiter" method="POST" class="inscription-form">
                    <!-- Champ pour le pseudo -->
                    <div class="form-group">
                        <label for="pseudo" class="form-label">Pseudo</label>
                        <input type="text" id="pseudo" name="pseudo" class="inscription-input" 
                               placeholder="Votre pseudo" 
                               value="<?php echo empty($this->erreurs) ? '' : $this->e($_POST['pseudo'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <!-- Champ pour le prénom -->
                    <div class="form-group">
                        <label for="prenom" class="form-label">Prénom</label>
                        <input type="text" id="prenom" name="prenom" class="inscription-input" 
                               placeholder="Votre prénom" 
                               value="<?php echo empty($this->erreurs) ? '' : $this->e($_POST['prenom'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <!-- Champ pour l'email -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="inscription-input" 
                               placeholder="Votre email" 
                               value="<?php echo empty($this->erreurs) ? '' : $this->e($_POST['email'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <!-- Champ pour le mot de passe -->
                    <div class="form-group">
                        <label for="mot_de_passe" class="form-label">Mot de passe</label>
                        <input type="password" id="mot_de_passe" name="mot_de_passe" 
                               class="inscription-input" placeholder="Votre mot de passe" required>
                    </div>
                    
                    <!-- Champ pour confirmer le mot de passe -->
                    <div class="form-group">
                        <label for="mot_de_passe_confirmation" class="form-label">Confirmation du mot de passe</label>
                        <input type="password" id="mot_de_passe_confirmation" name="mot_de_passe_confirmation" 
                               class="inscription-input" placeholder="Veuillez répéter votre mot de passe" required>
                    </div>
                    
                    <!-- Case à cocher PMR (Personne à mobilité réduite) -->
                    <div class="form-group-checkbox">
                        <label class="inscription-label">
                            <input type="checkbox" name="pmr" class="inscription-checkbox" 
                                   <?php echo isset($_POST['pmr']) && !empty($this->erreurs) ? 'checked' : ''; ?>> 
                            PMR (Personne à mobilité réduite)
                        </label>
                    </div>
                    
                    <!-- Bouton pour soumettre le formulaire -->
                    <button type="submit" class="inscription-button">S'inscrire</button>
                </form>
            </div>
        </main>
        <?php
    }

    /**
     * Surcharge de la méthode afficherHero pour personnaliser le sous-titre
     */
    protected function afficherHero($sousTitre = null)
    {
        // Si aucun sous-titre n'est fourni, on utilise celui spécifique à l'inscription
        if ($sousTitre === null) {
            $sousTitre = "Rejoignez-nous et accédez à des événements exclusifs !";
        }
        
        // Appel de la méthode parente avec le sous-titre personnalisé
        parent::afficherHero($sousTitre);
    }
}