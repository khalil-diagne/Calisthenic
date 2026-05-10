<?php
require_once __DIR__ . '/includes/config.php';

// Vérifier que l'utilisateur est connecté (optionnel, adapter selon vos besoins)
// if (!is_logged_in()) { safe_redirect_target('login.php'); }

// Récupérer toutes les commandes
$db = connecter_db();
$result = $db->query("SELECT * FROM commandes ORDER BY date_creation DESC");
$commandes = [];
while ($row = $result->fetch_assoc()) {
    $commandes[] = $row;
}
$db->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Commandes — RAKH Pulse</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">
<style>
  .admin-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    margin-top: 100px;
  }
  
  .admin-header {
    margin-bottom: 3rem;
  }
  
  .admin-header h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 3rem;
    color: var(--jaune);
    margin-bottom: 0.5rem;
  }
  
  .admin-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
  }
  
  .stat-card {
    background: var(--noir3);
    border: 1px solid var(--vert);
    padding: 1.5rem;
    text-align: center;
  }
  
  .stat-value {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2.5rem;
    color: var(--jaune);
  }
  
  .stat-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: var(--gris-clair);
    margin-top: 0.5rem;
  }
  
  .commandes-table {
    width: 100%;
    border-collapse: collapse;
    background: var(--noir3);
    border: 1px solid rgba(255,255,255,0.05);
  }
  
  .commandes-table thead {
    background: var(--noir2);
    border-bottom: 2px solid var(--jaune);
  }
  
  .commandes-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 700;
    color: var(--jaune);
    font-size: 0.75rem;
    text-transform: uppercase;
  }
  
  .commandes-table td {
    padding: 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.05);
  }
  
  .commandes-table tr:hover {
    background: var(--noir2);
  }
  
  .status-badge {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    border-radius: 3px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
  }
  
  .status-en_attente {
    background: rgba(255,215,0,0.2);
    color: var(--jaune);
  }
  
  .status-confirmee {
    background: rgba(0,168,79,0.2);
    color: var(--vert);
  }
  
  .status-livree {
    background: rgba(0,168,79,0.3);
    color: var(--vert);
  }
  
  .status-annulee {
    background: rgba(204,32,39,0.2);
    color: var(--rouge);
  }
  
  .action-btn {
    background: var(--vert);
    color: var(--noir);
    border: none;
    padding: 0.5rem 1rem;
    cursor: pointer;
    font-size: 0.7rem;
    margin-right: 0.5rem;
  }
  
  .action-btn:hover {
    background: var(--jaune);
  }
</style>
</head>
<body>

<nav>
  <div class="nav-logo">
    <span class="v">CALI</span><span class="j">THEN</span><span class="r">ICS</span>&nbsp;SN
  </div>
  <ul class="nav-links">
    <li><a href="index.php">← Retour</a></li>
  </ul>
</nav>

<div class="admin-container">
  <div class="admin-header">
    <h1>📊 Gestion des Commandes</h1>
    <p style="color: var(--gris-clair);">Tableau de bord des commandes RAKH PULSE</p>
  </div>
  
  <div class="admin-stats">
    <div class="stat-card">
      <div class="stat-value"><?php echo count($commandes); ?></div>
      <div class="stat-label">Total Commandes</div>
    </div>
    <div class="stat-card">
      <div class="stat-value"><?php echo count(array_filter($commandes, fn($c) => $c['statut'] === 'en_attente')); ?></div>
      <div class="stat-label">En Attente</div>
    </div>
    <div class="stat-card">
      <div class="stat-value"><?php echo count(array_filter($commandes, fn($c) => $c['statut'] === 'confirmee')); ?></div>
      <div class="stat-label">Confirmées</div>
    </div>
    <div class="stat-card">
      <div class="stat-value"><?php echo count(array_filter($commandes, fn($c) => $c['statut'] === 'livree')); ?></div>
      <div class="stat-label">Livrées</div>
    </div>
  </div>
  
  <table class="commandes-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Date</th>
        <th>Produit</th>
        <th>Prix</th>
        <th>Client</th>
        <th>Email</th>
        <th>Téléphone</th>
        <th>Adresse</th>
        <th>Statut</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($commandes as $cmd): ?>
      <tr>
        <td>#<?php echo $cmd['id']; ?></td>
        <td><?php echo date('d/m/Y H:i', strtotime($cmd['date_creation'])); ?></td>
        <td><?php echo h($cmd['produit']); ?></td>
        <td><?php echo h($cmd['prix']); ?></td>
        <td><?php echo h($cmd['nom']); ?></td>
        <td><?php echo h($cmd['email']); ?></td>
        <td><?php echo h($cmd['telephone']); ?></td>
        <td><?php echo h($cmd['adresse']); ?></td>
        <td><span class="status-badge status-<?php echo $cmd['statut']; ?>"><?php echo ucfirst(str_replace('_', ' ', $cmd['statut'])); ?></span></td>
        <td>
          <button class="action-btn" onclick="changerStatut(<?php echo $cmd['id']; ?>, 'confirmee')">✓ Confirmer</button>
          <button class="action-btn" onclick="changerStatut(<?php echo $cmd['id']; ?>, 'livree')" style="background:var(--vert);">📦 Livrée</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  
  <?php if (empty($commandes)): ?>
  <div style="text-align: center; padding: 3rem; color: var(--gris-clair);">
    <p>Aucune commande pour le moment.</p>
  </div>
  <?php endif; ?>
</div>

<script>
function changerStatut(id, statut) {
  // À implémenter avec une API pour mettre à jour le statut
  alert('Mise à jour du statut de la commande #' + id + ' à: ' + statut);
  // location.reload();
}
</script>

</body>
</html>
