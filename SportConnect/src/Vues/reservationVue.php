<?php

namespace App\Vues;

use App\Controleur\NavbarControleur;

class ReservationVue
{
    private $titre;          // Le titre de la page, généralement "Réservations"
    private $reservations;   // La liste des réservations disponibles
    private $sports;         // La liste des sports disponibles
    private $utilisateur;    // Les informations de l'utilisateur connecté

    /**
     * Constructeur de la classe ReservationVue
     *
     * Initialise les propriétés de la vue, y compris le titre de la page, la liste des réservations,
     * la liste des sports, et les informations de l'utilisateur connecté.
     *
     * @param string $titre Le titre de la page (généralement "Réservations").
     * @param array $reservations Liste des événements disponibles pour la réservation.
     * @param array $sports Liste des sports disponibles.
     * @param array|null $utilisateur Données de l'utilisateur connecté, null si non connecté.
     */
    public function __construct($titre, $reservations, $sports, $utilisateur = null)
    {
        $this->titre = $titre;           // Initialise le titre de la page
        $this->reservations = $reservations;  // Initialise la liste des réservations
        $this->sports = $sports;             // Initialise la liste des sports
        $this->utilisateur = $utilisateur;   // Initialise les données utilisateur
    }

    /**
     * Méthode pour afficher la vue des réservations
     *
     * Génère et affiche la page HTML avec la liste des réservations, le filtre de recherche,
     * et les informations de l'utilisateur connecté, si disponible.
     */
    public function afficher()
    {
        // Crée un objet NavbarControleur pour obtenir les liens dynamiques de la barre de navigation
        $navbarControleur = new NavbarControleur();
        // Obtient les liens de la barre de navigation en fonction de l'état de connexion
        $liensNavbar = $navbarControleur->obtenirLiens('reservation', $this->utilisateur !== null);
?>
        <!DOCTYPE html>
        <html lang="fr">

        <head>
            <meta charset="UTF-8"> <!-- Définit le jeu de caractères utilisé -->
            <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Permet un affichage responsive -->
            <title><?php echo htmlspecialchars($this->titre); ?></title> <!-- Affiche le titre de la page -->
            <link rel="stylesheet" href="CSS/style.css"> <!-- Lien vers le fichier de style CSS -->
        </head>

        <body>
            <!-- Navbar : barre de navigation en haut de la page -->
            <nav class="navbar">
                <div class="container">
                    <a href="?page=accueil" class="logo">
                        <img src="assets/logo.png" alt="SportConnect Logo" class="navbar-logo"> <!-- Logo du site -->
                        <span>SportConnect</span> <!-- Nom du site -->
                    </a>
                    <ul class="nav-links">
                        <!-- Génère dynamiquement les liens de la barre de navigation -->
                        <?php foreach ($liensNavbar as $nom => $lien): ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($lien['url']); ?>"
                                    class="<?php echo $lien['actif'] ? 'active' : ''; ?>"> <!-- Lien actif si la page correspond -->
                                    <?php echo htmlspecialchars($nom); ?> <!-- Affiche le nom du lien -->
                                </a>
                            </li>
                        <?php endforeach; ?>

                        <!-- Affiche les informations de l'utilisateur si connecté -->
                        <?php if ($this->utilisateur): ?>
                            <li class="user-info">Bienvenue, <?php echo htmlspecialchars($this->utilisateur['pseudo']); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>

            <!-- Header : section contenant le titre principal et un sous-titre -->
            <header class="hero">
                <h1><?php echo htmlspecialchars($this->titre); ?></h1> <!-- Affiche le titre de la page -->
                <p>Découvrez les événements et réservez votre place dès maintenant !</p> <!-- Sous-titre -->
            </header>

            <!-- Contenu principal : section de filtre de recherche pour trouver des événements -->
            <main>
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
                                <!-- Liste déroulante des sports disponibles -->
                                <?php foreach ($this->sports as $sport): ?>
                                    <option value="<?php echo htmlspecialchars($sport); ?>">
                                        <?php echo htmlspecialchars($sport); ?>
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

                <!-- Affichage du message de succès -->
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


                <!-- Liste des événements disponibles -->
                <section class="reservations">
                    <h2>Événements disponibles</h2>
                    <div id="reservation-list" class="reservation-list">
                        <?php if (!empty($this->reservations)) : ?>
                            <?php foreach ($this->reservations as $reservation): ?>
                                <div class="reservation-item">
                                    <h3 class="reservation-title">
                                        <?php echo htmlspecialchars($reservation['evenement'] ?? 'Nom de l\'événement non défini'); ?>
                                    </h3>
                                    <p class="reservation-detail">
                                        <strong>Date :</strong> <?php echo htmlspecialchars($reservation['date'] ?? 'Date non définie'); ?>
                                    </p>
                                    <p class="reservation-detail">
                                        <strong>Description :</strong> <?php echo htmlspecialchars($reservation['description'] ?? 'Aucune description disponible'); ?>
                                    </p>
                                    <p class="reservation-detail">
                                        <strong>Lieu :</strong> <?php echo htmlspecialchars($reservation['localisation'] ?? 'Lieu non défini'); ?>
                                    </p>
                                    <p class="reservation-detail">
                                        <strong>Accessibilité PMR :</strong> <?php echo htmlspecialchars($reservation['pmr_accessible'] ?? 'Non spécifié'); ?>
                                    </p>
                                    <p class="reservation-detail">
                                        <strong>Prix :</strong> <?php echo htmlspecialchars($reservation['prix'] ?? 'Prix non défini'); ?>
                                    </p>

                                    <!-- Bouton "Réserver" -->
                                    <form method="POST" action="?page=reservation&action=reserver">
                                        <?php if (isset($reservation['id_evenement'])): ?>
                                            <input type="hidden" name="id_evenement" value="<?php echo htmlspecialchars($reservation['id_evenement']); ?>">
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

            <!-- Footer : section en bas de la page -->
            <footer class="footer">
                <p>&copy; 2024 SportConnect - Tous droits réservés, fais avec amour ❤</p> <!-- Copyright -->
            </footer>

            <!-- Lien vers le fichier JavaScript -->
            <script src="JS/script.js"></script> <!-- Script JS pour le fonctionnement de la page -->
        </body>

        </html>
<?php
    }
}
