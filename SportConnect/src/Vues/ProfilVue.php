<?php

namespace App\Vues;

use App\Controleur\NavbarControleur;

/**
 * Classe ProfilVue
 * Gère l'affichage du profil utilisateur (informations, événements réservés/créés, etc.).
 */
class ProfilVue
{
    /**
     * @var string Titre de la page
     */
    private $titre;

    /**
     * @var array Données de l'utilisateur connecté
     */
    private $utilisateur;

    /**
     * @var array Liste des réservations de l'utilisateur
     */
    private $reservations;

    /**
     * @var array Liste des sports disponibles
     */
    private $sports;

    /**
     * @var array Liste des localisations existantes
     */
    private $localisations;

    /**
     * @var array Liste des événements créés par l'utilisateur
     */
    private $evenementsCrees;

    /**
     * Constructeur
     *
     * @param string $titre         Le titre de la page (ex. "Profil").
     * @param array  $utilisateur   Les données de l'utilisateur connecté.
     * @param array  $reservations  Les réservations de l'utilisateur (facultatif).
     * @param array  $sports        Les sports disponibles (facultatif).
     * @param array  $localisations Les localisations disponibles (facultatif).
     * @param array  $evenementsCrees Les événements créés par l'utilisateur (facultatif).
     */
    public function __construct(
        string $titre,
        array $utilisateur,
        array $reservations = [],
        array $sports = [],
        array $localisations = [],
        array $evenementsCrees = []
    ) {
        $this->titre           = $titre;
        $this->utilisateur     = $utilisateur;
        $this->reservations    = $reservations;
        $this->sports          = $sports;
        $this->localisations   = $localisations;
        $this->evenementsCrees = $evenementsCrees;
    }

    /**
     * Affiche la page du profil
     *
     * @return void
     */
    public function afficher(): void
    {
        // Récupération des liens de la navbar
        $navbarControleur = new NavbarControleur();
        $liensNavbar = $navbarControleur->obtenirLiens('profil', $this->utilisateur !== null);

        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <title><?php echo htmlspecialchars($this->titre); ?></title>
            <link rel="stylesheet" href="CSS/style.css">
        </head>
        <body>

            <!-- NAVBAR -->
            <nav class="navbar">
                <div class="container">
                    <a href="?page=accueil" class="logo">
                        <img src="assets/logo.png" alt="SportConnect Logo" class="navbar-logo">
                        <span>SportConnect</span>
                    </a>
                    <ul class="nav-links">
                        <?php foreach ($liensNavbar as $nom => $lien): ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($lien['url']); ?>"
                                   class="<?php echo $lien['actif'] ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($nom); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>

                        <?php if ($this->utilisateur): ?>
                            <li class="user-info">
                                Bienvenue, <?php echo htmlspecialchars($this->utilisateur['pseudo']); ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>

            <!-- HERO -->
            <header class="hero">
                <h1>Bienvenue, <?php echo htmlspecialchars($this->utilisateur['pseudo']); ?></h1>
            </header>

            <!-- MESSAGES DE SUCCÈS/ERREUR -->
            <div class="container">
                <?php if (isset($_SESSION['messageReussite'])): ?>
                    <div class="messageReussite">
                        <?php echo htmlspecialchars($_SESSION['messageReussite']); ?>
                    </div>
                    <?php unset($_SESSION['messageReussite']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['messageErreur'])): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($_SESSION['messageErreur']); ?>
                    </div>
                    <?php unset($_SESSION['messageErreur']); ?>
                <?php endif; ?>
            </div>

            <!-- CONTENU PRINCIPAL -->
            <main class="container main-profil">

                <!-- INFORMATIONS UTILISATEUR -->
                <section class="profil-details">
                    <h2 class="profil-title">Vos informations</h2>
                    <div class="profil-info">
                        <p>
                            <strong>Prénom :</strong>
                            <span><?php echo htmlspecialchars($this->utilisateur['prenom']); ?></span>
                        </p>
                        <p>
                            <strong>Email :</strong>
                            <span><?php echo htmlspecialchars($this->utilisateur['email']); ?></span>
                        </p>
                        <p>
                            <strong>PMR :</strong>
                            <span><?php echo $this->utilisateur['pmr'] ? 'Oui' : 'Non'; ?></span>
                        </p>
                    </div>
                </section>

                <!-- ÉVÉNEMENTS RÉSERVÉS ET CRÉÉS -->
                <section class="profil-evenements">
                    <h2>Vos événements</h2>

                    <?php
                    $aDesReservations = !empty($this->reservations);
                    $aDesCreations    = !empty($this->evenementsCrees);
                    ?>

                    <?php if ($aDesReservations || $aDesCreations): ?>

                        <!-- Événements réservés -->
                        <?php if ($aDesReservations): ?>
                            <h3>Événements réservés</h3>
                            <ul class="event-list">
                                <?php foreach ($this->reservations as $evenement): ?>
                                    <li class="event-item">
                                        <strong>Nom :</strong>
                                        <?php echo htmlspecialchars($evenement['nom_evenement']); ?><br>

                                        <strong>Date :</strong>
                                        <?php echo htmlspecialchars($evenement['date_evenement']); ?><br>

                                        <strong>Description :</strong>
                                        <?php echo htmlspecialchars($evenement['description']); ?><br>

