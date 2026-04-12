<?php
require_once __DIR__ . '/includes/config.php';

// Check if trying to fetch data via API (optional for future dynamic loads)
// but for now we embed JSON directly.

// Fetch spots from the database safely
try {
    $mysqli = connecter_db();
    
    // Check if table exists first (in case user hasn't run spots.sql yet)
    $val = $mysqli->query("SELECT 1 FROM spots LIMIT 1");
    if($val !== FALSE) {
        $result = $mysqli->query("SELECT * FROM spots ORDER BY ville, nom");
        $spots = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $spots[] = $row;
            }
        }
    } else {
        // Table doesn't exist yet
        $spots = [];
        $db_error = true;
    }
    $mysqli->close();
} catch (Exception $e) {
    $spots = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Carte des Spots — <?php echo SITE_NAME; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
  .spots-container {
    min-height: 100vh;
    background: var(--noir);
    padding: 2rem;
    display: flex;
    flex-direction: column;
  }
  .spots-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(255,215,0,0.1);
  }
  .spots-title h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2.5rem;
    color: var(--blanc);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .spots-title p {
    color: var(--gris-clair);
    margin: 0.5rem 0 0 0;
  }
  .spots-nav a {
    color: var(--gris-clair);
    text-decoration: none;
    padding: 0.5rem 1rem;
    transition: all 0.2s;
    border: 1px solid rgba(255,215,0,0.2);
    margin-left: 0.5rem;
  }
  .spots-nav a:hover, .spots-nav a.active {
    color: var(--jaune);
    border-color: var(--jaune);
  }
  
  .map-wrapper {
    flex: 1;
    width: 100%;
    min-height: 520px;
    height: 68vh;
    border: 2px solid rgba(255,215,0,0.2);
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    clip-path: polygon(20px 0%, 100% 0%, 100% calc(100% - 20px), calc(100% - 20px) 100%, 0% 100%, 0% 20px);
  }
  
  #map {
    width: 100%;
    min-height: 520px;
    height: 100%;
    z-index: 1;
  }
  
  .map-mode-hint {
    font-family: 'Syne', sans-serif;
    font-size: 0.8rem;
    color: var(--gris-clair);
    margin: 0 0 0.75rem 0;
  }
  .map-mode-hint strong {
    color: var(--jaune);
  }
  
  /* Leaflet Overrides for Dark Theme */
  .leaflet-popup-content-wrapper {
    background: var(--noir3);
    color: var(--blanc);
    border: 1px solid var(--jaune);
    border-radius: 4px;
    clip-path: polygon(8px 0%, 100% 0%, 100% calc(100% - 8px), calc(100% - 8px) 100%, 0% 100%, 0% 8px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.8);
  }
  .leaflet-popup-tip {
    background: var(--jaune);
  }
  .leaflet-container a.leaflet-popup-close-button {
    color: var(--gris-clair);
  }
  .leaflet-container a.leaflet-popup-close-button:hover {
    color: var(--jaune);
  }
  
  .custom-pin {
    font-size: 2rem;
    text-shadow: 0 2px 5px rgba(0,0,0,0.5);
    transition: transform 0.2s;
  }
  .custom-pin:hover {
    transform: scale(1.2);
  }
  
  .spot-popup {
    padding: 0.5rem;
  }
  .spot-popup h3 {
    font-family: 'Bebas Neue', sans-serif;
    color: var(--jaune);
    margin: 0 0 0.2rem 0;
    font-size: 1.5rem;
    letter-spacing: 1px;
  }
  .spot-city {
    font-family: 'Space Mono', monospace;
    font-size: 0.75rem;
    color: var(--gris-clair);
    text-transform: uppercase;
    margin-bottom: 0.8rem;
    display: inline-block;
    background: rgba(255,255,255,0.05);
    padding: 2px 6px;
    border-radius: 2px;
  }
  .spot-popup p {
    font-family: 'Syne', sans-serif;
    font-size: 0.9rem;
    color: #e0e0e0;
    line-height: 1.4;
    margin-bottom: 1rem;
  }
  .btn-itineraire {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    background: var(--jaune);
    color: var(--noir) !important;
    text-decoration: none;
    font-weight: bold;
    font-family: 'Syne', sans-serif;
    text-transform: uppercase;
    font-size: 0.8rem;
    transition: background 0.2s;
  }
  .btn-itineraire:hover {
    background: var(--vert);
  }
  
  .db-error {
    background: rgba(220, 53, 69, 0.1);
    color: #ff6b6b;
    padding: 1rem;
    border-left: 4px solid #ff6b6b;
    margin-bottom: 1.5rem;
  }
