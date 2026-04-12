<?php
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Calisthenics Senegal — RAKH Pulse</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">
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
    <li><a href="#exercices">Exercices</a></li>
    <li><a href="#programmes">Programmes</a></li>
    <li><a href="#boutique">Boutique</a></li>
    <li><a href="#communaute">Communauté</a></li>
    <li><a href="spots.php" class="nav-cta">Spots</a></li>
    <?php if (is_logged_in()): ?>
      <li><a href="coach_ia.php" class="nav-cta" style="background:var(--vert);color:var(--noir);border-color:var(--vert); margin-right:5px;">✨ Coach IA</a></li>
      <li><a href="profile.php" class="nav-cta">Dashboard</a></li>
      <li><a href="logout.php" style="color:#ff6b6b; font-weight:bold; padding-top:10px;">Déconnexion</a></li>
    <?php else: ?>
      <li><a href="login.php" class="nav-cta" style="background:transparent; border:1px solid var(--jaune); color:var(--jaune)!important;">Connexion</a></li>
      <li><a href="signup.php" class="nav-cta">Rejoindre</a></li>
    <?php endif; ?>
  </ul>
</nav>

<!-- HERO -->
<section class="hero" id="accueil">
  <div class="hero-bg"></div>
  <div class="hero-grid-lines"></div>

  <div class="hero-content">
    <div class="hero-tag">🇸🇳 Dakar · Thiès · Saint-Louis</div>
    <h1 class="hero-title">
      <span class="line1">FORGE</span>
      <span class="line2">TON</span>
      <span class="line3">CORPS</span>
    </h1>
    <p class="hero-desc">
      La force de la rue. La discipline du RAKH. Rejoins la communauté Calisthenics la plus active du Sénégal et transforme chaque barre de fer en victoire.
    </p>
    <div class="hero-btns">
      <a href="#programmes" class="btn-primary">Commencer Maintenant</a>
      <a href="#exercices" class="btn-secondary">Voir les exercices</a>
    </div>
    <div class="hero-stats">
      <div class="stat-item">
        <div class="stat-num">2.4K</div>
        <div class="stat-label">Athlètes</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">14</div>
        <div class="stat-label">Régions</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">380+</div>
        <div class="stat-label">Exercices</div>
      </div>
    </div>
  </div>

  <div class="hero-visual">
    <div class="hero-circle"></div>
    <div class="hero-circle-2"></div>
    <div class="hero-center-card">
      <div class="workout-of-day">⚡ Entraînement du Jour</div>
      <div class="workout-name">STREET POWER</div>
      <div class="workout-exercises">
        <div class="exercise-row">
          <span class="exercise-name">Pull-ups</span>
          <span class="exercise-sets">5 × 8</span>
        </div>
        <div class="exercise-row">
          <span class="exercise-name">Dips</span>
          <span class="exercise-sets">4 × 12</span>
        </div>
        <div class="exercise-row">
          <span class="exercise-name">L-Sit</span>
          <span class="exercise-sets">3 × 20s</span>
        </div>
        <div class="exercise-row">
          <span class="exercise-name">Muscle-Up</span>
          <span class="exercise-sets">3 × 5</span>
        </div>
      </div>
    </div>
    <div class="hero-floating-badge">🔥 Streak 7J</div>
    <div class="hero-floating-level">
      <div class="level-label">Ton niveau</div>
      <div class="level-val">Intermédiaire</div>
    </div>
  </div>
</section>

