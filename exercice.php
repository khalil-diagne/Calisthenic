<?php
require_once __DIR__ . '/includes/config.php';

// Base de données simulée des exercices
$exercices = [
    'pull-up' => [
        'nom' => 'Pull-Up (Traction)',
        'cat' => 'Tirage',
        'niveau' => 'Débutant',
        'desc' => "Le Pull-Up est le mouvement roi du tirage au poids du corps. Il cible majoritairement le grand dorsal, les rhomboïdes et les biceps. Une maîtrise stricte de ce mouvement est la porte d'entrée vers tous les skills complexes de la calisthenics.",
        'etapes' => [
            "Saisir la barre en pronation (paumes vers l'avant), largeur d'épaules.",
            "Initier le mouvement par une rétraction scapulaire (tirer les épaules vers le bas et l'arrière).",
            "Tirer en amenant les coudes vers le bas et l'arrière jusqu'à passer le menton au-dessus de la barre.",
            "Contrôler la descente jusqu'à l'extension complète (dead hang)."
        ],
        'erreurs' => [
            "Ne pas avoir une amplitude complète (demi-reps).",
            "Utiliser l'élan des jambes (Kipping).",
            "Enrouler les épaules vers l'avant en haut du mouvement."
        ],
        'video' => 'eGo4IYlbE5g',
        'video_label' => 'Calisthenicmovement — traction parfaite',
        'icon' => '🏋️'
    ],
    'dips' => [
        'nom' => 'Dips',
        'cat' => 'Poussée',
        'niveau' => 'Débutant',
        'desc' => "Les Dips aux barres parallèles développent massivement les triceps, la partie inférieure des pectoraux et le deltoïde antérieur. C'est l'exercice de poussée le plus important après les pompes.",
        'etapes' => [
            "Se hisser sur les barres parallèles, bras tendus, épaules abaissées.",
            "Descendre en penchant légèrement le torse vers l'avant jusqu'à casser l'angle de 90° au niveau du coude.",
            "Garder les coudes près du corps (ne pas les écarter).",
            "Pousser de manière explosive pour revenir à la position de départ."
        ],
        'erreurs' => [
            "Écarter les coudes sur les côtés (danger pour les épaules).",
            "Ne pas descendre assez bas.",
            "Hausser les épaules (shrugging) pendant l'exécution."
        ],
        'video' => 'AGCwEXqW__M',
        'video_label' => 'Tom Merrick — DIP TUTORIAL',
        'icon' => '💪'
    ],
    'l-sit' => [
        'nom' => 'L-Sit',
        'cat' => 'Core',
        'niveau' => 'Intermédiaire',
        'desc' => "Le L-Sit est un mouvement isométrique exigeant qui cible profondément la sangle abdominale, les fléchisseurs de hanche et qui demande une grosse dépression scapulaire et force des triceps.",
        'etapes' => [
            "S'asseoir au sol ou entre deux parallettes, mains posées à plat.",
            "Pousser fort dans le sol pour décoller les fesses (dépression scapulaire).",
            "Tendre les jambes droites devant soi de manière à former un 'L' avec son corps.",
            "Gainer au maximum, pointer les pointes de pieds et maintenir la position."
        ],
        'erreurs' => [
            "Plier les genoux (ce qui réduit énormément la difficulté).",
            "Ne pas pousser assez sur les bras (les fesses touchent le sol).",
            "S'arrondir excessivement au niveau de la colonne."
        ],
        'video' => 'QyV_cwE3WIU',
        'video_label' => 'The Calisthenics Project — progressions',
        'icon' => '🔥'
    ],
    'muscle-up' => [
        'nom' => 'Muscle-Up',
        'cat' => 'Skills',
        'niveau' => 'Avancé',
        'desc' => "Le Muscle-Up combine un pull-up explosif et un straight-bar dip en un seul mouvement fluide. C'est l'un des premiers 'Skills' aériens que tout pratiquant souhaite débloquer.",
        'etapes' => [
            "Adopter une 'False grip' ou créer un léger balancier pour générer de l'élan (au début).",
            "Tirer de manière extrêmement explosive vers le sternum ou le nombril.",
            "Effectuer la transition rapide : passer les poignets et le torse par-dessus la barre.",
            "Terminer par un dip complet pour verrouiller la position haute."
        ],
        'erreurs' => [
            "Tirer trop verticalement (il faut tirer légèrement en arrière ou en 'C').",
            "Faire le mouvement de travers (un bras passe avat l'autre, 'chicken wing').",
            "Ne pas avoir la force de base (nécessite de maîtriser 15 pull-ups stricts avant)."
        ],
        'video' => 'fkTiH6ZQkWw',
        'video_label' => 'Caliverse — breakdown',
        'icon' => '⭐'
    ],
    'hspu' => [
        'nom' => 'Handstand Push-Up',
        'cat' => 'Poussée',
        'niveau' => 'Intermédiaire',
        'desc' => "Travailler les épaules au poids du corps atteint son paroxysme avec les Handstand Push-Ups (HSPU). L'équilibre et la force brute des deltoïdes sont indispensables.",
        'etapes' => [
            "Se placer en poirier (Handstand) contre un mur ou en pose libre (Free-standing).",
            "Descendre lentement en pliant les bras, la tête doit aller légèrement vers l'avant.",
            "Former un triangle entre les mains et la tête posée au sol.",
            "Pousser fort pour revenir bras tendus, en alignant bien la posture."
        ],
        'erreurs' => [
            "Écarter les coudes (ils doivent pointer à 45 degrés vers l'arrière).",
            "Creuser énormément le bas du dos (banana back).",
            "Faire atterrir la tête exactement entre les mains sans avancer vers l'avant."
        ],
        'video' => 'n2LuZBT1vr8',
        'video_label' => 'FitnessFAQs — handstand push-up mur',
        'icon' => '🤸'
    ],
    'front-lever' => [
        'nom' => 'Front Lever',
        'cat' => 'Skills',
        'niveau' => 'Élite',
        'desc' => "Le Front Lever est un exercice de tirage statique où l'athlète lévite parallèlement au sol. Il requiert une force phénoménale des grands dorsaux et un gainage parfait.",
        'etapes' => [
            "Saisir la barre et rétracter + dépresser les omoplates de toutes ses forces.",
            "Garder les bras absolument tendus.",
            "Lever tout le corps tendu à l'horizontale.",
            "Maintenir la position (commencer par les variantes Tuck, Adv Tuck, Straddle, Half-lay)."
        ],
        'erreurs' => [
            "Plier légèrement les bras (annule la difficulté).",
            "Casser l'alignement du bassin (fesses qui tombent).",
            "Protracter les épaules en relâchant la tension du dos."
        ],
        'video' => 'Dn83ADziT2s',
        'video_label' => 'Progressions complètes',
        'icon' => '🌟'
    ],
    'dragon-flag' => [
        'nom' => 'Dragon Flag',
        'cat' => 'Core',
        'niveau' => 'Débutant',
        'desc' => "Rendu populaire par Bruce Lee, le Dragon Flag construit une sangle abdominale en acier massif en travaillant à la fois la force et le gainage de l'arrière du corps.",
        'etapes' => [
            "Se coucher sur un banc plat et s'agripper fermement derrière sa tête.",
            "Soulever tout le corps (jambes et bassin) pour qu'il repose uniquement sur le haut du dos et les épaules.",
            "Garder l'alignement et descendre très lentement le corps droit vers le banc.",
            "S'arrêter à quelques millimètres du banc et remonter de manière explosive."
        ],
        'erreurs' => [
            "Plier au niveau des hanches lors de la montée ou de la descente.",
            "Relâcher la tension dans les abdos juste avant d'atteindre le banc.",
            "Cambrer le bas du dos."
        ],
        'video' => 'Q8OjRwJWPt8',
        'video_label' => 'Antranik — forme & progressions',
        'icon' => '💥'
    ],
    'back-lever' => [
        'nom' => 'Back Lever',
        'cat' => 'Tirage',
        'niveau' => 'Avancé',
        'desc' => "Un skill d'anneaux et de barre qui cible la chaîne antérieure, les deltoïdes et la mobilité des épaules. L'athlète lévite face contre sol.",
        'etapes' => [
            "Se tenir à la barre ou aux anneaux et s'inverser totalement (skin the cat).",
            "Laisser le corps descendre doucement derrière soi jusqu'à l'horizontale.",
            "Arrondir légèrement le haut du dos (protraction des épaules) et contracter l'ensemble du corps.",
            "Maintenir bras tendus, corps droit comme une flèche."
        ],
        'erreurs' => [
            "Relâcher completement les épaules (provoque d'énormes douleurs).",
            "Mouvement entrepris avec une mobilité insuffisante au préalable.",
            "Cambrer ou laisser les fesses monter trop haut."
        ],
        'video' => 'kYoJnMY4XM0',
        'video_label' => 'Tutoriel rapide',
        'icon' => '🦅'
    ]
];

