# 📦 Installation du Backend MHTECH Consulting

Ce guide vous explique comment installer et configurer le backend PHP/MySQL pour tous les formulaires du site MHTECH Consulting.

---

## 🎯 Prérequis

- **WAMP/XAMPP/MAMP** (serveur Apache + PHP 8.0+ + MySQL 5.7+)
- **PHPMailer** (déjà installé dans `assets/inc/app/PHPMailer/`)
- Accès à **phpMyAdmin** ou ligne de commande MySQL

---

## 📋 Étape 1: Créer la Base de Données

### Option A: Via phpMyAdmin (Recommandé)

1. Ouvrez **phpMyAdmin** (http://localhost/phpmyadmin)
2. Cliquez sur l'onglet **"SQL"**
3. Ouvrez le fichier `assets/inc/database.sql` dans un éditeur
4. **Copiez tout le contenu** du fichier
5. **Collez** dans la zone de texte SQL de phpMyAdmin
6. Cliquez sur **"Exécuter"**

✅ La base de données `mhtech_consulting` sera créée avec toutes les tables.

### Option B: Via ligne de commande

```bash
# Depuis le dossier assets/inc/
mysql -u root -p < database.sql
```

---

## 🔧 Étape 2: Configuration des Variables d'Environnement

Le fichier `.env` contient déjà la configuration par défaut pour WAMP:

```env
# Database Configuration (déjà configuré)
DB_HOST = localhost
DB_NAME = mhtech_consulting
DB_USER = root
DB_PASSWORD =
```

### ⚠️ Si vous utilisez un mot de passe MySQL:

1. Ouvrez `assets/inc/.env`
2. Modifiez la ligne `DB_PASSWORD` :
   ```env
   DB_PASSWORD = votre_mot_de_passe
   ```

### ⚠️ Si vous utilisez XAMPP/MAMP:

Les paramètres par défaut devraient fonctionner sans modification.

---

## 📁 Étape 3: Créer le Dossier de Stockage des CVs

Le dossier sera créé automatiquement lors du premier upload, mais vous pouvez le créer manuellement:

### Sur Windows (WAMP):
```cmd
mkdir "C:\wamp64\www\MHTECH\assets\inc\uploads\cvs"
```

### Permissions:
Assurez-vous que le dossier est accessible en écriture par Apache:
- Sous Windows: Clic droit > Propriétés > Sécurité > Modifier (Donner accès complet à "Utilisateurs")

---

## 🧪 Étape 4: Tester la Configuration

### Test 1: Vérifier la Base de Données

1. Ouvrez **phpMyAdmin**
2. Sélectionnez la base `mhtech_consulting`
3. Vous devriez voir **5 tables**:
   - ✅ `contacts`
   - ✅ `newsletter_subscriptions`
   - ✅ `cv_submissions`
   - ✅ `recruitment_requests`
   - ✅ `activity_logs`

### Test 2: Tester un Formulaire

1. Démarrez WAMP/XAMPP
2. Ouvrez votre site: `http://localhost/MHTECH/`
3. Allez sur la page **Contact** ou **Staffing IT**
4. Remplissez un formulaire de test
5. Vérifiez dans **phpMyAdmin** que les données sont enregistrées

---

## 📧 Étape 5: Configuration Email (Optionnel)

Les emails sont déjà configurés dans `.env` avec les identifiants de Script Fusions.

### Pour utiliser vos propres identifiants:

1. Ouvrez `assets/inc/.env`
2. Modifiez ces lignes:

```env
APP_NAME = "MHTECH Consulting"
APP_EMAIL = contact@mhtechconsulting.com
ADMIN_EMAIL = votre-email@example.com

# Configuration SMTP (exemple Gmail)
SMTP_HOST_NAME = smtp.gmail.com
SMTP_PASSWORD = votre_app_password
ENCRYPTION_TYPE = tls
SMTP_PORT = 587
```

### 📌 Note pour Gmail:
- Utilisez un **App Password** (https://myaccount.google.com/apppasswords)
- N'utilisez PAS votre mot de passe Gmail normal

---

## 🔍 Architecture des Fichiers Backend

```
assets/inc/
├── database.sql                    # Script SQL (tables + vues)
├── .env                            # Configuration (DB + Email)
├── sendemail.php                   # Formulaires généraux (✅ mis à jour)
├── upload-cv.php                   # Dépôt de CV (✅ nouveau)
├── recruitment-request.php         # Demandes de recrutement (✅ nouveau)
├── app/
│   ├── Database.php               # Classe de connexion MySQL (✅ nouveau)
│   ├── Env.php                    # Gestion variables d'environnement
│   ├── settings.php               # Configuration PHPMailer
│   └── PHPMailer/                 # Librairie email
└── uploads/
    └── cvs/                       # Stockage sécurisé des CVs
        └── .htaccess              # Protection (deny from all)
```

---

## 📊 Tables de la Base de Données

### 1️⃣ **contacts** (Formulaires généraux)
Stocke tous les contacts provenant de:
- Chat popup
- Sidebar consultation
- Page contact
- Contact section staffing

**Colonnes**: id, name, email, phone, subject, request_type, message, source, ip_address, user_agent, created_at

---

### 2️⃣ **newsletter_subscriptions** (Abonnements newsletter)
Stocke les emails des abonnés newsletter.

**Colonnes**: id, email (UNIQUE), status, source, ip_address, subscribed_at, unsubscribed_at

---

### 3️⃣ **cv_submissions** (CVs déposés)
Stocke les candidatures avec fichiers CV.

**Colonnes**: id, name, email, phone, position, cv_filename, cv_original_name, cv_file_size, cv_mime_type, message, ip_address, user_agent, status, created_at, reviewed_at

**Statuts**: new, reviewed, contacted, archived

---

### 4️⃣ **recruitment_requests** (Demandes de recrutement)
Stocke les demandes des entreprises.

**Colonnes**: id, company, contact_name, email, phone, profile, duration, message, ip_address, user_agent, status, created_at, updated_at

**Statuts**: new, in_progress, proposal_sent, closed, cancelled

---

### 5️⃣ **activity_logs** (Logs d'activité)
Enregistre toutes les actions pour audit.

**Colonnes**: id, table_name, record_id, action, ip_address, user_agent, created_at

---

## 🛡️ Sécurité Implémentée

### ✅ Protection des Uploads
- Validation du type MIME réel (pas seulement l'extension)
- Taille maximale: 5MB
- Extensions autorisées: PDF, DOC, DOCX
- Noms de fichiers aléatoires (uniqid)
- Dossier protégé par `.htaccess` (deny from all)

### ✅ Protection SQL Injection
- Utilisation de **PDO avec prepared statements**
- Tous les paramètres sont bindés (`:placeholder`)
- Aucune concaténation SQL directe

### ✅ Protection XSS
- Validation et nettoyage de toutes les entrées (`preg_replace`)
- Échappement HTML avec `htmlspecialchars()`
- Suppression des headers malveillants (From:, To:, BCC:, etc.)

### ✅ Logs et Traçabilité
- Enregistrement IP + User Agent
- Table `activity_logs` pour audit
- Logs d'erreur avec `error_log()`

---

## 🚀 Fonctionnalités

### ✅ Formulaires Fonctionnels (7/9)
1. ✅ Chat Popup (sendemail.php)
2. ✅ Sidebar Consultation (sendemail.php)
3. ✅ Contact Principal (sendemail.php)
4. ✅ Newsletter (sendemail.php)
5. ✅ Contact Staffing (sendemail.php)
6. ✅ **Dépôt CV** (upload-cv.php) ✨ NOUVEAU
7. ✅ **Demande Recrutement** (recruitment-request.php) ✨ NOUVEAU

### ✅ Emails Automatiques
- Confirmation candidat/entreprise
- Notification admin avec pièces jointes
- Templates HTML professionnels
- Version texte (AltBody)

### ✅ Base de Données
- 5 tables relationnelles
- 3 vues pour statistiques
- Indexes optimisés
- Charset UTF-8 (utf8mb4)

---

## 🔧 Dépannage

### Erreur: "Database connection error"
**Solution**: Vérifiez les identifiants dans `.env`:
```env
DB_HOST = localhost
DB_NAME = mhtech_consulting
DB_USER = root
DB_PASSWORD =
```

### Erreur: "Table doesn't exist"
**Solution**: Exécutez le script SQL `database.sql` dans phpMyAdmin.

### Erreur: "Permission denied" (upload)
**Solution**: Créez manuellement le dossier `assets/inc/uploads/cvs/` et donnez les permissions d'écriture.

### Les emails ne partent pas
**Solution**: Vérifiez la configuration SMTP dans `.env`:
- Host correct
- Port correct (587 pour TLS, 465 pour SSL)
- App Password (pas le mot de passe normal)

### Erreur: "Class 'Database' not found"
**Solution**: Vérifiez que `Database.php` existe dans `assets/inc/app/` et que le fichier est lisible.

---

## 📈 Consulter les Données

### Via phpMyAdmin:
```sql
-- Tous les contacts
SELECT * FROM contacts ORDER BY created_at DESC;

-- CVs reçus
SELECT * FROM cv_submissions WHERE status = 'new' ORDER BY created_at DESC;

-- Demandes de recrutement
SELECT * FROM recruitment_requests WHERE status = 'new' ORDER BY created_at DESC;

-- Statistiques newsletter
SELECT COUNT(*) as total_subscribers FROM newsletter_subscriptions WHERE status = 'active';
```

### Vues pré-configurées:
```sql
-- Stats contacts
SELECT * FROM v_contacts_stats;

-- Stats CVs
SELECT * FROM v_cv_stats;

-- Stats recrutement
SELECT * FROM v_recruitment_stats;
```

---

## ✅ Checklist d'Installation

- [ ] Base de données créée (`mhtech_consulting`)
- [ ] 5 tables créées (contacts, newsletter_subscriptions, cv_submissions, recruitment_requests, activity_logs)
- [ ] Fichier `.env` configuré (DB + Email)
- [ ] Dossier `uploads/cvs/` créé avec permissions
- [ ] WAMP/XAMPP démarré (Apache + MySQL)
- [ ] Test d'un formulaire effectué
- [ ] Données visibles dans phpMyAdmin

---

## 📞 Support

En cas de problème:
1. Vérifiez les logs Apache (erreur PHP)
2. Vérifiez les logs MySQL (erreur SQL)
3. Consultez les logs applicatifs (voir `error_log()` dans le code)

---

**🎉 Installation terminée ! Tous vos formulaires sont maintenant opérationnels avec stockage en base de données et envoi d'emails automatiques.**