<!-- STRIP -->
<div class="strip">
  <div class="strip-inner">
    <span class="strip-item">CALISTHENICS</span>
    <span class="strip-dot">●</span>
    <span class="strip-item">STREET WORKOUT</span>
    <span class="strip-dot">●</span>
    <span class="strip-item">RAKH PULSE</span>
    <span class="strip-dot">●</span>
    <span class="strip-item">DAKAR FORCE</span>
    <span class="strip-dot">●</span>
    <span class="strip-item">DISCIPLINE</span>
    <span class="strip-dot">●</span>
    <span class="strip-item">PERSÉVÉRANCE</span>
    <span class="strip-dot">●</span>
    <span class="strip-item">CALISTHENICS</span>
    <span class="strip-dot">●</span>
    <span class="strip-item">STREET WORKOUT</span>
    <span class="strip-dot">●</span>
    <span class="strip-item">RAKH PULSE</span>
    <span class="strip-dot">●</span>
    <span class="strip-item">DAKAR FORCE</span>
    <span class="strip-dot">●</span>
    <span class="strip-item">DISCIPLINE</span>
    <span class="strip-dot">●</span>
    <span class="strip-item">PERSÉVÉRANCE</span>
    <span class="strip-dot">●</span>
  </div>
</div>

<!-- FEATURES -->
<section class="features" id="features">
  <div class="section-header reveal">
    <div class="section-tag">Pourquoi nous ?</div>
    <h2 class="section-title">TOUT CE DONT TU AS <em>BESOIN</em></h2>
  </div>
  <div class="features-grid">
    <div class="feature-card reveal">
      <span class="feature-num">01</span>
      <span class="feature-icon">🏋️</span>
      <h3 class="feature-title">Bibliothèque Complète</h3>
      <p class="feature-desc">Plus de 380 exercices classés par groupe musculaire, niveau et type de mouvement. Des bases aux skills avancés.</p>
    </div>
    <div class="feature-card reveal">
      <span class="feature-num">02</span>
      <span class="feature-icon">📊</span>
      <h3 class="feature-title">Suivi Intelligent</h3>
      <p class="feature-desc">Analyse tes performances en temps réel. Graphiques de progression, PR, streaks et objectifs hebdomadaires personnalisés.</p>
    </div>
    <div class="feature-card reveal">
      <span class="feature-num">03</span>
      <span class="feature-icon">🌍</span>
      <h3 class="feature-title">Adapté au Contexte</h3>
      <p class="feature-desc">Des programmes pensés pour nos conditions : chaleur, équipement disponible en plein air, timing adapté au Ramadan.</p>
    </div>
    <div class="feature-card reveal">
      <span class="feature-num">04</span>
      <span class="feature-icon">👥</span>
      <h3 class="feature-title">Communauté Active</h3>
      <p class="feature-desc">2 400+ athlètes sénégalais. Challenges hebdomadaires, classements régionaux, et events dans ta ville.</p>
    </div>
    <div class="feature-card reveal">
      <span class="feature-num">05</span>
      <span class="feature-icon">🎯</span>
      <h3 class="feature-title">Programmes Guidés</h3>
      <p class="feature-desc">Street Power, Endurance Tropicale, Skill Builder… Des plans structurés sur 4 à 12 semaines avec progression garantie.</p>
    </div>
    <div class="feature-card reveal">
      <span class="feature-num">06</span>
      <span class="feature-icon">🏆</span>
      <h3 class="feature-title">Badges & Récompenses</h3>
      <p class="feature-desc">Débloque des badges en accomplissant des défis. Montre ta progression et motive la communauté autour de toi.</p>
    </div>
  </div>
</section>