                                        <strong>Localisation :</strong>
                                        <?php echo htmlspecialchars($evenement['localisation']); ?><br>

                                        <!-- Formulaire de suppression de réservation -->
                                        <form method="POST" action="?page=reservation&action=supprimer" class="form-supprimer">
                                            <input type="hidden" name="id_evenement"
                                                   value="<?php echo htmlspecialchars($evenement['id_evenement']); ?>">
                                            <button type="submit" class="btn-supprimer">
                                                Supprimer
                                            </button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <hr>
                        <?php endif; ?>

                        <!-- Événements créés -->
                        <?php if ($aDesCreations): ?>
                            <h3>Événements créés</h3>
                            <ul class="event-list">
                                <?php foreach ($this->evenementsCrees as $evenement): ?>
                                    <li class="event-item">
                                        <strong>Nom :</strong>
                                        <?php echo htmlspecialchars($evenement['nom_evenement']); ?><br>

                                        <strong>Date :</strong>
                                        <?php echo htmlspecialchars($evenement['date_evenement']); ?><br>

                                        <strong>Description :</strong>
                                        <?php echo htmlspecialchars($evenement['description']); ?><br>

                                        <strong>Localisation :</strong>
                                        <?php echo htmlspecialchars($evenement['localisation']); ?><br>

                                        <!-- Formulaire de suppression d'un événement créé -->
                                        <form method="POST"
                                              action="?page=evenement&action=supprimer_evenement_creer_utilisateur"
                                              class="form-supprimer">
                                            <input type="hidden" name="id_evenement"
                                                   value="<?php echo htmlspecialchars($evenement['id_evenement']); ?>">
                                            <button type="submit" class="btn-supprimer">
                                                Supprimer
                                            </button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                    <?php else: ?>
                        <p>Vous n'avez participé à aucun événement ni créé d'événements pour le moment.</p>
                    <?php endif; ?>
                </section>

                <!-- FORMULAIRE DE CRÉATION D'ÉVÉNEMENT -->
                <section class="creer-evenement">
                    <h2>Créer un nouvel événement</h2>
                    <form method="POST" action="?page=evenement&action=creer_evenement" class="form-creer-evenement">

                        <!-- Nom de l'événement -->
                        <div class="form-group">
                            <label for="nom_evenement">Nom de l'événement</label>
                            <input type="text"
                                   id="nom_evenement"
                                   name="nom_evenement"
                                   placeholder="Nom de l'événement"
                                   required />
                        </div>

                        <!-- Date de l'événement -->
                        <div class="form-group">
                            <label for="date_evenement">Date</label>
                            <input type="date"
                                   id="date_evenement"
                                   name="date_evenement"
                                   required />
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description"
                                      name="description"
                                      placeholder="Description de l'événement"></textarea>
                        </div>

                        <!-- Sport -->
                        <div class="form-group">
                            <label for="id_sport">Sport</label>
                            <select id="id_sport" name="id_sport" required>
                                <option value="">Sélectionner un sport</option>
                                <?php foreach ($this->sports as $sport): ?>
                                    <option value="<?php echo htmlspecialchars($sport['id_sport']); ?>">
                                        <?php echo htmlspecialchars($sport['nom_sport']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Localisation -->
                        <fieldset class="form-fieldset">
                            <legend>Localisation</legend>

                            <!-- Localisation existante -->
                            <div class="form-group">
                                <label>
                                    <input type="radio" name="localisation_type" value="existante" checked />
                                    Utiliser une localisation existante
                                </label>
                                <select id="id_localisation" name="id_localisation">
                                    <option value="">Sélectionner une localisation</option>
                                    <?php foreach ($this->localisations as $localisation): ?>
                                        <option value="<?php echo htmlspecialchars($localisation['id_localisation']); ?>">
                                            <?php echo htmlspecialchars(
                                                $localisation['nom_localisation_evenement'] . ", " . $localisation['ville']
                                            ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Nouvelle localisation -->
                            <div class="form-group">
                                <label>
                                    <input type="radio" name="localisation_type" value="nouvelle" />
                                    Ajouter une nouvelle localisation
                                </label>
                                <div id="nouvelle-localisation-fields" class="nouvelle-localisation-fields">
                                    <div class="form-group">
                                        <label for="nom_localisation_evenement">Nom de la localisation</label>
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
                        </fieldset>

                        <!-- Accessibilité PMR -->
                        <div class="form-group">
                            <label for="pmr_accessible">Accessibilité PMR</label>
                            <select id="pmr_accessible" name="pmr_accessible">
                                <option value="1">Oui</option>
                                <option value="0">Non</option>
                            </select>
                        </div>

                        <!-- Montant -->
                        <div class="form-group">
                            <label for="montant">Montant (€)</label>
                            <input type="number"
                                   id="montant"
                                   name="montant"
                                   step="0.01"
                                   min="0"
                                   placeholder="Montant" />
                        </div>

                        <!-- Bouton de création -->
                        <button type="submit" class="btn-creer">Créer l'événement</button>
                    </form>
                </section>

            </main>

            <!-- FOOTER -->
            <footer class="footer">
                <p>&copy; 2024 SportConnect &ndash; Tous droits réservés.</p>
            </footer>

            <!-- SCRIPT JS -->
            <script src="JS/script.js"></script>
        </body>
        </html>
        <?php
    }
}
