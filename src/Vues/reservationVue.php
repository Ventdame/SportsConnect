<?php

namespace App\Vues;

use App\Core\VueBase;

/**
 * Classe ReservationVue
 * 
 * Vue pour la page de réservation
 */
class ReservationVue extends VueBase
{
    /**
     * Liste des réservations disponibles
     * 
     * @var array
     */
    private $reservations;
    
    /**
     * Liste des sports disponibles
     * 
     * @var array
     */
    private $sports;

    /**
     * Constructeur de ReservationVue
     * 
     * @param string $titre Le titre de la page
     * @param array $reservations Liste des événements disponibles pour la réservation
     * @param array $sports Liste des sports disponibles
     * @param array|null $utilisateur Données de l'utilisateur connecté
     * @param array $erreurs Liste des erreurs
     */
    public function __construct($titre, $reservations, $sports, $utilisateur = null, $erreurs = [])
    {
        parent::__construct($titre, [], $erreurs);
        $this->reservations = $reservations;
        $this->sports = $sports;
        
        // Si l'utilisateur a été passé explicitement, on le stocke
        if ($utilisateur !== null) {
            $this->utilisateur = $utilisateur;
        }
    }

    /**
     * Affiche le contenu spécifique de la page de réservation
     */
    protected function afficherContenu()
    {
        // Déterminer la valeur PMR à insérer dans l'attribut data-pmr
        $statutPmr = (isset($this->utilisateur['pmr']) && $this->utilisateur['pmr'] === 'oui') ? 'oui' : 'non';
        ?>
        <main>
            <!-- Section de filtre de recherche -->
            <section class="search-filter">
                <form id="filter-form" class="filter-form">
                    <!-- Champ de recherche pour la ville -->
                    <div class="form-group">
                        <input type="text" id="filter-ville" name="ville" class="filter-input" placeholder="Ville">
                    </div>

                    <!-- Sélecteur pour choisir un sport -->
                    <div class="form-group">
                        <select id="filter-sport" name="sport" class="filter-select">
                            <option value="">Veuillez sélectionner un sport</option>
                            <?php 
                            foreach ($this->sports as $sport) :
                                // Selon la structure de $this->sports, adapter l'extraction du nom de sport
                                if (is_array($sport) && isset($sport['nom_sport'])) {
                                    $nomSport = $sport['nom_sport'];
                                } elseif (is_string($sport)) {
                                    $nomSport = $sport;
                                } else {
                                    $nomSport = "Sport inconnu";
                                }
                            ?>
                                <option value="<?php echo $this->e($nomSport); ?>">
                                    <?php echo $this->e($nomSport); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Champ de recherche pour la date -->
                    <div class="form-group">
                        <input type="date" id="filter-date" name="date" class="filter-input">
                    </div>

                    <!-- Bouton de soumission du formulaire -->
                    <div class="form-group">
                        <button type="submit" class="btn-search">Rechercher</button>
                    </div>
                </form>
            </section>

            <!-- Liste des événements disponibles -->
            <section class="reservations">
                <h2>Événements disponibles</h2>
                <div id="reservation-list" class="reservation-list">
                    <?php if (!empty($this->reservations)) : ?>
                        <?php foreach ($this->reservations as $reservation): ?>
                            <div class="reservation-item">
                                <h3 class="reservation-title">
                                    <?php echo $this->e($reservation['evenement'] ?? 'Nom de l\'événement non défini'); ?>
                                </h3>
                                <p class="reservation-detail">
                                    <strong>Date :</strong> 
                                    <?php echo $this->e($reservation['date'] ?? 'Date non définie'); ?>
                                </p>
                                <p class="reservation-detail">
                                    <strong>Description :</strong> 
                                    <?php echo $this->e($reservation['description'] ?? 'Aucune description disponible'); ?>
                                </p>
                                <p class="reservation-detail">
                                    <strong>Lieu :</strong> 
                                    <?php echo $this->e($reservation['localisation'] ?? 'Lieu non défini'); ?>
                                </p>
                                <p class="reservation-detail">
                                    <strong>Accessibilité PMR :</strong> 
                                    <?php echo $this->e($reservation['pmr_accessible'] ?? 'Non spécifié'); ?>
                                </p>
                                <p class="reservation-detail">
                                    <strong>Prix :</strong> 
                                    <?php 
                                        if (isset($reservation['prix'])) {
                                            echo $this->e($reservation['prix']) . '€';
                                        } else {
                                            echo 'Prix non défini';
                                        }
                                    ?>
                                </p>
                                <p class="reservation-detail">
                                    <strong>Participants :</strong> 
                                    <?php
                                    if (isset($reservation['Participants'])) {
                                        if (is_array($reservation['Participants'])) {
                                            $participantsSecurises = array_map([$this, 'e'], $reservation['Participants']);
                                            echo implode(', ', $participantsSecurises);
                                        } else {
                                            echo $this->e($reservation['Participants']);
                                        }
                                    } else {
                                        echo 'Aucun participant à cet événement';
                                    }
                                    ?>
                                </p>

                                <!-- Bouton "Réserver" -->
                                <form method="POST" action="?page=reservation&action=reserver">
                                    <?php if (isset($reservation['id_evenement'])): ?>
                                        <input type="hidden" name="id_evenement" 
                                               value="<?php echo $this->e($reservation['id_evenement']); ?>">
                                        <button type="submit" class="btn-reserver">Réserver</button>
                                    <?php else: ?>
                                        <p>Impossible de réserver cet événement (ID manquant).</p>
                                    <?php endif; ?>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun événement disponible pour les critères sélectionnés.</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
        <?php
    }

    /**
     * Surcharge de la méthode afficher pour ajouter l'attribut data-pmr au body
     */
    public function afficher($styles = [], $scripts = [], $scriptsFooter = [])
    {
        // Détermine le statut PMR pour l'attribut data-pmr du body
        $statutPmr = (isset($this->utilisateur['pmr']) && $this->utilisateur['pmr'] === 'oui') ? 'oui' : 'non';
        
        // Ajoute script.js pour la gestion des filtres
        $scriptsFooter[] = 'JS/script.js';
        
        parent::afficher($styles, $scripts, $scriptsFooter);
    }
}