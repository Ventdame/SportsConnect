-- Ajout du champ statut à la table evenements
ALTER TABLE `evenements` ADD `statut` ENUM('en attente', 'approuve', 'refuse') NOT NULL DEFAULT 'en attente' AFTER `max_participants`;

-- Mise à jour des événements existants pour leur donner le statut 'approuve' par défaut
UPDATE `evenements` SET `statut` = 'approuve' WHERE 1;