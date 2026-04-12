-- Tables pour le système de suivi des programmes d'entraînement interactifs

-- 1. Table des programmes en cours par utilisateur
CREATE TABLE IF NOT EXISTS `user_programmes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `programme_id` varchar(50) NOT NULL, -- ex: 'street-power', 'endurance'
  `date_debut` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('en_cours','termine','abandonne') NOT NULL DEFAULT 'en_cours',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table de l'historique des séances (logs complets)
CREATE TABLE IF NOT EXISTS `workout_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `programme_id` varchar(50) NOT NULL,
  `semaine` int(11) NOT NULL,
  `jour` int(11) NOT NULL,
  `date_completion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE,
  -- Eviter de logger le même jour de la même semaine plusieurs fois pour le même programme
  UNIQUE KEY `unique_workout` (`user_id`, `programme_id`, `semaine`, `jour`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mettre à jour la table des utilisateurs pour ajouter un système de streaks ou points plus tard si besoin.
