## 🔧 Diagnostic et Configuration

### 1️⃣ Test rapide de connexion
Ouvre ce lien dans ton navigateur:
```
http://localhost/img/calis/test_connexion.php
```

Cela affichera tous les problèmes de connexion:
- ❌ MySQL pas lancé
- ❌ Base de données n'existe pas
- ❌ Tables n'existent pas
- ❌ Pas de données initialisées

---

### 2️⃣ Configuration des identifiants

**Édite [config.php](config.php)** avec tes vrais paramètres MySQL:

```php
define('DB_HOST', 'localhost');      // Serveur MySQL
define('DB_USER', 'root');           // Utilisateur (vérifie le vrai user)
define('DB_PASS', '');               // Mot de passe (c'est peut-être pas vide)
define('DB_NAME', 'calisthenics_senegal'); // Nom base de données
```

---

### 3️⃣ Créer la base de données

**Via phpMyAdmin** (http://localhost/phpmyadmin):

1. Clique sur "Nouveau" (en bas à gauche)
2. Rentre le nom: `calisthenics_senegal`
3. Clique "Créer"
4. Va dans l'onglet "SQL"
5. Copie-colle le contenu de [create_users_table.sql](create_users_table.sql)
6. Exécute (Clique le bouton "Exécuter")

**OU via Terminal**:
```bash
mysql -u root -p < create_users_table.sql
```

---

### 4️⃣ Vérifier les vrais paramètres MySQL

**Pour trouver tes paramètres réels**, ouvre phpMyAdmin et cherche:

**Utilisateur MySQL:**
- Clique sur "Utilisateurs" → cherche le user actif
- Généralement: `root` sans mot de passe

**Port MySQL:**
- Généralement: `3306`
- Parfois: `3307` si port 3306 occupé

**Tester directement dans phpMyAdmin:**
1. Ouvre phpMyAdmin: http://localhost/phpmyadmin
2. Clique "Base de données"
3. Tu dois voir ou créer `calisthenics_senegal`

---

### 5️⃣ Erreurs courantes

| Erreur | Solution |
|--------|----------|
| "Connection refused" | MySQL pas lancé → Démarrer XAMPP |
| "Access denied for user 'root'" | Mauvais mot de passe → Vérifier en phpMyAdmin |
| "Unknown database" | Base pas créée → Créer avec `create_users_table.sql` |
| "Table doesn't exist" | SQL pas importé → Importer le script |

---

### 6️⃣ Une fois configuré

Teste l'inscription:
1. Va sur: http://localhost/img/calis/signup.php
2. Remplis le formulaire
3. Clique "Créer le compte"
4. Ça doit t'indiquer "✅ Inscription réussie!"

---

### ⚠️ Import du SQL - Étape par étape

**Si tu n'arrives pas à importer le SQL:**

1. Ouvre **phpMyAdmin**
2. Sélectionne la base `calisthenics_senegal`
3. Onglet **"SQL"**
4. Ouvre [create_users_table.sql](create_users_table.sql) dans un éditeur
5. Copie **TOUT** le contenu
6. Colle-le dans phpMyAdmin
7. Clique **"Exécuter"**

---

### 📱 Besoin d'aide?

Dis-moi:
- Quel message d'erreur tu reçois précis?
- Les résultats du fichier `test_connexion.php`?
- Tes vrais paramètres MySQL (user, pass, port)?
