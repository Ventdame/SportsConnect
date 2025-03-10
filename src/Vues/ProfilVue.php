<?php

namespace App\Vues;

use App\Core\VueBase;

/**
 * Classe ProfilVue
 * 
 * Vue pour la page de profil utilisateur
 */
class ProfilVue extends VueBase
{
    /**
     * Liste des réservations de l'utilisateur
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
     * Liste des localisations existantes
     * 
     * @var array
     */
    private $localisations;

    /**
     * Liste des événements créés par l'utilisateur
     * 
     * @var array
     */
    private $evenementsCrees;

    /**
     * Constructeur de ProfilVue
     * 
     * @param string $titre Le titre de la page
     * @param array $utilisateur Les données de l'utilisateur connecté
     * @param array $reservations Les réservations de l'utilisateur
     * @param array $sports Les sports disponibles
     * @param array $localisations Les localisations disponibles
     * @param array $evenementsCrees Les événements créés par l'utilisateur
     * @param array $erreurs Liste des erreurs à afficher
     */
    public function __construct(
        string $titre,
        array $utilisateur,
        array $reservations = [],
        array $sports = [],
        array $localisations = [],
        array $evenementsCrees = [],
        array $erreurs = []
    ) {
        parent::__construct($titre, [], $erreurs);
        $this->utilisateur = $utilisateur;
        $this->reservations = $reservations;
        $this->sports = $sports;
        $this->localisations = $localisations;
        $this->evenementsCrees = $evenementsCrees;
    }

    /**
     * Surcharge de la méthode afficherHero pour personnaliser le contenu
     */
    protected function afficherHero($sousTitre = null)
    {
        ?>
        <header class="hero">
            <div class="hero-content">
                <h1>Bienvenue sur votre espace, <?php echo $this->e($this->utilisateur['prenom']); ?></h1>
                <p>Gérez vos réservations et créez de nouveaux événements sportifs</p>
            </div>
        </header>
        <?php
    }

