# 🔧 DIAGNOSTIC ET CORRECTION DES PROBLÈMES

## 🎯 **PROBLÈMES IDENTIFIÉS**

### ❌ Problème 1: Erreur 403 Forbidden
**Symptôme**: `upload-cv.php` et `recruitment-request.php` retournent 403
**Cause**: Le fichier `.htaccess` bloquait l'accès à ces fichiers
**Solution**: ✅ CORRIGÉ - Ajouté les fichiers à la liste autorisée

### ❌ Problème 2: Erreur SMTP
**Symptôme**: "Could not connect to SMTP host"
**Cause**: Configuration SMTP invalide en développement local
**Solution**: ✅ CORRIGÉ - Les scripts continuent même si l'email échoue

### ❌ Problème 3: Aucune donnée en base
**Cause**: Combinaison des 2 problèmes ci-dessus
**Solution**: ✅ CORRIGÉ - Maintenant les données sont sauvegardées même sans email

---

## ✅ **CORRECTIONS APPLIQUÉES**

### 1. Fichier `.htaccess` mis à jour
**Avant:**
```apache
<FilesMatch "^sendemail\.php$">
    Order allow,deny
    Allow from all
</FilesMatch>
```

**Après:**
```apache
<FilesMatch "^(sendemail|upload-cv|recruitment-request)\.php$">
    Order allow,deny
    Allow from all
</FilesMatch>
```

### 2. Gestion des erreurs email
- `upload-cv.php`: Emails entourés de try/catch
- `recruitment-request.php`: Emails entourés de try/catch
- Les données sont **toujours sauvegardées** même si l'email échoue

---

## 🧪 **ÉTAPES DE TEST**

### Étape 1: Tester la connexion base de données
```
http://localhost/MHTECH/test-db-connection.php
```

Vous devez voir:
- ✅ Connexion à la base de données réussie
- ✅ Toutes les tables existent
- ✅ Test d'insertion fonctionnel

### Étape 2: Importer la base de données (si pas encore fait)

1. Ouvrez **phpMyAdmin**: http://localhost/phpmyadmin
2. Allez dans l'onglet **SQL**
3. Ouvrez le fichier `assets/inc/database.sql`
4. Copiez TOUT le contenu
5. Collez dans phpMyAdmin
6. Cliquez **Exécuter**

### Étape 3: Tester les formulaires

#### Test 1: Contact Principal
```
http://localhost/MHTECH/contact.html
```
- Remplissez le formulaire
- Cliquez "Envoyer"
- Vérifiez dans phpMyAdmin: table `contacts`

#### Test 2: Dépôt CV
```
http://localhost/MHTECH/staffing.html
```
- Cliquez "Déposez votre CV"
- Remplissez le formulaire
- Joignez un fichier PDF
- Cliquez "Envoyer mon CV"
- Vérifiez dans phpMyAdmin: table `cv_submissions`

#### Test 3: Demande de Recrutement
```
http://localhost/MHTECH/staffing.html
```
- Cliquez "Demande de recrutement"
- Remplissez le formulaire entreprise
- Cliquez "Envoyer la demande"
- Vérifiez dans phpMyAdmin: table `recruitment_requests`

---

## 🔍 **VÉRIFIER LES DONNÉES EN BASE**

### Via phpMyAdmin

**1. Voir tous les contacts:**
```sql
SELECT * FROM contacts ORDER BY created_at DESC;
```

**2. Voir les CVs reçus:**
```sql
SELECT name, email, position, cv_original_name, created_at
FROM cv_submissions
ORDER BY created_at DESC;
```

**3. Voir les demandes de recrutement:**
```sql
SELECT company, contact_name, profile, duration, created_at
FROM recruitment_requests
ORDER BY created_at DESC;
```

**4. Voir les abonnés newsletter:**
```sql
SELECT email, status, source, subscribed_at
FROM newsletter_subscriptions
ORDER BY subscribed_at DESC;
```