<!-- EXERCISES -->
<section class="exercises" id="exercices">
  <div class="section-header reveal">
    <div class="section-tag">Bibliothèque</div>
    <h2 class="section-title">LES MOUVEMENTS <em>FONDATEURS</em></h2>
  </div>
  <div class="exercise-filter reveal">
    <button class="filter-btn active" onclick="filterEx(this,'all')">Tous</button>
    <button class="filter-btn" onclick="filterEx(this,'poussee')">💪 Poussée</button>
    <button class="filter-btn" onclick="filterEx(this,'tirage')">🔄 Tirage</button>
    <button class="filter-btn" onclick="filterEx(this,'core')">🔥 Core</button>
    <button class="filter-btn" onclick="filterEx(this,'skills')">⭐ Skills</button>
  </div>
  <div class="exercises-grid" id="exGrid">

    <div class="exercise-card" data-cat="tirage">
      <div class="exercise-card-img-wrap">
        <div class="exercise-bg-pattern"></div>
        <div class="exercise-card-img">🏋️</div>
        <span class="exercise-level level-debutant">Débutant</span>
        <div class="exercise-overlay">
          <h4>Étapes clés</h4>
          <ol>
            <li>Rétraction scapulaire</li>
            <li>Tirer coudes vers le bas</li>
            <li>Menton au-dessus de barre</li>
          </ol>
          <a href="exercice.php?id=pull-up" class="btn-tuto">▶ Tuto Vidéo</a>
        </div>
      </div>
      <div class="exercise-card-body">
        <div class="exercise-card-cat">Tirage</div>
        <div class="exercise-card-name">Pull-Up</div>
        <div class="exercise-card-meta">
          <span>Dos · Biceps</span>
          <span>|</span>
          <span>3–5 séries</span>
        </div>
      </div>
    </div>

    <div class="exercise-card" data-cat="poussee">
      <div class="exercise-card-img-wrap">
        <div class="exercise-bg-pattern"></div>
        <div class="exercise-card-img">💪</div>
        <span class="exercise-level level-debutant">Débutant</span>
        <div class="exercise-overlay">
          <h4>Étapes clés</h4>
          <ol>
            <li>Descendre jusqu'à 90°</li>
            <li>Garder les coudes serrés</li>
            <li>Pousser fort à la montée</li>
          </ol>
          <a href="exercice.php?id=dips" class="btn-tuto">▶ Tuto Vidéo</a>
        </div>
      </div>
      <div class="exercise-card-body">
        <div class="exercise-card-cat">Poussée</div>
        <div class="exercise-card-name">Dips</div>
        <div class="exercise-card-meta">
          <span>Triceps · Épaules</span>
          <span>|</span>
          <span>4 séries</span>
        </div>
      </div>
    </div>

    <div class="exercise-card" data-cat="core">
      <div class="exercise-card-img-wrap">
        <div class="exercise-bg-pattern"></div>
        <div class="exercise-card-img">🔥</div>
        <span class="exercise-level level-intermediaire">Intermédiaire</span>
        <div class="exercise-overlay">
          <h4>Étapes clés</h4>
          <ol>
            <li>Mains à plat ou sur barres</li>
            <li>Épaules vers le bas</li>
            <li>Jambes tendues à 90°</li>
          </ol>
          <a href="exercice.php?id=l-sit" class="btn-tuto">▶ Tuto Vidéo</a>
        </div>
      </div>
      <div class="exercise-card-body">
        <div class="exercise-card-cat">Core</div>
        <div class="exercise-card-name">L-Sit</div>
        <div class="exercise-card-meta">
          <span>Abdos · Hip Flexors</span>
          <span>|</span>
          <span>Isométrique</span>
        </div>
      </div>
    </div>

    <div class="exercise-card" data-cat="skills">
      <div class="exercise-card-img-wrap">
        <div class="exercise-bg-pattern"></div>
        <div class="exercise-card-img">⭐</div>
        <span class="exercise-level level-avance">Avancé</span>
        <div class="exercise-overlay">
          <h4>Étapes clés</h4>
          <ol>
            <li>Prise Faux Grip</li>
            <li>Traction explosive</li>
            <li>Transition rapide au dip</li>
          </ol>
          <a href="exercice.php?id=muscle-up" class="btn-tuto">▶ Tuto Vidéo</a>
        </div>
      </div>
      <div class="exercise-card-body">
        <div class="exercise-card-cat">Skills</div>
        <div class="exercise-card-name">Muscle-Up</div>
        <div class="exercise-card-meta">
          <span>Full upper body</span>
          <span>|</span>
          <span>Explosif</span>
        </div>
      </div>
    </div>

    <div class="exercise-card" data-cat="poussee">
      <div class="exercise-card-img-wrap">
        <div class="exercise-bg-pattern"></div>
        <div class="exercise-card-img">🤸</div>
        <span class="exercise-level level-intermediaire">Intermédiaire</span>
        <div class="exercise-overlay">
          <h4>Étapes clés</h4>
          <ol>
            <li>Trouver son équilibre</li>
            <li>Descendre tête en avant</li>
            <li>Former un triangle avec mains</li>
          </ol>
          <a href="exercice.php?id=hspu" class="btn-tuto">▶ Tuto Vidéo</a>
        </div>
      </div>
      <div class="exercise-card-body">
        <div class="exercise-card-cat">Poussée</div>
        <div class="exercise-card-name">Handstand Push-Up</div>
        <div class="exercise-card-meta">
          <span>Épaules · Triceps</span>
          <span>|</span>
          <span>Équilibre</span>
        </div>
      </div>
    </div>

    <div class="exercise-card" data-cat="skills">
      <div class="exercise-card-img-wrap">
        <div class="exercise-bg-pattern"></div>
        <div class="exercise-card-img">🌟</div>
        <span class="exercise-level level-elite">Élite</span>
        <div class="exercise-overlay">
          <h4>Étapes clés</h4>
          <ol>
            <li>Rétraction scapulaire</li>
            <li>Bras totalement tendus</li>
            <li>Corps parallèle au sol</li>
          </ol>
          <a href="exercice.php?id=front-lever" class="btn-tuto">▶ Tuto Vidéo</a>
        </div>
      </div>
      <div class="exercise-card-body">
        <div class="exercise-card-cat">Skills</div>
        <div class="exercise-card-name">Front Lever</div>
        <div class="exercise-card-meta">
          <span>Dos · Core · Tout</span>
          <span>|</span>
          <span>Isométrique</span>
        </div>
      </div>
    </div>

    <div class="exercise-card" data-cat="core">
      <div class="exercise-card-img-wrap">
        <div class="exercise-bg-pattern"></div>
        <div class="exercise-card-img">💥</div>
        <span class="exercise-level level-debutant">Débutant</span>
        <div class="exercise-overlay">
          <h4>Étapes clés</h4>
          <ol>
            <li>Accroche ferme au banc</li>
            <li>Corps gainé et aligné</li>
            <li>Descente très lente</li>
          </ol>
          <a href="exercice.php?id=dragon-flag" class="btn-tuto">▶ Tuto Vidéo</a>
        </div>
      </div>
      <div class="exercise-card-body">
        <div class="exercise-card-cat">Core</div>
        <div class="exercise-card-name">Dragon Flag</div>
        <div class="exercise-card-meta">
          <span>Abdos complets</span>
          <span>|</span>
          <span>Dynamique</span>
        </div>
      </div>
    </div>

    <div class="exercise-card" data-cat="tirage">
      <div class="exercise-card-img-wrap">
        <div class="exercise-bg-pattern"></div>
        <div class="exercise-card-img">🦅</div>
        <span class="exercise-level level-avance">Avancé</span>
        <div class="exercise-overlay">
          <h4>Étapes clés</h4>
          <ol>
            <li>Grip en supination/pronation</li>
            <li>Haut du dos arrondi</li>
            <li>Tension musculaire complète</li>
          </ol>
          <a href="exercice.php?id=back-lever" class="btn-tuto">▶ Tuto Vidéo</a>
        </div>
      </div>
      <div class="exercise-card-body">
        <div class="exercise-card-cat">Tirage</div>
        <div class="exercise-card-name">Back Lever</div>
        <div class="exercise-card-meta">
          <span>Dos · Épaules</span>
          <span>|</span>
          <span>Isométrique</span>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- PROGRAMS -->
