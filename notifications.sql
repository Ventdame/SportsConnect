-- Structure de la table `notifications`
CREATE TABLE `notifications` (
  `id_notification` int NOT NULL AUTO_INCREMENT,
  `id_utilisateur_destinataire` int NOT NULL,
  `id_utilisateur_source` int NOT NULL,
  `id_evenement` int NOT NULL,
  `contenu` text NOT NULL,
  `date_notification` datetime DEFAULT CURRENT_TIMESTAMP,
  `lu` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_notification`),
  KEY `id_utilisateur_destinataire` (`id_utilisateur_destinataire`),
  KEY `id_utilisateur_source` (`id_utilisateur_source`),
  KEY `id_evenement` (`id_evenement`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Trigger pour les suppressions d'événements
DELIMITER $$
CREATE TRIGGER after_event_delete
AFTER DELETE ON evenements
FOR EACH ROW
BEGIN
    INSERT INTO notifications (id_utilisateur_destinataire, id_utilisateur_source, id_evenement, contenu)
    SELECT p.id_utilisateur, OLD.id_utilisateur, OLD.id_evenement, 
           CONCAT('L\'événement "', OLD.nom_evenement, '" a été annulé')
    FROM participants p
    WHERE p.id_evenement = OLD.id_evenement
    AND p.id_utilisateur != OLD.id_utilisateur;
END$$
DELIMITER ;