**5. Voir les logs d'activité:**
```sql
SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 20;
```

---

## ⚠️ **SI VOUS AVEZ ENCORE DES PROBLÈMES**

### Problème: "Database connection error"

**Vérifications:**
1. MySQL est démarré dans WAMP (icône verte)
2. La base `mhtech_consulting` existe
3. Les identifiants dans `.env` sont corrects:
   ```
   DB_HOST = localhost
   DB_NAME = mhtech_consulting
   DB_USER = root
   DB_PASSWORD =
   ```

**Solution:**
```bash
# Ouvrir phpMyAdmin
# Créer la base manuellement:
CREATE DATABASE mhtech_consulting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
# Puis importer database.sql
```

### Problème: "Table doesn't exist"

**Solution:**
Exécutez le script `database.sql` dans phpMyAdmin (voir Étape 2 ci-dessus)

### Problème: Erreur 500 Internal Server Error

**Causes possibles:**
1. Erreur de syntaxe PHP
2. Fichier Database.php ou Env.php introuvable
3. Permissions de fichiers

**Vérification:**
```
http://localhost/MHTECH/test-db-connection.php
```

**Logs Apache:**
- WAMP: `C:\wamp64\logs\apache_error.log`
- XAMPP: `C:\xampp\apache\logs\error.log`

### Problème: Les emails ne partent pas

**C'est NORMAL en développement local !**

Les données sont quand même sauvegardées en base. Les emails sont juste loggés et ignorés.

**Pour activer les emails (optionnel):**
1. Configurez un vrai SMTP dans `.env`:
   ```
   APP_EMAIL = votre-email@gmail.com
   SMTP_HOST_NAME = smtp.gmail.com
   SMTP_PASSWORD = votre_app_password
   SMTP_PORT = 587
   ENCRYPTION_TYPE = tls
   ```

2. Utilisez un App Password Gmail:
   https://myaccount.google.com/apppasswords

---

## ✅ **CHECKLIST DE VALIDATION**

Cochez au fur et à mesure:

- [ ] WAMP/XAMPP démarré (Apache + MySQL)
- [ ] Base `mhtech_consulting` créée
- [ ] Script `database.sql` exécuté
- [ ] 5 tables visibles dans phpMyAdmin
- [ ] `test-db-connection.php` affiche tous les ✅
- [ ] Formulaire contact fonctionne
- [ ] Formulaire CV fonctionne
- [ ] Formulaire recrutement fonctionne
- [ ] Données visibles dans phpMyAdmin

---

## 📊 **RÉSUMÉ DES FORMULAIRES**

| Formulaire | URL | Fichier PHP | Table | Status |
|-----------|-----|-------------|-------|---------|
| Chat Popup | Toutes pages | sendemail.php | contacts | ✅ |
| Sidebar | Toutes pages | sendemail.php | contacts | ✅ |
| Contact | contact.html | sendemail.php | contacts | ✅ |
| Newsletter | Toutes pages | sendemail.php | newsletter_subscriptions | ✅ |
| Contact Staffing | staffing.html | sendemail.php | contacts | ✅ |
| **Dépôt CV** | staffing.html | upload-cv.php | cv_submissions | ✅ |
| **Recrutement** | staffing.html | recruitment-request.php | recruitment_requests | ✅ |

---

## 🎉 **TOUT DEVRAIT FONCTIONNER MAINTENANT !**

Les 3 problèmes majeurs ont été corrigés:
1. ✅ `.htaccess` autorise maintenant les 3 fichiers PHP
2. ✅ Les emails ne bloquent plus l'enregistrement
3. ✅ Les données sont sauvegardées en base de données

**Pour tester immédiatement:**
```
http://localhost/MHTECH/test-db-connection.php
```

**Puis testez vos formulaires sur:**
```
http://localhost/MHTECH/staffing.html
```