</style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-trail" id="cursorTrail"></div>

<div class="spots-container">
  <div class="spots-header">
    <div class="spots-title">
      <h1><span style="color:var(--vert)">📍</span> CARTE DES SPOTS</h1>
      <p>Trouve ton terrain de jeu, rejoins la communauté RAKH Pulse</p>
    </div>
    <div class="spots-nav">
      <?php if(is_logged_in()): ?>
        <a href="profile.php">Mon Profil</a>
      <?php endif; ?>
      <a href="index.php">← Retour Accueil</a>
    </div>
  </div>
  
  <?php if(isset($db_error) && $db_error): ?>
    <div class="db-error">
      <strong>Attention :</strong> La table <code>spots</code> n'existe pas encore dans la base de données. 
      Veuillez exécuter le fichier <code>spots.sql</code> dans votre phpMyAdmin pour afficher les spots réels.
    </div>
  <?php endif; ?>

  <p class="map-mode-hint"><strong>Vue satellite</strong> par défaut — icône des couches en haut à droite pour repasser en <strong>plan sombre</strong>.</p>
  <div class="map-wrapper">
    <div id="map"></div>
  </div>
</div>

<script src="assets/js/script.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    // Initialiser la carte centrée sur le Sénégal (Dakar)
    var attrOsmCarto = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &mdash; <a href="https://carto.com/attributions">CARTO</a>';
    var attrEsri = 'Imagerie &copy; <a href="https://www.esri.com/">Esri</a> &mdash; sources Esri, Maxar, Earthstar, IGN, …';

    var satelliteBase = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: attrEsri,
        maxZoom: 20
    });
    var satelliteLabels = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
        attribution: attrEsri,
        maxZoom: 20,
        opacity: 0.85
    });
    var darkBase = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: attrOsmCarto,
        subdomains: 'abcd',
        maxZoom: 20
    });

    var satelliteLayer = L.layerGroup([satelliteBase, satelliteLabels]);

    var map = L.map('map', {
        zoomControl: true,
        layers: [satelliteLayer]
    }).setView([14.7167, -17.4677], 12);

    var baseLayers = {
        'Satellite': satelliteLayer,
        'Plan sombre': darkBase
    };

    L.control.layers(baseLayers, null, {position: 'topright', collapsed: false}).addTo(map);

    map.whenReady(function() {
        map.invalidateSize();
    });

    // Récupérer les données PHP (ou tableau vide si table non créée)
    var spots = <?php echo json_encode($spots); ?>;

    // Créer une icône de marqueur personnalisée (emoji📍 au lieu de l'image par défaut)
    var customIcon = L.divIcon({
      className: 'custom-pin',
      html: '📍',
      iconSize: [30, 30],
      iconAnchor: [15, 30], // pointe vers le bas
      popupAnchor: [0, -30] // popup au-dessus du marqueur
    });

    if (spots.length > 0) {
        // Ajouter un marqueur pour chaque spot
        spots.forEach(function(spot) {
            if(spot.latitude && spot.longitude) {
                var marker = L.marker([spot.latitude, spot.longitude], {icon: customIcon}).addTo(map);
                
                var popupContent = `
                    <div class="spot-popup">
                        <h3>${spot.nom}</h3>
                        <div class="spot-city">${spot.ville}</div>
                        <p>${spot.description}</p>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=${spot.latitude},${spot.longitude}" class="btn-itineraire" target="_blank">Y aller 🚀</a>
                    </div>
                `;
                marker.bindPopup(popupContent);
            }
        });
        
        // Ajuster la vue pour voir tous les spots
        if(spots.length > 1) {
            var bounds = L.latLngBounds(spots.map(s => [s.latitude, s.longitude]));
            map.fitBounds(bounds, {padding: [50, 50]});
        }
    } else {
        // Fallback s'il n'y a pas de données (ex: table non créée)
        var fallbackMarker = L.marker([14.6937, -17.4704], {icon: customIcon}).addTo(map);
        fallbackMarker.bindPopup(`
            <div class="spot-popup">
                <h3>Corniche Ouest</h3>
                <div class="spot-city">Dakar (Aperçu)</div>
                <p>Ceci est un spot d'exemple. Exécutez le script SQL pour afficher les vrais spots de la communauté.</p>
            </div>
        `).openPopup();
    }
</script>
</body>
</html>
