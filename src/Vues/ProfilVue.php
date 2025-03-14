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
            <!-- Nouvelle structure en grille -->
            <div class="profil-grid">
                <!-- Colonne de gauche -->
                <div class="left-column">
                    <!-- INFORMATIONS UTILISATEUR -->
                    <section class="profile-card">
                        <div class="section-header">
                            <i class="fa-solid fa-id-card"></i> Vos informations
                        </div>
                        <div class="profile-content">
                            <div class="profile-field">
                                <div class="profile-label">PSEUDO</div>
                                <div class="profile-value"><?php echo $this->e($this->utilisateur['pseudo']); ?></div>
                            </div>
                            <div class="profile-field">
                                <div class="profile-label">PRÉNOM</div>
                                <div class="profile-value"><?php echo $this->e($this->utilisateur['prenom']); ?></div>
                            </div>
                            <div class="profile-field">
                                <div class="profile-label">EMAIL</div>
                                <div class="profile-value"><?php echo $this->e($this->utilisateur['email']); ?></div>
                            </div>
                            <div class="profile-field">
                                <div class="profile-label">PMR</div>
                                <div class="profile-value">
                                    <?php if ($this->utilisateur['pmr']): ?>
                                        <span class="pmr-badge yes"><i class="fa-solid fa-wheelchair"></i> Oui</span>
                                    <?php else: ?>
                                        <span class="pmr-badge">Non</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- FORMULAIRE DE CRÉATION D'ÉVÉNEMENT -->
                    <section class="create-event-section">
                        <div class="section-header">
                            <i class="fa-solid fa-plus-circle"></i> Créer un nouvel événement
                        </div>
                        <div class="create-event-form">
                            <form method="POST" action="?page=evenement&action=creer_evenement" class="form-creer-evenement">
                                <div class="form-row">
                                    <!-- Nom de l'événement -->
                                    <div class="event-form-group">
                                        <label class="event-form-label" for="nom_evenement">
                                            <i class="fa-solid fa-tag"></i> Nom de l'événement
                                        </label>
                                        <input type="text"
                                            id="nom_evenement"
                                            name="nom_evenement"
                                            class="event-form-control"
                                            placeholder="Nom de l'événement"
                                            required />
                                    </div>

                                    <!-- Date de l'événement -->
                                    <div class="event-form-group">
                                        <label class="event-form-label" for="date_evenement">
                                            <i class="fa-solid fa-calendar"></i> Date
                                        </label>
                                        <input type="date"
                                            id="date_evenement"
                                            name="date_evenement"
                                            class="event-form-control"
                                            required />
                                    </div>
                                </div>

                                <div class="form-row">
                                    <!-- Sport -->
                                    <div class="event-form-group">
                                        <label class="event-form-label" for="id_sport">
                                            <i class="fa-solid fa-basketball"></i> Sport
                                        </label>
                                        <select id="id_sport" name="id_sport" class="event-form-control" required>
                                            <option value="">Sélectionner un sport</option>
                                            <?php foreach ($this->sports as $sport): ?>
                                                <option value="<?php echo $this->e($sport['id_sport']); ?>">
                                                    <?php echo $this->e($sport['nom_sport']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Montant -->
                                    <div class="event-form-group">
                                        <label class="event-form-label" for="montant">
                                            <i class="fa-solid fa-euro-sign"></i> Montant
                                        </label>
                                        <input type="number"
                                            id="montant"
                                            name="montant"
                                            class="event-form-control"
                                            step="0.01"
                                            min="0"
                                            placeholder="Montant en €" />
                                    </div>
                                </div>

                                <div class="form-row">
                                    <!-- Nombre maximum de participants -->
                                    <div class="event-form-group">
                                        <label class="event-form-label" for="max_participants">
                                            <i class="fa-solid fa-users"></i> Nombre maximum de participants
                                        </label>
                                        <input type="number"
                                            id="max_participants"
                                            name="max_participants"
                                            class="event-form-control"
                                            min="1"
                                            value="10"
                                            required />
                                        <small class="event-form-help">Indiquez combien de personnes pourront participer à cet événement</small>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="event-form-group">
                                    <label class="event-form-label" for="description">
                                        <i class="fa-solid fa-align-left"></i> Description
                                    </label>
                                    <textarea id="description"
                                        name="description"
                                        class="event-form-control"
                                        placeholder="Description de l'événement"
                                        rows="4"></textarea>
                                </div>

                                <!-- Accessibilité PMR -->
                                <div class="event-form-group">
                                    <label class="event-form-label" for="pmr_accessible">
                                        <i class="fa-solid fa-wheelchair"></i> Accessibilité PMR
                                    </label>
                                    <select id="pmr_accessible" name="pmr_accessible" class="event-form-control">
                                        <option value="1">Oui</option>
                                        <option value="0">Non</option>
                                    </select>
                                </div>

                                <!-- Localisation -->
                                <fieldset class="form-fieldset">
                                    <legend><i class="fa-solid fa-location-dot"></i> Localisation</legend>

                                    <div class="radio-group">
                                        <!-- Localisation existante -->
                                        <div class="radio-option">
                                            <input type="radio" id="loc-existante" name="localisation_type" value="existante" checked />
                                            <label class="radio-label" for="loc-existante">Utiliser une localisation existante</label>

                                            <div class="radio-content">
                                                <select id="id_localisation" name="id_localisation" class="event-form-control">
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
                                            <label class="radio-label" for="loc-nouvelle">Ajouter une nouvelle localisation</label>

                                            <div class="radio-content" id="nouvelle-localisation-fields">
                                                <div class="form-row">
                                                    <div class="event-form-group">
                                                        <label class="event-form-label" for="nom_localisation_evenement">Nom</label>
                                                        <input type="text"
                                                            id="nom_localisation_evenement"
                                                            name="nom_localisation_evenement"
                                                            class="event-form-control"
                                                            placeholder="Nom de la localisation" />
                                                    </div>
                                                    <div class="event-form-group">
                                                        <label class="event-form-label" for="ville">Ville</label>
                                                        <input type="text"
                                                            id="ville"
                                                            name="ville"
                                                            class="event-form-control"
                                                            placeholder="Ville" />
                                                    </div>
                                                </div>
                                                <div class="form-row">
                                                    <div class="event-form-group">
                                                        <label class="event-form-label" for="adresse">Adresse</label>
                                                        <input type="text"
                                                            id="adresse"
                                                            name="adresse"
                                                            class="event-form-control"
                                                            placeholder="Adresse" />
                                                    </div>
                                                    <div class="event-form-group">
                                                        <label class="event-form-label" for="code_postal">Code postal</label>
                                                        <input type="text"
                                                            id="code_postal"
                                                            name="code_postal"
                                                            class="event-form-control"
                                                            placeholder="Code postal" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                <!-- Bouton de création -->
                                <div class="form-actions">
                                    <button type="submit" class="btn-create">
                                        <i class="fa-solid fa-plus-circle"></i> Créer l'événement
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>

                <!-- Colonne de droite -->
                <div class="right-column">
                    <!-- ÉVÉNEMENTS RÉSERVÉS ET CRÉÉS -->
                    <section class="events-section">
                        <div class="section-header">
                            <i class="fa-solid fa-calendar-check"></i> Vos événements
                        </div>

                        <?php
                        $aDesReservations = !empty($this->reservations);
                        $aDesCreations    = !empty($this->evenementsCrees);
                        ?>

                        <div class="events-tabs">
                            <div class="event-tab <?php echo $aDesReservations ? 'active' : ''; ?>" data-tab="tab-reservations">
                                <i class="fa-solid fa-ticket"></i> Réservations
                                <span class="event-badge"><?php echo count($this->reservations); ?></span>
                            </div>
                            <div class="event-tab <?php echo !$aDesReservations && $aDesCreations ? 'active' : ''; ?>" data-tab="tab-creations">
                                <i class="fa-solid fa-plus-circle"></i> Événements créés
                                <span class="event-badge"><?php echo count($this->evenementsCrees); ?></span>
                            </div>
                        </div>

                        <div class="event-content">
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
                                                <!-- Remplacer le lien actuel par un formulaire -->
                                                <form method="POST" action="?page=evenement&action=supprimer_evenement_creer_utilisateur" style="display: inline;">
                                                    <input type="hidden" name="id_evenement" value="<?php echo $this->e($evenement['id_evenement']); ?>">
                                                    <button type="submit" class="btn-supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?');">
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
                const tabBtns = document.querySelectorAll('.event-tab');

                tabBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        // Supprimer la classe active de tous les boutons et contenus
                        document.querySelectorAll('.event-tab, .tab-pane').forEach(el => {
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