<section class="programs" id="programmes">
  <div class="section-header reveal">
    <div class="section-tag">Plans d'entraînement</div>
    <h2 class="section-title">PROGRAMMES <em>FORGES</em> POUR TOI</h2>
  </div>
  <div class="programs-grid">

    <div class="program-card reveal">
      <div class="program-card-inner">
        <div class="program-header">
          <span class="program-icon">⚡</span>
          <div class="program-name">Street Power</div>
          <div class="program-tagline">Force brute depuis la rue</div>
        </div>
        <div class="program-body">
          <div class="program-stats">
            <div class="prog-stat">
              <div class="prog-stat-val">8 sem</div>
              <div class="prog-stat-label">Durée</div>
            </div>
            <div class="prog-stat">
              <div class="prog-stat-val">4×/sem</div>
              <div class="prog-stat-label">Fréquence</div>
            </div>
            <div class="prog-stat">
              <div class="prog-stat-val">45min</div>
              <div class="prog-stat-label">Par session</div>
            </div>
          </div>
          <div class="program-tags">
            <span class="prog-tag">Force</span>
            <span class="prog-tag">Muscle</span>
            <span class="prog-tag">Intermédiaire</span>
          </div>
          <div class="program-progress">
            <div class="prog-progress-label">
              <span>Progression type</span>
              <span>78%</span>
            </div>
            <div class="prog-progress-bar">
              <div class="prog-progress-fill" style="width:78%"></div>
            </div>
          </div>
          <a href="programme_street_power.php" class="btn-program">Démarrer le programme</a>
        </div>
      </div>
    </div>

    <div class="program-card reveal">
      <div class="program-card-inner">
        <div class="program-header">
          <span class="program-icon">🌊</span>
          <div class="program-name">Endurance Tropicale</div>
          <div class="program-tagline">Endurant comme le baobab</div>
        </div>
        <div class="program-body">
          <div class="program-stats">
            <div class="prog-stat">
              <div class="prog-stat-val">6 sem</div>
              <div class="prog-stat-label">Durée</div>
            </div>
            <div class="prog-stat">
              <div class="prog-stat-val">5×/sem</div>
              <div class="prog-stat-label">Fréquence</div>
            </div>
            <div class="prog-stat">
              <div class="prog-stat-val">30min</div>
              <div class="prog-stat-label">Par session</div>
            </div>
          </div>
          <div class="program-tags">
            <span class="prog-tag">Cardio</span>
            <span class="prog-tag">Endurance</span>
            <span class="prog-tag">Débutant</span>
          </div>
          <div class="program-progress">
            <div class="prog-progress-label">
              <span>Progression type</span>
              <span>65%</span>
            </div>
            <div class="prog-progress-bar">
              <div class="prog-progress-fill" style="width:65%"></div>
            </div>
          </div>
          <a href="programme_endurance.php" class="btn-program">Démarrer le programme</a>
        </div>
      </div>
    </div>

    <div class="program-card reveal">
      <div class="program-card-inner">
        <div class="program-header">
          <span class="program-icon">🔥</span>
          <div class="program-name">Skill Builder</div>
          <div class="program-tagline">Maîtrise les skills d'élite</div>
        </div>
        <div class="program-body">
          <div class="program-stats">
            <div class="prog-stat">
              <div class="prog-stat-val">12 sem</div>
              <div class="prog-stat-label">Durée</div>
            </div>
            <div class="prog-stat">
              <div class="prog-stat-val">3×/sem</div>
              <div class="prog-stat-label">Fréquence</div>
            </div>
            <div class="prog-stat">
              <div class="prog-stat-val">60min</div>
              <div class="prog-stat-label">Par session</div>
            </div>
          </div>
          <div class="program-tags">
            <span class="prog-tag">Skills</span>
            <span class="prog-tag">Technique</span>
            <span class="prog-tag">Avancé</span>
          </div>
          <div class="program-progress">
            <div class="prog-progress-label">
              <span>Progression type</span>
              <span>92%</span>
            </div>
            <div class="prog-progress-bar">
              <div class="prog-progress-fill" style="width:92%"></div>
            </div>
          </div>
          <a href="programme_skill_builder.php" class="btn-program">Démarrer le programme</a>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- BOUTIQUE -->