$id = $_GET['id'] ?? 'pull-up';
if (!isset($exercices[$id])) {
    $id = 'pull-up';
}
$ex = $exercices[$id];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($ex['nom']); ?> — Détails — RAKH Pulse</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">
<style>
  .detail-container {
    padding: 10rem 4rem 4rem;
    min-height: 100vh;
    background: var(--noir);
  }
  .detail-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--gris-clair);
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 2rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 0.85rem;
    transition: color 0.2s;
  }
  .detail-back:hover { color: var(--jaune); }
  
  .detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: start;
  }
  
  .video-wrapper {
    background: var(--noir3);
    border: 1px solid rgba(255,215,0,0.1);
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.5);
  }
  .video-container {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 */
    height: 0;
    overflow: hidden;
  }
  .video-container iframe {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    border: none;
  }
  
  .detail-content {
    background: var(--noir2);
    padding: 3rem;
    border: 1px solid rgba(255,215,0,0.05);
    clip-path: polygon(20px 0%, 100% 0%, calc(100% - 20px) 100%, 0% 100%);
  }
  .dt-tags {
    display: flex;
    gap: 0.8rem;
    margin-bottom: 1.5rem;
  }
  .dt-tag {
    padding: 0.3rem 0.8rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
  }
  .dt-tag.cat { background: var(--gris); color: var(--blanc); border: 1px solid rgba(255,255,255,0.2); }
  .dt-tag.lvl-debutant { background: var(--vert); color: var(--noir); }
  .dt-tag.lvl-intermediaire { background: var(--jaune); color: var(--noir); }
  .dt-tag.lvl-avance { background: var(--rouge); color: var(--blanc); }
  .dt-tag.lvl-elite { background: var(--noir); color: var(--blanc); border: 1px solid var(--jaune); }
  
  .dt-title {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 3.5rem;
    color: var(--blanc);
    letter-spacing: 2px;
    margin-bottom: 1rem;
    line-height: 1.1;
  }
  .dt-desc {
    color: var(--blanc2);
    font-size: 1rem;
    line-height: 1.7;
    margin-bottom: 2.5rem;
  }
  
  .dt-section {
    margin-bottom: 2.5rem;
  }
  .dt-section h4 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.8rem;
    color: var(--jaune);
    letter-spacing: 1px;
    margin-bottom: 1rem;
    border-bottom: 1px solid rgba(255,215,0,0.2);
    padding-bottom: 0.5rem;
  }
  .dt-section ul, .dt-section ol {
    padding-left: 1.5rem;
    color: var(--gris-clair);
    font-size: 0.95rem;
    line-height: 1.6;
  }
  .dt-section li { margin-bottom: 0.6rem; }
  
  .err-list li { color: #ff6b6b; }
  
  @media(max-width: 1024px) {
    .detail-grid { grid-template-columns: 1fr; gap: 3rem; }
    .detail-container { padding: 8rem 2rem 2rem; }
  }
</style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-trail" id="cursorTrail"></div>

<!-- NAV -->
<nav>
  <div class="nav-logo">
    <span class="v">CALI</span><span class="j">THEN</span><span class="r">ICS</span>&nbsp;SN
  </div>
  <ul class="nav-links">
    <li><a href="index.php#exercices" style="color:var(--jaune);">Exercices</a></li>
    <li><a href="index.php#programmes">Programmes</a></li>
    <li><a href="index.php#boutique">Boutique</a></li>
    <li><a href="index.php#communaute">Communauté</a></li>
    <?php if (is_logged_in()): ?>
      <li><a href="profile.php" class="nav-cta">Dashboard</a></li>
    <?php else: ?>
      <li><a href="login.php" class="nav-cta" style="background:transparent; border:1px solid var(--jaune); color:var(--jaune)!important;">Connexion</a></li>
    <?php endif; ?>
  </ul>
</nav>

<div class="detail-container">
  <div style="max-width: 1400px; margin: 0 auto;">
    <a href="index.php#exercices" class="detail-back">← Retour à la bibliothèque</a>
    
    <div class="detail-grid">
      
      <!-- COL 1 : TEXT -->
      <div class="detail-content reveal visible">
        <div class="dt-tags">
          <span class="dt-tag cat"><?php echo htmlspecialchars($ex['cat']); ?></span>
          <span class="dt-tag lvl-<?php echo strtolower(str_replace('é', 'e', $ex['niveau'])); ?>">
            <?php echo htmlspecialchars($ex['niveau']); ?>
          </span>
        </div>
        
        <h1 class="dt-title"><?php echo $ex['icon']; ?> <?php echo htmlspecialchars($ex['nom']); ?></h1>
        <p class="dt-desc">
          <?php echo htmlspecialchars($ex['desc']); ?>
        </p>
        
        <div class="dt-section">
          <h4>📌 Exécution correcte</h4>
          <ol>
            <?php foreach ($ex['etapes'] as $etape): ?>
              <li><?php echo htmlspecialchars($etape); ?></li>
            <?php endforeach; ?>
          </ol>
        </div>
        
        <div class="dt-section">
          <h4 style="color:#ff6b6b; border-bottom-color:rgba(255,107,107,0.2);">⚠️ Erreurs communes</h4>
          <ul class="err-list">
            <?php foreach ($ex['erreurs'] as $erreur): ?>
              <li><?php echo htmlspecialchars($erreur); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
      
      <!-- COL 2 : VIDEO -->
      <div class="video-container-wrapper reveal visible">
        <h3 style="font-family:'Bebas Neue',sans-serif; color:var(--blanc); font-size:2rem; margin-bottom:1rem; letter-spacing:1px;">▶ Tutoriel Vidéo</h3>
        <div class="video-wrapper">
          <div class="video-container">
            <iframe 
               src="https://www.youtube.com/embed/<?php echo htmlspecialchars($ex['video']); ?>?rel=0" 
               title="Tutoriel Video pour <?php echo htmlspecialchars($ex['nom']); ?>" 
               loading="lazy"
               allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
               allowfullscreen>
            </iframe>
          </div>
          <div style="margin-top:0.8rem; display:flex; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
            <a href="https://www.youtube.com/watch?v=<?php echo htmlspecialchars($ex['video']); ?>" target="_blank" rel="noopener noreferrer" style="color:var(--jaune); font-weight:700;">Ouvrir sur YouTube</a>
            <span style="color:var(--gris-clair); font-size:0.92rem;">Source : <?php echo htmlspecialchars($ex['video_label'] ?? 'YouTube'); ?></span>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>

