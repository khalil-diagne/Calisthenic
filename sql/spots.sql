-- ==============================================
-- Table des spots de Calisthenics au Sénégal
-- ==============================================

CREATE TABLE IF NOT EXISTS spots (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom VARCHAR(100) NOT NULL,
  description TEXT,
  latitude DECIMAL(10,8) NOT NULL,
  longitude DECIMAL(11,8) NOT NULL,
  ville VARCHAR(50) NOT NULL,
  image_url VARCHAR(255) DEFAULT 'default_spot.jpg',
  date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insérer quelques spots fictifs par défaut
INSERT INTO spots (nom, description, latitude, longitude, ville) VALUES
('Corniche Ouest - Fann', 'Spot très populaire. Plusieurs barres de traction de la petite à la grande, barres parallèles, monkey bars. Très fréquenté entre 17h et 19h.', 14.6865, -17.4691, 'Dakar'),
('Plage de la BCEAO - Yoff', 'Barres de traction artisanales installées sur la plage de sable fin. Idéal pour s\'entraîner avant de plonger dans l\'océan.', 14.7610, -17.4474, 'Dakar'),
('Parcours Sportif Corniche (Olympique)', 'Équipements récents avec tout ce dont on a besoin pour un entraînement complet. Vue magnifique sur l\'Océan Atlantique.', 14.6937, -17.4704, 'Dakar'),
('Place de l\'Agora - Thiès', 'Une structure faite maison par les athlètes de Thiès avec des barres solides. Communauté super bienveillante.', 14.7936, -16.9248, 'Thiès'),
('Saint-Louis Plage Nord', 'Entraînement calisthenics avec vue sur la mer. Parfait pour les handstands sur le sable et le travail de force brute.', 16.0326, -16.5028, 'Saint-Louis');
