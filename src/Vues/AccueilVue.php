<?php

namespace App\Vues;

use App\Core\VueBase;

/**
 * Classe AccueilVue
 * 
 * Vue pour la page d'accueil
 */
class AccueilVue extends VueBase
{
    /**
     * Liste des sports disponibles
     * 
     * @var array
     */
    private $sports;

    /**
     * Constructeur de AccueilVue
     * 
     * @param string $titre Le titre de la page
     * @param array $sports Liste des sports disponibles
     * @param array|null $utilisateur Informations sur l'utilisateur connecté (si connecté)
     * @param array $erreurs Liste des erreurs éventuelles
     */
    public function __construct($titre, $sports = [], $utilisateur = null, $erreurs = [])
    {
        parent::__construct($titre, [], $erreurs);
        $this->sports = $sports;
        
        // Si l'utilisateur a été passé explicitement, on le stocke
        if ($utilisateur !== null) {
            $this->utilisateur = $utilisateur;
        }
    }

    /**
     * Affiche le contenu spécifique de la page d'accueil
     */
    protected function afficherContenu()
    {
        ?>
        <main id="sports-section">
            <section class="sports-section">
                <h2>Sports disponibles</h2>
                <h3>Quelques secondes suffisent pour reserver une activié !</h3>
                <?php if (!empty($this->sports)): ?>
                    <div class="sports-list">
                        <?php foreach ($this->sports as $sport): ?>
                            <div class="sport-card">
                                <img src="assets/sports/<?php echo strtolower(str_replace(' ', '_', $sport)); ?>.png" 
                                     alt="<?php echo $this->e($sport); ?>" 
                                     class="sport-image">
                                <h3 class="sport-title"><?php echo $this->e($sport); ?></h3>
                                <a href="?page=reservation&sport=<?php echo urlencode($sport); ?>" class="cta-button">
                                    Explorer les événements
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Aucun sport disponible pour le moment.</p>
                <?php endif; ?>
            </section>
        </main>
        <?php
    }
}