<section class="boutique" id="boutique">
  <div class="section-header reveal">
    <div class="section-tag">Équipements & Nutrition</div>
    <h2 class="section-title">BOUTIQUE <em>RAKH PULSE</em></h2>
  </div>
  <div class="boutique-grid">
    <div class="product-card reveal">
      <div class="product-tag">Nouveau</div>
      <div class="product-icon">🧤</div>
      <h3 class="product-name">Gants Pro Grip</h3>
      <p class="product-desc">Protège tes mains lors des muscle-ups et tractions intensives. Adhérence maximale.</p>
      <div class="product-price">12 000 <span>FCFA</span></div>
      <a href="#" class="btn-buy">Commander</a>
    </div>
    <div class="product-card reveal">
      <div class="product-tag">Populaire</div>
      <div class="product-icon">〰️</div>
      <h3 class="product-name">Bandes Élastiques</h3>
      <p class="product-desc">Pack de 3 résistances (Light, Medium, Heavy). Idéal pour débloquer le Front Lever.</p>
      <div class="product-price">15 000 <span>FCFA</span></div>
      <a href="#" class="btn-buy">Commander</a>
    </div>
    <div class="product-card reveal">
      <div class="product-tag">Premium</div>
      <div class="product-icon">👕</div>
      <h3 class="product-name">T-Shirt Compression</h3>
      <p class="product-desc">T-shirt respirant à effet seconde peau pour un maintien musculaire parfait lors de tes workouts.</p>
      <div class="product-price">18 000 <span>FCFA</span></div>
      <a href="#" class="btn-buy">Commander</a>
    </div>
  </div>
