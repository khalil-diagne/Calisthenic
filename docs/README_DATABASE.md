# Configuration de la Base de Données - Calisthenics Senegal

## 📋 Étapes d'installation

### 1. Créer la base de données
```sql
CREATE DATABASE IF NOT EXISTS calisthenics_senegal;
USE calisthenics_senegal;
```

### 2. Exécuter le script SQL complet
- Ouvrez phpMyAdmin (http://localhost/phpmyadmin)
- Allez dans l'onglet "SQL"
- Copiez tout le contenu de `sql/create_users_table.sql`
- Exécutez le script

**OU** via la ligne de commande:
```bash
mysql -u root -p calisthenics_senegal < sql/create_users_table.sql
```

### 3. Vérifier les tables créées
Les tables suivantes doivent être présentes:
- ✅ `regions` - Liste des régions du Sénégal
- ✅ `niveaux` - Niveaux d'expérience en calisthenics
- ✅ `utilisateurs` - Données des utilisateurs inscrits

### 4. Vérifier les données initialisées
```sql
SELECT * FROM regions;
SELECT * FROM niveaux;
```

---

## 📁 Fichiers fournis

### `create_users_table.sql`
Script SQL complet contenant:
- Création des tables
- Insertion de données initiales
- Index pour optimiser les requêtes
- Procédures stockées pour les opérations courantes
- Vues pour les rapports

### `api/inscription.php` (anciennement `signup_handler.php`)
Gère les requêtes d'inscription:
- Validation des données
- Vérification des doublons
- Hachage sécurisé du mot de passe
- Insertion en base de données
- Réponse en JSON

### `includes/config.php`
Configuration centralisée (MySQL, sessions, `.env`).

---

## 🔗 Intégration avec le formulaire HTML

### Mettre à jour `signup.html` pour utiliser l'API PHP:

Remplacez la fonction `handleSignup` par:

```javascript
async function handleSignup(event) {
  event.preventDefault();
  
  const nom = document.getElementById('nom').value;
  const email = document.getElementById('email').value;
  const telephone = document.getElementById('telephone').value;
  const region = document.getElementById('region').value;
  const niveau = document.getElementById('niveau').value;
  const password = document.getElementById('password').value;
  
  try {
    const response = await fetch('api/inscription.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        nom: nom,
        email: email,
        telephone: telephone,
        region: region,
        niveau: niveau,
        password: password
      })
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert(`✅ ${result.message}\n\nBienvenue ${nom}! 🎉`);
      document.getElementById('nom').value = '';
      document.getElementById('email').value = '';
      document.getElementById('telephone').value = '';
      document.getElementById('region').value = '';
      document.getElementById('niveau').value = '';
      document.getElementById('password').value = '';
      // Rediriger vers la page de connexion après 2 secondes
      setTimeout(() => {
        window.location.href = 'login.html';
      }, 2000);
    } else {
      alert(`❌ Erreur: ${result.message}`);
    }
  } catch (error) {
    alert('❌ Erreur lors de l\'inscription: ' + error.message);
  }
}
```

---

## 🛡️ Sécurité

### ✅ Mesures implémentées:
- Mots de passe hachés avec BCRYPT
- Requêtes préparées (protection contre SQL injection)
- Validation des données côté serveur
- Vérification des doublons d'email
- CORS protégé par défaut

### 🔐 Recommandations supplémentaires:
- Ajouter HTTPS en production
- Valider les emails via lien de confirmation
- Implémenter un système de rate-limiting
- Ajouter une authentification 2FA optionnelle
- Chiffrer les données sensibles en base

---

## 📊 Requêtes utiles

### Voir tous les utilisateurs inscrits
```sql
SELECT * FROM utilisateurs_complet;
```

### Voir les statistiques par région
```sql
SELECT * FROM utilisateurs_par_region;
```

### Voir les statistiques par niveau
```sql
SELECT * FROM utilisateurs_par_niveau;
```

### Rechercher un utilisateur par email
```sql
CALL GetUtilisateurByEmail('email@example.com');
```

### Réinitialiser un mot de passe
```sql
CALL ChangePassword(1, PASSWORD('nouveau_mot_de_passe'));
```

---

## 🐛 Dépannage

**Erreur: "Erreur de connexion à la base de données"**
- Vérifiez que MySQL est en cours d'exécution
- Vérifiez les identifiants dans `.env` ou `includes/config.php`
- Vérifiez que la base de données existe

**Erreur: "Cet email est déjà utilisé"**
- L'utilisateur est déjà inscrit
- Suggérez au utilisateur de réinitialiser son mot de passe

**Erreur: "Région non valide"**
- Assurez-vous que les données de test contiennent les régions
- Vérifiez les noms exacts des régions dans la base

---

## 📞 Support

Pour toute question ou problème:
1. Consultez les logs d'erreur dans la console PHP
2. Vérifiez que toutes les tables existent
3. Testez les requêtes SQL directement dans phpMyAdmin
