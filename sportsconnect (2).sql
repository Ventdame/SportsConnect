-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : lun. 17 mars 2025 à 10:28
-- Version du serveur : 8.0.31
-- Version de PHP : 8.1.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `sportsconnect`
--

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `id_avis_utilisateur` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `id_evenement` int NOT NULL,
  `note` int DEFAULT NULL,
  `commentaire` text
) ;

-- --------------------------------------------------------

--
-- Structure de la table `avis_evenement`
--

CREATE TABLE `avis_evenement` (
  `id_avis_evenement` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `id_evenement` int NOT NULL,
  `note` int DEFAULT NULL,
  `commentaire` text
) ;

--
-- Déchargement des données de la table `avis_evenement`
--

INSERT INTO `avis_evenement` (`id_avis_evenement`, `id_utilisateur`, `id_evenement`, `note`, `commentaire`) VALUES
(1, 1, 1, 4, 'Très bon tournoi, bien organisé.'),
(2, 2, 2, 5, 'Super expérience, à refaire !'),
(3, 3, 3, 3, 'Bien, mais il y avait des problèmes logistiques.'),
(4, 4, 4, 5, 'Parfait pour une initiation.');

-- --------------------------------------------------------

--
-- Structure de la table `avis_utilisateur`
--

CREATE TABLE `avis_utilisateur` (
  `id_avis_utilisateur` int NOT NULL,
  `id_utilisateur_receveur` int NOT NULL,
  `note` int DEFAULT NULL,
  `commentaire` text,
  `id_utilisateur_redacteur` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `categories_sports`
--

CREATE TABLE `categories_sports` (
  `id_categorie` int NOT NULL,
  `nom_categorie` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `categories_sports`
--

INSERT INTO `categories_sports` (`id_categorie`, `nom_categorie`) VALUES
(1, 'Sports de Ballon'),
(2, 'Raquettes'),
(3, 'Sports Collectifs'),
(4, 'Sports Aquatiques');

-- --------------------------------------------------------

--
-- Structure de la table `equipe`
--

CREATE TABLE `equipe` (
  `id_equipe` int NOT NULL,
  `nom_equipe` varchar(50) NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_sport` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `equipe`
--

INSERT INTO `equipe` (`id_equipe`, `nom_equipe`, `date_creation`, `id_sport`) VALUES
(1, 'Les Champions', '2024-12-01 00:00:00', 1),
(2, 'Les Tennis Stars', '2024-11-15 00:00:00', 2),
(3, 'Team Dunk', '2024-12-10 00:00:00', 3),
(4, 'Padel Pros', '2024-12-20 00:00:00', 4);

-- --------------------------------------------------------

--
-- Structure de la table `evenements`
--

CREATE TABLE `evenements` (
  `id_evenement` int NOT NULL,
  `nom_evenement` varchar(50) NOT NULL,
  `date_evenement` datetime NOT NULL,
  `id_sport` int NOT NULL,
  `id_localisation` int DEFAULT NULL,
  `description` text,
  `MONTANT` decimal(10,2) NOT NULL,
  `id_utilisateur` int NOT NULL,
  `max_participants` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `evenements`
--

INSERT INTO `evenements` (`id_evenement`, `nom_evenement`, `date_evenement`, `id_sport`, `id_localisation`, `description`, `MONTANT`, `id_utilisateur`, `max_participants`) VALUES
(6, 'Match de Tennis', '2025-02-10 10:00:00', 2, 3, 'Partie amicale ouverte à tous', 20.00, 2, NULL),
(7, 'Compétition de Basketball', '2025-03-20 18:00:00', 3, 2, 'Tournoi de basketball pour clubs amateurs', 25.00, 3, NULL),
(8, 'Session de Padel', '2025-04-05 10:30:00', 4, 4, 'Session d\'initiation au padel', 15.00, 4, NULL),
(9, 'Tournoi de Volleyball', '2025-02-15 10:00:00', 5, 1, 'Tournoi pour amateurs et pros.', 0.00, 0, NULL),
(10, 'Match de Badminton', '2025-03-10 18:00:00', 6, 2, 'Compétition régionale.', 0.00, 0, NULL),
(11, 'Championnat de Handball', '2025-04-12 14:00:00', 7, 3, 'Championnat inter-clubs.', 0.00, 0, NULL),
(12, 'Duel d\'Escrime', '2025-05-01 16:00:00', 8, 4, 'Duel amical entre champions.', 0.00, 0, NULL),
(13, 'Match de Rugby', '2025-06-20 15:00:00', 9, 5, 'Match entre clubs locaux.', 0.00, 1, NULL),
(14, 'Meeting d\'Athlétisme', '2025-07-25 09:00:00', 10, 6, 'Rencontre nationale des clubs.', 0.00, 0, NULL),
(15, 'Compétition de Judo', '2025-08-14 10:30:00', 11, 7, 'Compétition amicale.', 0.00, 0, NULL),
(16, 'Championnat de Natation', '2025-09-05 11:00:00', 12, 8, 'Championnat régional de natation.', 0.00, 0, NULL),
(17, 'Open de Tennis de Table', '2025-10-15 14:30:00', 13, 9, 'Tournoi national pour amateurs.', 0.00, 0, NULL),
(18, 'Tournoi d\'Escalade', '2025-11-20 10:00:00', 14, 10, 'Tournoi sur mur artificiel.', 0.00, 0, NULL),
(34, 'Test tournois', '1999-07-16 00:00:00', 10, 8, 'test', 10.00, 60, 10);

-- --------------------------------------------------------

--
-- Structure de la table `feedbacks_suggestions`
--

CREATE TABLE `feedbacks_suggestions` (
  `id_feedback` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `contenu` text NOT NULL,
  `date_feedback` datetime DEFAULT CURRENT_TIMESTAMP,
  `statut` enum('En attente','Valider','Refuser') DEFAULT 'En attente'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `feedbacks_suggestions`
--

INSERT INTO `feedbacks_suggestions` (`id_feedback`, `id_utilisateur`, `contenu`, `date_feedback`, `statut`) VALUES
(1, 1, 'Ajouter plus de matchs de basketball.', '2024-12-27 11:08:34', 'En attente'),
(2, 2, 'Organiser des événements en soirée.', '2024-12-27 11:08:34', 'En attente'),
(3, 3, 'Proposer des réductions pour les nouveaux utilisateurs.', '2024-12-27 11:08:34', 'En attente'),
(4, 4, 'Augmenter la fréquence des événements.', '2024-12-27 11:08:34', 'En attente');

-- --------------------------------------------------------

--
-- Structure de la table `localisations_evenements`
--

CREATE TABLE `localisations_evenements` (
  `id_localisation` int NOT NULL,
  `nom_localisation_evenement` varchar(50) NOT NULL,
  `ville` varchar(50) NOT NULL,
  `adresse` varchar(100) NOT NULL,
  `code_postal` varchar(10) DEFAULT NULL,
  `pmr_accessible` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `localisations_evenements`
--

INSERT INTO `localisations_evenements` (`id_localisation`, `nom_localisation_evenement`, `ville`, `adresse`, `code_postal`, `pmr_accessible`) VALUES
(1, 'Stade Municipal', 'Bruxelles', '123 Rue de Paris', '75000', 0),
(2, 'Gymnase Lyonnais', 'Tubize', '45 Rue des Sports', '69000', 0),
(3, 'Club Roland Garros', 'Anderlecht', '1 Avenue Gordon Bennett', '75016', 0),
(4, 'Complexe Aquatique', 'Rebecq', '99 Boulevard Méditerranée', '13008', 0),
(5, 'Stade Ernest-Wallon', 'Toulouse', '', NULL, 0),
(6, 'Stade Charléty', 'Paris', '', NULL, 0),
(7, 'Gymnase Jean-Mermoz', 'Lyon', '', NULL, 0),
(8, 'Piscine Municipale', 'Marseille', '', NULL, 0),
(9, 'Salle Pierre-Mauroy', 'Lille', '', NULL, 0),
(10, 'Mur d\'Escalade Vertical\'Art', 'Grenoble', '', NULL, 0),
(11, 'Omnisport ', 'Tubize', 'Rue du bailli ', '1480', 0);

-- --------------------------------------------------------

--
-- Structure de la table `membres_equipe`
--

CREATE TABLE `membres_equipe` (
  `id_membre` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `id_equipe` int NOT NULL,
  `role` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `membres_equipe`
--

INSERT INTO `membres_equipe` (`id_membre`, `id_utilisateur`, `id_equipe`, `role`) VALUES
(1, 1, 1, 'Capitaine'),
(2, 2, 2, 'Membre'),
(3, 3, 3, 'Membre'),
(4, 4, 4, 'Capitaine');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id_message` int NOT NULL,
  `id_expediteur` int NOT NULL,
  `id_destinataire` int NOT NULL,
  `contenu` text NOT NULL,
  `date_envoi` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id_message`, `id_expediteur`, `id_destinataire`, `contenu`, `date_envoi`) VALUES
(1, 1, 2, 'Salut, es-tu prêt pour le tournoi ?', '2024-12-27 11:08:16'),
(2, 2, 1, 'Oui, je suis impatient !', '2024-12-27 11:08:16'),
(3, 3, 4, 'Bienvenue dans notre équipe !', '2024-12-27 11:08:16'),
(4, 4, 3, 'Merci, ravi de rejoindre !', '2024-12-27 11:08:16');

-- --------------------------------------------------------

--
-- Structure de la table `participants_evenement`
--

CREATE TABLE `participants_evenement` (
  `id_participant` int NOT NULL,
  `id_evenement` int NOT NULL,
  `id_equipe` int DEFAULT NULL,
  `id_utilisateur` int NOT NULL,
  `statut` enum('en attente','valider') DEFAULT 'en attente'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `payements`
--

CREATE TABLE `payements` (
  `id_payement` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date_payement` datetime DEFAULT CURRENT_TIMESTAMP,
  `methode_payement` varchar(50) NOT NULL,
  `statut` enum('en attente','payer','refuser') DEFAULT 'en attente',
  `id_evenement` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `payements`
--

INSERT INTO `payements` (`id_payement`, `id_utilisateur`, `montant`, `date_payement`, `methode_payement`, `statut`, `id_evenement`) VALUES
(1, 1, 30.00, '2024-12-27 11:08:43', 'Carte Bancaire', 'payer', 1),
(2, 2, 20.00, '2024-12-27 11:08:43', 'Paypal', 'payer', 2),
(3, 3, 25.00, '2024-12-27 11:08:43', 'Carte Bancaire', 'en attente', 3),
(4, 4, 15.00, '2024-12-27 11:08:43', 'Carte Bancaire', 'payer', 4);

-- --------------------------------------------------------

--
-- Structure de la table `sports`
--

CREATE TABLE `sports` (
  `id_sport` int NOT NULL,
  `nom_sport` varchar(50) NOT NULL,
  `id_categorie` int DEFAULT NULL,
  `pmr` tinyint(1) NOT NULL DEFAULT '0',
  `genre` enum('F','H','Mixte','') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Mixte'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `sports`
--

INSERT INTO `sports` (`id_sport`, `nom_sport`, `id_categorie`, `pmr`, `genre`) VALUES
(1, 'Football', 1, 0, 'H'),
(2, 'Tennis', 2, 0, 'Mixte'),
(3, 'Basketball', 1, 0, 'Mixte'),
(4, 'Padel', 2, 0, 'Mixte'),
(5, 'Volleyball', 1, 0, 'Mixte'),
(6, 'Badminton', 2, 0, 'Mixte'),
(7, 'Handball', 1, 0, 'Mixte'),
(8, 'Escrime', 3, 0, 'Mixte'),
(9, 'Rugby', 1, 0, 'Mixte'),
(10, 'Athlétisme', 3, 0, 'Mixte'),
(11, 'Judo', 3, 0, 'Mixte'),
(12, 'Natation', 4, 0, 'H'),
(13, 'Ping-Pong', 2, 0, 'Mixte'),
(14, 'Escalade', 3, 0, 'Mixte');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id_utilisateur` int NOT NULL,
  `pseudo` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `date_inscription` datetime DEFAULT CURRENT_TIMESTAMP,
  `pmr` tinyint(1) NOT NULL DEFAULT '0',
  `role` enum('administrateur','user','') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'user',
  `sexe` enum('H','F','A','') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'A'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id_utilisateur`, `pseudo`, `prenom`, `nom`, `email`, `mot_de_passe`, `date_inscription`, `pmr`, `role`, `sexe`) VALUES
(48, 'Ventdame', 'Anthony', '', 'anthony040497@msn.com', '$2y$10$X7zhusNizUKdUvQ1I8FsaOQ5qSeNHYsUCXttgRcyXa18yBsvvPrq2', '2025-03-07 16:49:00', 1, 'user', 'F'),
(47, 'FabriceMahieu', 'Fabrice', '', 'FabriceMahieu@gmail.com', '$2y$10$PQcogBDYSZcIl1YEB2rY.e2tqY2eXSiOGn8TiZjRMDwXd0UT9QZWG', '2025-01-17 17:16:01', 1, 'user', ''),
(60, 'Malgore', 'Anthony', '', 'ap8.sas@outlook.com', '$2y$10$MzLhEV5dfb2WC/sSyvW3xOqIXZB/4mbg8ivS/vaYXppK9GtjDCaKG', '2025-03-14 17:20:50', 1, 'user', 'H'),
(58, 'Ventdamee', 'Anthony', '', 'ap6.sas@outlook.com', '$2y$10$nF/cJwn5p3MGcl0OR7/1z.tK/oo3Br.hUv1TLIfA.jHvuijmBwv4W', '2025-03-14 17:12:01', 1, 'user', 'H'),
(57, 'test', 'test', '', 'ventdameee@outlook.com', '$2y$10$QHRe3hj0BftWizKpVjsXp.Oc6gWtCpr4Eps7FoYWo.NxcTeA25NUe', '2025-03-14 17:11:38', 1, 'user', 'H'),
(56, 'anthonyprlii', 'Anthony', '', 'anthonyprli@outlook.com', '$2y$10$gIR3/RT2jlqHhqtdHm2TXezcqZF2K4aaDtUJr6lSFUORqzH2Gxeri', '2025-03-14 17:08:18', 1, 'user', 'F');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`id_avis_utilisateur`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_evenement` (`id_evenement`);

--
-- Index pour la table `avis_evenement`
--
ALTER TABLE `avis_evenement`
  ADD PRIMARY KEY (`id_avis_evenement`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_evenement` (`id_evenement`);

--
-- Index pour la table `avis_utilisateur`
--
ALTER TABLE `avis_utilisateur`
  ADD PRIMARY KEY (`id_avis_utilisateur`);

--
-- Index pour la table `categories_sports`
--
ALTER TABLE `categories_sports`
  ADD PRIMARY KEY (`id_categorie`),
  ADD UNIQUE KEY `nom_categorie` (`nom_categorie`);

--
-- Index pour la table `equipe`
--
ALTER TABLE `equipe`
  ADD PRIMARY KEY (`id_equipe`),
  ADD KEY `id_sport` (`id_sport`);

--
-- Index pour la table `evenements`
--
ALTER TABLE `evenements`
  ADD PRIMARY KEY (`id_evenement`),
  ADD KEY `id_localisation` (`id_localisation`),
  ADD KEY `fk_id_sport` (`id_sport`);

--
-- Index pour la table `feedbacks_suggestions`
--
ALTER TABLE `feedbacks_suggestions`
  ADD PRIMARY KEY (`id_feedback`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `localisations_evenements`
--
ALTER TABLE `localisations_evenements`
  ADD PRIMARY KEY (`id_localisation`);

--
-- Index pour la table `membres_equipe`
--
ALTER TABLE `membres_equipe`
  ADD PRIMARY KEY (`id_membre`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_equipe` (`id_equipe`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `id_expediteur` (`id_expediteur`),
  ADD KEY `id_destinataire` (`id_destinataire`);

--
-- Index pour la table `participants_evenement`
--
ALTER TABLE `participants_evenement`
  ADD PRIMARY KEY (`id_participant`),
  ADD KEY `id_evenement` (`id_evenement`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `payements`
--
ALTER TABLE `payements`
  ADD PRIMARY KEY (`id_payement`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `sports`
--
ALTER TABLE `sports`
  ADD PRIMARY KEY (`id_sport`),
  ADD UNIQUE KEY `nom_sport` (`nom_sport`),
  ADD KEY `id_categorie` (`id_categorie`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `pseudo` (`pseudo`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `id_avis_utilisateur` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `avis_evenement`
--
ALTER TABLE `avis_evenement`
  MODIFY `id_avis_evenement` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `avis_utilisateur`
--
ALTER TABLE `avis_utilisateur`
  MODIFY `id_avis_utilisateur` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `categories_sports`
--
ALTER TABLE `categories_sports`
  MODIFY `id_categorie` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `equipe`
--
ALTER TABLE `equipe`
  MODIFY `id_equipe` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `evenements`
--
ALTER TABLE `evenements`
  MODIFY `id_evenement` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT pour la table `feedbacks_suggestions`
--
ALTER TABLE `feedbacks_suggestions`
  MODIFY `id_feedback` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `localisations_evenements`
--
ALTER TABLE `localisations_evenements`
  MODIFY `id_localisation` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `membres_equipe`
--
ALTER TABLE `membres_equipe`
  MODIFY `id_membre` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id_message` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `participants_evenement`
--
ALTER TABLE `participants_evenement`
  MODIFY `id_participant` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=172;

--
-- AUTO_INCREMENT pour la table `payements`
--
ALTER TABLE `payements`
  MODIFY `id_payement` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `sports`
--
ALTER TABLE `sports`
  MODIFY `id_sport` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id_utilisateur` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