    /**
     * Affiche le contenu spécifique de la page de profil
     */
    protected function afficherContenu()
    {
        ?>
        <!-- CONTENU PRINCIPAL -->
        <main class="container main-profil">
            <!-- Style pour la nouvelle mise en page en grille -->
            <style>
                .profil-grid {
                    display: grid;
                    grid-template-columns: 1fr 2fr;
                    gap: 20px;
                    margin-bottom: 30px;
                }
                
                .right-column {
                    display: flex;
                    flex-direction: column;
                    gap: 20px;
                }
                
                @media (max-width: 768px) {
                    .profil-grid {
                        grid-template-columns: 1fr;
                    }
                }
                
                .creer-evenement {
                    margin-top: 0;
                }
            </style>

            <!-- Nouvelle structure en grille -->
            <div class="profil-grid">
                <!-- Colonne de gauche -->
                <div class="left-column">
                    <!-- INFORMATIONS UTILISATEUR -->
                    <section class="profil-details">
                        <h2 class="profil-title"><i class="fa-solid fa-id-card"></i> Vos informations</h2>
                        <div class="profil-info">
                            <div class="info-group">
                                <div class="info-label">Pseudo</div>
                                <div class="info-value"><?php echo $this->e($this->utilisateur['pseudo']); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Prénom</div>
                                <div class="info-value"><?php echo $this->e($this->utilisateur['prenom']); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo $this->e($this->utilisateur['email']); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">PMR</div>
                                <div class="info-value">
                                    <?php if ($this->utilisateur['pmr']): ?>
                                        <span class="badge badge-success"><i class="fa-solid fa-wheelchair"></i> Oui</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Non</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- FORMULAIRE DE CRÉATION D'ÉVÉNEMENT -->
                    <section class="creer-evenement">
                        <h2 class="section-title"><i class="fa-solid fa-plus-circle"></i> Créer un nouvel événement</h2>
                
                <form method="POST" action="?page=evenement&action=creer_evenement" class="form-creer-evenement">
                    <div class="form-row">
                        <!-- Nom de l'événement -->
                        <div class="form-group">
                            <label for="nom_evenement"><i class="fa-solid fa-tag"></i> Nom de l'événement</label>
                            <input type="text"
                                id="nom_evenement"
                                name="nom_evenement"
                                placeholder="Nom de l'événement"
                                required />
                        </div>

                        <!-- Date de l'événement -->
                        <div class="form-group">
                            <label for="date_evenement"><i class="fa-solid fa-calendar"></i> Date</label>
                            <input type="date"
                                id="date_evenement"
                                name="date_evenement"
                                required />
                        </div>
                    </div>

                    <div class="form-row">
                        <!-- Sport -->
                        <div class="form-group">
                            <label for="id_sport"><i class="fa-solid fa-basketball"></i> Sport</label>
                            <select id="id_sport" name="id_sport" required>
                                <option value="">Sélectionner un sport</option>
                                <?php foreach ($this->sports as $sport): ?>
                                    <option value="<?php echo $this->e($sport['id_sport']); ?>">
                                        <?php echo $this->e($sport['nom_sport']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Montant -->
                        <div class="form-group">
                            <label for="montant"><i class="fa-solid fa-euro-sign"></i> Montant</label>
                            <input type="number"
                                id="montant"
                                name="montant"
                                step="0.01"
                                min="0"
                                placeholder="Montant en €" />
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <!-- Nombre maximum de participants -->
                        <div class="form-group">
                            <label for="max_participants"><i class="fa-solid fa-users"></i> Nombre maximum de participants</label>
                            <input type="number"
                                id="max_participants"
                                name="max_participants"
                                min="1"
                                value="10"
                                required />
                            <small class="form-text">Indiquez combien de personnes pourront participer à cet événement</small>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description"><i class="fa-solid fa-align-left"></i> Description</label>
                        <textarea id="description"
                                name="description"
                                placeholder="Description de l'événement"
                                rows="4"></textarea>
                    </div>

                    <!-- Accessibilité PMR -->
                    <div class="form-group">
                        <label for="pmr_accessible"><i class="fa-solid fa-wheelchair"></i> Accessibilité PMR</label>
                        <select id="pmr_accessible" name="pmr_accessible">
                            <option value="1">Oui</option>
                            <option value="0">Non</option>
                        </select>
                    </div>

                    <!-- Localisation -->
                    <fieldset class="form-fieldset">
                        <legend><i class="fa-solid fa-location-dot"></i> Localisation</legend>

                        <div class="radio-options">
                            <!-- Localisation existante -->
                            <div class="radio-option">
                                <input type="radio" id="loc-existante" name="localisation_type" value="existante" checked />
                                <label for="loc-existante">Utiliser une localisation existante</label>

                                <div class="option-content">
                                    <select id="id_localisation" name="id_localisation">
                                        <option value="">Sélectionner une localisation</option>
                                        <?php foreach ($this->localisations as $localisation): ?>
                                            <option value="<?php echo $this->e($localisation['id_localisation']); ?>">
                                                <?php echo $this->e($localisation['nom_localisation_evenement'] . ", " . $localisation['ville']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Nouvelle localisation -->
                            <div class="radio-option">
                                <input type="radio" id="loc-nouvelle" name="localisation_type" value="nouvelle" />
                                <label for="loc-nouvelle">Ajouter une nouvelle localisation</label>

                                <div class="option-content" id="nouvelle-localisation-fields">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="nom_localisation_evenement">Nom</label>
                                            <input type="text"
                                                id="nom_localisation_evenement"
                                                name="nom_localisation_evenement"
                                                placeholder="Nom de la localisation" />
                                        </div>
                                        <div class="form-group">
                                            <label for="ville">Ville</label>
                                            <input type="text"
                                                id="ville"
                                                name="ville"
                                                placeholder="Ville" />
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="adresse">Adresse</label>
                                            <input type="text"
                                                id="adresse"
                                                name="adresse"
                                                placeholder="Adresse" />
                                        </div>
                                        <div class="form-group">
                                            <label for="code_postal">Code postal</label>
                                            <input type="text"
                                                id="code_postal"
                                                name="code_postal"
                                                placeholder="Code postal" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Bouton de création -->
                    <div class="form-actions">
                        <button type="submit" class="btn-creer">
                            <i class="fa-solid fa-plus-circle"></i> Créer l'événement
                        </button>
                    </div>
                </form>
                    </section>
                </div>

                <!-- Colonne de droite -->
                <div class="right-column">
                    <!-- ÉVÉNEMENTS RÉSERVÉS ET CRÉÉS -->
                    <section class="profil-evenements">
                        <h2><i class="fa-solid fa-calendar-check"></i> Vos événements</h2>

                        <?php
                        $aDesReservations = !empty($this->reservations);
                        $aDesCreations    = !empty($this->evenementsCrees);
                        ?>

                        <div class="tab-container">
                            <div class="tab-header">
                                <div class="tab-btn <?php echo $aDesReservations ? 'active' : ''; ?>" data-tab="tab-reservations">
                                    <i class="fa-solid fa-ticket"></i> Réservations 
                                    <span class="counter"><?php echo count($this->reservations); ?></span>
                                </div>
                                <div class="tab-btn <?php echo !$aDesReservations && $aDesCreations ? 'active' : ''; ?>" data-tab="tab-creations">
                                    <i class="fa-solid fa-plus-circle"></i> Événements créés
                                    <span class="counter"><?php echo count($this->evenementsCrees); ?></span>
                                </div>
                            </div>

                            <div class="tab-content">
                                <!-- Onglet des réservations -->
                                <div id="tab-reservations" class="tab-pane <?php echo $aDesReservations ? 'active' : ''; ?>">
                                    <?php if ($aDesReservations): ?>
                                        <div class="event-cards">
                                            <?php foreach ($this->reservations as $evenement): ?>
                                                <div class="event-card">
                                                    <div class="event-card-header">
                                                        <strong><?php echo $this->e($evenement['nom_evenement']); ?></strong>
                                                    </div>
                                                    <div class="event-card-content">
                                                        <div class="event-detail">
                                                            <div class="label"><i class="fa-solid fa-calendar"></i> Date</div>
                                                            <div><?php echo $this->e($evenement['date_evenement']); ?></div>
                                                        </div>
                                                        <div class="event-detail">
                                                            <div class="label"><i class="fa-solid fa-basketball"></i> Sport</div>
                                                            <div><?php echo $this->e($evenement['sport'] ?? 'Non spécifié'); ?></div>
                                                        </div>
                                                        <div class="event-detail">
                                                            <div class="label"><i class="fa-solid fa-location-dot"></i> Lieu</div>
                                                            <div><?php echo $this->e($evenement['localisation']); ?></div>
                                                        </div>
                                                        <?php if (!empty($evenement['description'])): ?>
                                                        <div class="event-detail full-width">
                                                            <div class="label"><i class="fa-solid fa-align-left"></i> Description</div>
                                                            <div><?php echo $this->e($evenement['description']); ?></div>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="event-card-footer">
                                                        <form method="POST" action="?page=reservation&action=supprimer" class="form-supprimer">
                                                            <input type="hidden" name="id_evenement" value="<?php echo $this->e($evenement['id_evenement']); ?>">
                                                            <button type="submit" class="btn-supprimer">
                                                                <i class="fa-solid fa-trash"></i> Supprimer
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="fa-solid fa-calendar-xmark empty-icon"></i>
                                            <p style="color: red; font-weight: bold;">Vous n'avez pas de réservations</p>
                                            <a href="?page=reservation" class="cta-button">Découvrir les événements</a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Onglet des événements créés -->
                                <div id="tab-creations" class="tab-pane <?php echo !$aDesReservations && $aDesCreations ? 'active' : ''; ?>">
                                    <?php if ($aDesCreations): ?>
                                        <div class="event-cards">
                                            <?php foreach ($this->evenementsCrees as $evenement): ?>
                                                <div class="event-card">
                                                    <div class="event-card-header">
                                                        <strong><?php echo $this->e($evenement['nom_evenement']); ?></strong>
                                                        <span class="event-badge">Créateur</span>
                                                    </div>
                                                    <div class="event-card-content">
                                                        <div class="event-detail">
                                                            <div class="label"><i class="fa-solid fa-calendar"></i> Date</div>
                                                            <div><?php echo $this->e($evenement['date_evenement']); ?></div>
                                                        </div>
                                                        <div class="event-detail">
                                                            <div class="label"><i class="fa-solid fa-basketball"></i> Sport</div>
                                                            <div><?php echo $this->e($evenement['sport'] ?? 'Non spécifié'); ?></div>
                                                        </div>
                                                        <div class="event-detail">
                                                            <div class="label"><i class="fa-solid fa-location-dot"></i> Lieu</div>
                                                            <div><?php echo $this->e($evenement['localisation']); ?></div>
                                                        </div>
                                                        <?php if (!empty($evenement['description'])): ?>
                                                        <div class="event-detail full-width">
                                                            <div class="label"><i class="fa-solid fa-align-left"></i> Description</div>
                                                            <div><?php echo $this->e($evenement['description']); ?></div>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="event-card-footer">
                                                        <form method="POST" action="?page=evenement&action=supprimer_evenement_creer_utilisateur" class="form-supprimer">
                                                            <input type="hidden" name="id_evenement" value="<?php echo $this->e($evenement['id_evenement']); ?>">
                                                            <button type="submit" class="btn-supprimer">
                                                                <i class="fa-solid fa-trash"></i> Supprimer
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="fa-solid fa-calendar-plus empty-icon"></i>
                                            <p style="color: red; font-weight: bold;">Vous n'avez aucuns événements créés</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </main>
        <?php
    }

    /**
     * Surcharge de la méthode afficher pour ajouter les scripts spécifiques à la page de profil
     */
    public function afficher($styles = [], $scripts = [], $scriptsFooter = [])
    {
        // Ajout du script JavaScript pour les onglets et la gestion des localisations
        $scriptsFooter[] = 'JS/script.js';
        
        parent::afficher($styles, $scripts, $scriptsFooter);
        
        // Script inline spécifique à la page de profil
        ?>
        <script>
            // Script pour l'interaction des onglets
            document.addEventListener('DOMContentLoaded', function() {
                const tabBtns = document.querySelectorAll('.tab-btn');
                
                tabBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        // Supprimer la classe active de tous les boutons et contenus
                        document.querySelectorAll('.tab-btn, .tab-pane').forEach(el => {
                            el.classList.remove('active');
                        });
                        
                        // Ajouter la classe active au bouton cliqué
                        this.classList.add('active');
                        
                        // Afficher le contenu correspondant
                        const tabId = this.getAttribute('data-tab');
                        document.getElementById(tabId).classList.add('active');
                    });
                });

                // Gestion de l'affichage des champs de nouvelle localisation
                const radioExistante = document.getElementById('loc-existante');
                const radioNouvelle = document.getElementById('loc-nouvelle');
                const champsNouvelleLocalisation = document.getElementById('nouvelle-localisation-fields');

                function updateLocalisationFields() {
                    if (radioNouvelle.checked) {
                        champsNouvelleLocalisation.style.display = 'block';
                    } else {
                        champsNouvelleLocalisation.style.display = 'none';
                    }
                }

                radioExistante.addEventListener('change', updateLocalisationFields);
                radioNouvelle.addEventListener('change', updateLocalisationFields);
                
                // Initialisation au chargement
                updateLocalisationFields();
            });
        </script>
        <?php
    }
}