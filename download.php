<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Télécharger l'app — Calisthenics Senegal</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">
<style>
  .download-container {
    min-height: 100vh;
    padding: 8rem 4rem;
    background: var(--noir);
  }
  .download-header {
    text-align: center;
    margin-bottom: 4rem;
  }
  .download-header h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(2.5rem, 6vw, 4rem);
    letter-spacing: 2px;
    margin-bottom: 1rem;
  }
  .download-header p {
    font-size: 1.1rem;
    color: var(--gris-clair);
    max-width: 600px;
    margin: 0 auto;
  }
  .download-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 3rem;
    max-width: 900px;
    margin: 0 auto;
  }
  .download-card {
    background: var(--noir3);
    border: 1px solid rgba(255,215,0,0.1);
    padding: 3rem;
    text-align: center;
    clip-path: polygon(16px 0%, 100% 0%, calc(100% - 16px) 100%, 0% 100%);
    transition: transform 0.3s;
  }
  .download-card:hover {
    transform: translateY(-6px);
  }
  .download-icon {
    font-size: 4rem;
    margin-bottom: 1.5rem;
  }
  .download-card h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2rem;
    margin-bottom: 1rem;
    color: var(--blanc);
  }
  .download-card p {
    color: var(--gris-clair);
    line-height: 1.6;
    margin-bottom: 2rem;
  }
  .download-btn {
    display: inline-block;
    padding: 0.9rem 2rem;
    background: var(--jaune);
    color: var(--noir);
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 0.9rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    border: none;
    cursor: pointer;
    clip-path: polygon(12px 0%, 100% 0%, calc(100% - 12px) 100%, 0% 100%);
    transition: background 0.2s;
    text-decoration: none;
  }
  .download-btn:hover {
    background: var(--vert);
  }
  .system-requirements {
    max-width: 900px;
    margin: 4rem auto 0;
    padding: 2rem;
    background: var(--noir3);
    border: 1px solid rgba(255,215,0,0.1);
  }
  .system-requirements h3 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    color: var(--jaune);
  }
  .requirements-list {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
  }
  .requirement-item {
    color: var(--gris-clair);
  }
  .requirement-item strong {
    color: var(--blanc);
  }
  .back-to-home {
    display: inline-block;
    margin-bottom: 2rem;
    color: var(--gris-clair);
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.2s;
  }
  .back-to-home:hover {
    color: var(--jaune);
  }
  @media (max-width: 768px) {
    .download-grid {
      grid-template-columns: 1fr;
    }
    .requirements-list {
      grid-template-columns: 1fr;
    }
    .download-container {
      padding: 5rem 1.5rem;
    }
  }
</style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-trail" id="cursorTrail"></div>

<div class="download-container">
  <a href="index.php" class="back-to-home">← Retour à l'accueil</a>
  
  <div class="download-header">
    <h1>TÉLÉCHARGE L'APP</h1>
    <p>Entraîne-toi n'importe où, n'importe quand avec l'application Calisthenics Senegal</p>
  </div>

  <div class="download-grid">
    <div class="download-card">
      <div class="download-icon">📱</div>
      <h2>iOS</h2>
      <p>Disponible sur l'App Store. Compatible avec iPhone 12 et versions récentes.</p>
      <a href="#" class="download-btn" onclick="handleDownload('iOS'); return false;">Télécharger iOS</a>
    </div>

    <div class="download-card">
      <div class="download-icon">🤖</div>
      <h2>Android</h2>
      <p>Disponible sur Google Play Store. Compatible avec Android 10+.</p>
      <a href="#" class="download-btn" onclick="handleDownload('Android'); return false;">Télécharger Android</a>
    </div>
  </div>

  <div class="system-requirements">
    <h3>Configuration requise</h3>
    <div class="requirements-list">
      <div class="requirement-item">
        <strong>iOS:</strong> iOS 14.5 ou version ultérieure, 150 MB d'espace
      </div>
      <div class="requirement-item">
        <strong>Android:</strong> Android 10 ou version ultérieure, 200 MB d'espace
      </div>
      <div class="requirement-item">
        <strong>Connexion:</strong> Internet (WiFi ou 4G recommandé)
      </div>
      <div class="requirement-item">
        <strong>RAM:</strong> Minimum 2 GB
      </div>
    </div>
  </div>
</div>

<script src="assets/js/script.js"></script>
<script>
  function handleDownload(platform) {
    alert(`Redirection vers ${platform} App Store...\n\nL'application sera téléchargée sur votre appareil.`);
    // Dans une vraie app, on redirigerait vers App Store ou Google Play
  }
</script>
</body>
</html>

