-- Table pour sauvegarder les programmes générés par l'IA Coach
CREATE TABLE IF NOT EXISTS ia_programmes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  niveau VARCHAR(50) NOT NULL,
  objectif VARCHAR(100) NOT NULL,
  parties_corps VARCHAR(255) NOT NULL,
  jours_semaine INT NOT NULL DEFAULT 3,
  contenu MEDIUMTEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