</section>

<!-- COMMUNITY -->
<section class="community" id="communaute">
  <div class="community-layout">
    <div class="community-text reveal">
      <div class="section-header">
        <div class="section-tag">Communauté</div>
        <h2 class="section-title">ENSEMBLE ON VA <em>PLUS LOIN</em></h2>
      </div>
      <ul class="community-feature-list">
        <li class="comm-feature">
          <div class="comm-feature-icon">🏆</div>
          Challenges hebdomadaires avec classements régionaux
        </li>
        <li class="comm-feature">
          <div class="comm-feature-icon">📍</div>
          Trouve des spots de workout près de chez toi
        </li>
        <li class="comm-feature">
          <div class="comm-feature-icon">🤝</div>
          Partage tes progrès, inspire ta communauté
        </li>
        <li class="comm-feature">
          <div class="comm-feature-icon">🎖️</div>
          Events et compétitions au Sénégal
        </li>
      </ul>
      <?php if (!is_logged_in()): ?>
        <a href="signup.php" class="btn-primary">Rejoindre la communauté</a>
      <?php else: ?>
        <a href="profile.php" class="btn-primary">Mon Profil</a>
      <?php endif; ?>
    </div>

    <div class="leaderboard reveal">
      <div class="lb-header">
        <div class="lb-title">🏆 Top Athlètes</div>
        <div class="lb-period">Cette semaine</div>
      </div>
      <div class="lb-row">
        <div class="lb-rank gold">🥇</div>
        <div class="lb-user">
          <div class="lb-avatar">🦁</div>
          <div>
            <div class="lb-name">Ibrahima Diallo</div>
            <div class="lb-city">Dakar</div>
          </div>
        </div>
        <div class="lb-points">4,820 pts</div>
        <div class="lb-badge">🔥</div>
      </div>
      <div class="lb-row">
        <div class="lb-rank silver">🥈</div>
        <div class="lb-user">
          <div class="lb-avatar">⚡</div>
          <div>
            <div class="lb-name">Fatou Ndiaye</div>
            <div class="lb-city">Thiès</div>
          </div>
        </div>
        <div class="lb-points">4,310 pts</div>
        <div class="lb-badge">⭐</div>
      </div>
      <div class="lb-row">
        <div class="lb-rank bronze">🥉</div>
        <div class="lb-user">
          <div class="lb-avatar">🐆</div>
          <div>
            <div class="lb-name">Moussa Sarr</div>
            <div class="lb-city">Saint-Louis</div>
          </div>
        </div>
        <div class="lb-points">3,980 pts</div>
        <div class="lb-badge">💪</div>
      </div>
      <div class="lb-row">
        <div class="lb-rank" style="color:var(--gris-clair)">4</div>
        <div class="lb-user">
          <div class="lb-avatar">🦅</div>
          <div>
            <div class="lb-name">Aminata Cissé</div>
            <div class="lb-city">Ziguinchor</div>
          </div>
        </div>
        <div class="lb-points">3,650 pts</div>
        <div class="lb-badge">🏅</div>
      </div>
      <div class="lb-row">
        <div class="lb-rank" style="color:var(--gris-clair)">5</div>
        <div class="lb-user">
          <div class="lb-avatar">🌟</div>
          <div>
            <div class="lb-name">Cheikh Ba</div>
            <div class="lb-city">Kaolack</div>
          </div>
        </div>
        <div class="lb-points">3,210 pts</div>
        <div class="lb-badge">✨</div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section" id="rejoindre">
  <div class="cta-flags">
    <div class="flag-stripe v"></div>
    <div class="flag-stripe j"></div>
    <div class="flag-stripe r"></div>
  </div>
  <h2 class="section-title reveal">PRÊT À <em>DOMINER</em> ?</h2>
  <p class="reveal">Rejoins des milliers d'athlètes sénégalais qui ont choisi la discipline, la sueur et la progression. Commence dès aujourd'hui.</p>
  <div style="display:flex;gap:1rem;justify-content:center;" class="reveal">
    <?php if (!is_logged_in()): ?>
      <a href="signup.php" class="btn-primary">Créer mon compte gratuit</a>
    <?php endif; ?>
    <a href="download.php" class="btn-secondary">Télécharger l'app</a>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-brand">
    <div class="nav-logo">
      <span class="v">CALI</span><span class="j">THEN</span><span class="r">ICS</span>&nbsp;SN
    </div>
    <p>La première plateforme de calisthenics dédiée aux athlètes sénégalais. Forge ton corps, élève ton esprit.</p>
  </div>
  <div>
    <div class="footer-col-title">Navigation</div>
    <ul class="footer-links">
      <li><a href="#accueil">Accueil</a></li>
      <li><a href="#exercices">Exercices</a></li>
      <li><a href="#programmes">Programmes</a></li>
      <li><a href="#boutique">Boutique</a></li>
      <li><a href="#communaute">Communauté</a></li>
    </ul>
  </div>
  <div>
    <div class="footer-col-title">Ressources</div>
    <ul class="footer-links">
      <li><a href="#">Guide débutant</a></li>
      <li><a href="#">Plans nutritionnels</a></li>
      <li><a href="#">Spots Dakar</a></li>
      <li><a href="#">Blog</a></li>
    </ul>
  </div>
  <div>
    <div class="footer-col-title">Contact</div>
    <ul class="footer-links">
      <li><a href="#">Instagram</a></li>
      <li><a href="#">WhatsApp Group</a></li>
      <li><a href="#">contact@calisthsa.sn</a></li>
      <li><a href="#">À propos</a></li>
    </ul>
  </div>
</footer>
<div class="footer-bottom">
  <span>© 2026 Calisthenics Senegal. Fait avec 💪 à Dakar.</span>
  <div class="footer-colors">
    <div class="footer-dot" style="background:var(--vert)"></div>
    <div class="footer-dot" style="background:var(--jaune)"></div>
    <div class="footer-dot" style="background:var(--rouge)"></div>
    <span style="margin-left:0.5rem">RAKH Pulse Design System</span>
  </div>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>
