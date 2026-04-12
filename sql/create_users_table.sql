-- ==============================================
-- Base de données pour CALISTHENICS SENEGAL
-- Inscription et gestion utilisateurs
-- ==============================================

-- Table des régions
CREATE TABLE IF NOT EXISTS regions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom VARCHAR(50) NOT NULL UNIQUE,
  code VARCHAR(10)
);

-- Table des niveaux d'expérience
CREATE TABLE IF NOT EXISTS niveaux (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom VARCHAR(50) NOT NULL UNIQUE,
  description TEXT
);

-- Table des utilisateurs (table principale)
CREATE TABLE IF NOT EXISTS utilisateurs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom_complet VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  telephone VARCHAR(20),
  region_id INT,
  niveau_id INT,
  password VARCHAR(255) NOT NULL,
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  actif BOOLEAN DEFAULT TRUE,
  email_confirme BOOLEAN DEFAULT FALSE,
  
  FOREIGN KEY (region_id) REFERENCES regions(id),
  FOREIGN KEY (niveau_id) REFERENCES niveaux(id)
);

-- Insérer les régions
INSERT INTO regions (nom, code) VALUES
('Dakar', 'DK'),
('Thiès', 'TH'),
('Saint-Louis', 'SL'),
('Kaolack', 'KL'),
('Ziguinchor', 'ZG'),
('Tambacounda', 'TB'),
('Kolda', 'KD'),
('Matam', 'MT'),
('Louga', 'LG'),
('Fatick', 'FK'),
('Autre', 'AU');

-- Insérer les niveaux
INSERT INTO niveaux (nom, description) VALUES
('Débutant', 'Nouveau dans la calisthenics, moins de 1 mois'),
('Intermédiaire', 'Expérience de 1-6 mois'),
('Avancé', 'Expérience d\'au moins 6 mois à 1 an'),
('Expert', 'Expérience de plus d\'1 an'),
('Élite', 'Expert confirmé en calisthenics');

-- Index pour améliorer les performances
CREATE INDEX idx_email ON utilisateurs(email);
CREATE INDEX idx_region_id ON utilisateurs(region_id);
CREATE INDEX idx_niveau_id ON utilisateurs(niveau_id);
CREATE INDEX idx_actif ON utilisateurs(actif);
CREATE INDEX idx_date_creation ON utilisateurs(date_creation);

-- ==============================================
-- Procédures stockées utiles
-- ==============================================

-- Procédure pour créer un utilisateur
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS InsertUtilisateur(
  IN p_nom_complet VARCHAR(100),
  IN p_email VARCHAR(100),
  IN p_telephone VARCHAR(20),
  IN p_region VARCHAR(50),
  IN p_niveau VARCHAR(50),
  IN p_password VARCHAR(255)
)
BEGIN
  DECLARE v_region_id INT;
  DECLARE v_niveau_id INT;
  
  SELECT id INTO v_region_id FROM regions WHERE nom = p_region LIMIT 1;
  SELECT id INTO v_niveau_id FROM niveaux WHERE nom = p_niveau LIMIT 1;
  
  INSERT INTO utilisateurs (nom_complet, email, telephone, region_id, niveau_id, password)
  VALUES (p_nom_complet, p_email, p_telephone, v_region_id, v_niveau_id, p_password);
END //
DELIMITER ;

-- Procédure pour récupérer un utilisateur par email
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS GetUtilisateurByEmail(
  IN p_email VARCHAR(100)
)
BEGIN
  SELECT 
    u.id,
    u.nom_complet,
    u.email,
    u.telephone,
    r.nom AS region,
    n.nom AS niveau,
    u.password,
    u.date_creation,
    u.actif,
    u.email_confirme
  FROM utilisateurs u
  LEFT JOIN regions r ON u.region_id = r.id
  LEFT JOIN niveaux n ON u.niveau_id = n.id
  WHERE u.email = p_email;
END //
DELIMITER ;

-- Procédure pour mettre à jour le profil
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS UpdateUtilisateur(
  IN p_id INT,
  IN p_nom_complet VARCHAR(100),
  IN p_telephone VARCHAR(20),
  IN p_region VARCHAR(50),
  IN p_niveau VARCHAR(50)
)
BEGIN
  DECLARE v_region_id INT;
  DECLARE v_niveau_id INT;
  
  SELECT id INTO v_region_id FROM regions WHERE nom = p_region LIMIT 1;
  SELECT id INTO v_niveau_id FROM niveaux WHERE nom = p_niveau LIMIT 1;
  
  UPDATE utilisateurs
  SET 
    nom_complet = p_nom_complet,
    telephone = p_telephone,
    region_id = v_region_id,
    niveau_id = v_niveau_id
  WHERE id = p_id;
END //
DELIMITER ;

-- Procédure pour changer le mot de passe
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS ChangePassword(
  IN p_id INT,
  IN p_new_password VARCHAR(255)
)
BEGIN
  UPDATE utilisateurs
  SET password = p_new_password
  WHERE id = p_id;
END //
DELIMITER ;

-- Procédure pour confirmer email
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS ConfirmEmail(
  IN p_id INT
)
BEGIN
  UPDATE utilisateurs
  SET email_confirme = TRUE
  WHERE id = p_id;
END //
DELIMITER ;

-- ==============================================
-- Vues utiles
-- ==============================================

-- Vue pour afficher les utilisateurs avec leurs infos complètes
CREATE OR REPLACE VIEW utilisateurs_complet AS
SELECT 
  u.id,
  u.nom_complet,
  u.email,
  u.telephone,
  r.nom AS region,
  n.nom AS niveau,
  u.date_creation,
  u.date_modification,
  u.actif,
  u.email_confirme
FROM utilisateurs u
LEFT JOIN regions r ON u.region_id = r.id
LEFT JOIN niveaux n ON u.niveau_id = n.id;

-- Vue pour compter les utilisateurs par région
CREATE OR REPLACE VIEW utilisateurs_par_region AS
SELECT 
  r.nom AS region,
  COUNT(u.id) AS nombre_utilisateurs,
  COUNT(CASE WHEN u.actif = TRUE THEN 1 END) AS utilisateurs_actifs
FROM regions r
LEFT JOIN utilisateurs u ON r.id = u.region_id
GROUP BY r.id, r.nom;

-- Vue pour compter les utilisateurs par niveau
CREATE OR REPLACE VIEW utilisateurs_par_niveau AS
SELECT 
  n.nom AS niveau,
  COUNT(u.id) AS nombre_utilisateurs,
  COUNT(CASE WHEN u.actif = TRUE THEN 1 END) AS utilisateurs_actifs
FROM niveaux n
LEFT JOIN utilisateurs u ON n.id = u.niveau_id
GROUP BY n.id, n.nom;
