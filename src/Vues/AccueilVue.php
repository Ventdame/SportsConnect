<?php

namespace App\Vues;

use App\Core\VueBase;

class AccueilVue extends VueBase
{
    private $sports;
    private $estPMR;

    public function __construct(
        string $titre, 
        array $sports = [], 
        array $utilisateur = null, 
        array $erreurs = [],
        bool $estPMR = false
    ) {
        parent::__construct($titre, [], $erreurs);
        $this->sports = $sports;
        $this->estPMR = $estPMR;
        
        if ($utilisateur !== null) {
            $this->utilisateur = $utilisateur;
        }
    }

    protected function afficherHero($sousTitre = null)
    {
        if ($sousTitre === null) {
            $sousTitre = "Explorez et rejoignez des événements sportifs près de chez vous !";
        }
        
        ?>
        <header class="hero">
            <h1><?php echo htmlspecialchars($this->titre); ?></h1>
            <p><?php echo htmlspecialchars($sousTitre); ?></p>
        </header>
        <?php
    }

    protected function afficherContenu()
    {
        ?>
        <main>
            <!-- Section des avantages - Placée juste après le hero sans margin -->
            <section class="features-section">
                <div class="container">
                    <h2>Pourquoi choisir SportConnect ?</h2>
                    <div class="features-grid">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <h3>Communauté</h3>
                            <p>Rejoignez une communauté de sportifs passionnés et partagez des moments inoubliables.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fa-solid fa-calendar-check"></i>
                            </div>
                            <h3>Événements</h3>
                            <p>Trouvez facilement des événements sportifs près de chez vous adaptés à votre niveau.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fa-solid fa-map-marker-alt"></i>
                            </div>
                            <h3>Proximité</h3>
                            <p>Découvrez les meilleures installations sportives dans votre région.</p>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Section des sports -->
            <section class="sports-section">
                <div class="container">
                    <h2>Sports disponibles</h2>
                    <p class="sports-intro">Quelques secondes suffisent pour réserver une activité !</p>
                    
                    <?php if ($this->estPMR): ?>
                        <div class="pmr-message">
                            <i class="fa-solid fa-wheelchair"></i>
                            <p>Nous vous présentons ci-dessous les sports adaptés aux personnes à mobilité réduite. 
                            Notre objectif est de rendre le sport accessible à tous !</p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($this->sports)): ?>
                        <div class="sports-categories">
                            <?php 
                            // Limiter à 6 sports par page pour une meilleure présentation
                            $sportsAffichés = array_slice($this->sports, 0, 6); 
                            ?>
                            <div class="sports-list">
                                <?php foreach ($sportsAffichés as $sport): ?>
                                    <?php 
                                    // Extraire le nom du sport
                                    $nomSport = is_array($sport) ? ($sport['nom_sport'] ?? 'Sport inconnu') : $sport;
                                    $idSport = is_array($sport) ? ($sport['id_sport'] ?? '0') : '0';
                                    ?>
                                    <div class="sport-card">
                                        <div class="sport-image-container">
                                            <img src="assets/sports/<?php echo strtolower(str_replace(' ', '_', $nomSport)); ?>.png" 
                                                alt="<?php echo $this->e($nomSport); ?>" 
                                                class="sport-image"
                                                onerror="this.src='assets/sports/default.png';">
                                        </div>
                                        <h3 class="sport-title"><?php echo $this->e($nomSport); ?></h3>
                                        <p class="sport-description">Rejoignez des événements de <?php echo $this->e($nomSport); ?> et rencontrez d'autres passionnés.</p>
                                        <a href="?page=reservation&sport=<?php echo urlencode($nomSport); ?>" class="cta-button">
                                            Explorer
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if (count($this->sports) > 6): ?>
                                <div class="voir-plus-container">
                                    <a href="?page=reservation" class="voir-plus-button">
                                        Voir tous les sports <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-sports-message">Aucun sport disponible pour le moment.</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
        <?php
    }
}