<?php
/**
 * Test de connexion à la base de données
 * Ouvrez ce fichier dans votre navigateur: http://localhost/MHTECH/test-db-connection.php
 */

echo "<h1>Test de Connexion Base de Données MHTECH</h1>";
echo "<hr>";

// Test 1: Charger les fichiers
echo "<h2>Test 1: Chargement des fichiers</h2>";
try {
    require_once __DIR__ . '/assets/inc/app/Env.php';
    echo "✅ Env.php chargé<br>";

    require_once __DIR__ . '/assets/inc/app/Database.php';
    echo "✅ Database.php chargé<br>";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
    die();
}

// Test 2: Charger .env
echo "<h2>Test 2: Configuration .env</h2>";
try {
    Env::load(__DIR__ . '/assets/inc/.env');
    echo "✅ Fichier .env chargé<br>";

    echo "DB_HOST: " . Env::get('DB_HOST', 'NON DÉFINI') . "<br>";
    echo "DB_NAME: " . Env::get('DB_NAME', 'NON DÉFINI') . "<br>";
    echo "DB_USER: " . Env::get('DB_USER', 'NON DÉFINI') . "<br>";
    echo "DB_PASSWORD: " . (empty(Env::get('DB_PASSWORD')) ? '(vide)' : '(défini)') . "<br>";
} catch (Exception $e) {
    echo "❌ Erreur .env: " . $e->getMessage() . "<br>";
}

// Test 3: Connexion MySQL
echo "<h2>Test 3: Connexion MySQL</h2>";
try {
    $db = Database::getInstance();
    echo "✅ Connexion à la base de données réussie!<br>";

    $conn = $db->getConnection();
    echo "✅ Objet PDO obtenu<br>";
} catch (Exception $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "<br>";
    echo "<p style='color:red;'><strong>SOLUTION:</strong> Vérifiez que MySQL est démarré dans WAMP et que la base 'mhtech_consulting' existe.</p>";
    die();
}

// Test 4: Vérifier les tables
echo "<h2>Test 4: Vérification des tables</h2>";
try {
    $tables = [
        'contacts',
        'newsletter_subscriptions',
        'cv_submissions',
        'recruitment_requests',
        'activity_logs'
    ];

    foreach ($tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' existe<br>";
        } else {
            echo "❌ Table '$table' n'existe PAS<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}

// Test 5: Test d'insertion
echo "<h2>Test 5: Test d'insertion</h2>";
try {
    $testData = [
        'email' => 'test_' . time() . '@example.com',
        'source' => 'test_script',
        'ip_address' => '127.0.0.1'
    ];

    $id = $db->insert('newsletter_subscriptions', $testData);
    echo "✅ Insertion réussie! ID: $id<br>";

    // Compter les enregistrements
    $stmt = $conn->query("SELECT COUNT(*) as total FROM newsletter_subscriptions");
    $result = $stmt->fetch();
    echo "✅ Total abonnés newsletter: " . $result['total'] . "<br>";

} catch (Exception $e) {
    echo "❌ Erreur d'insertion: " . $e->getMessage() . "<br>";
}

// Test 6: Vérifier les données existantes
echo "<h2>Test 6: Données dans les tables</h2>";
try {
    $tables = ['contacts', 'cv_submissions', 'recruitment_requests', 'newsletter_subscriptions'];

    foreach ($tables as $table) {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM $table");
        $result = $stmt->fetch();
        echo "Table '$table': <strong>" . $result['total'] . " enregistrement(s)</strong><br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>Résumé</h2>";
echo "<p>Si tous les tests sont ✅, votre base de données fonctionne correctement.</p>";
echo "<p>Si vous voyez des ❌, suivez les instructions affichées pour corriger les problèmes.</p>";
?>
