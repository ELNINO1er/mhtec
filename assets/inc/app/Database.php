<?php

/**
 * Database Class
 * Classe singleton pour gérer la connexion à la base de données MySQL
 * Utilise PDO pour la sécurité et la performance
 */
class Database
{
    private static $instance = null;
    private $connection = null;
    private $host;
    private $database;
    private $username;
    private $password;
    private $charset = 'utf8mb4';

    /**
     * Constructeur privé (pattern Singleton)
     */
    private function __construct()
    {
        // Charger les variables d'environnement
        $this->host = Env::get('DB_HOST', 'localhost');
        $this->database = Env::get('DB_NAME', 'mhtech_consulting');
        $this->username = Env::get('DB_USER', 'root');
        $this->password = Env::get('DB_PASSWORD', '');

        $this->connect();
    }

    /**
     * Récupère l'instance unique de Database (Singleton)
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Établit la connexion à la base de données
     */
    private function connect()
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);

        } catch (PDOException $e) {
            // En production, logger l'erreur au lieu de l'afficher
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
        }
    }

    /**
     * Récupère la connexion PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Prépare et exécute une requête INSERT
     * @return int ID du dernier élément inséré
     */
    public function insert($table, $data)
    {
        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->connection->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->execute();
        return $this->connection->lastInsertId();
    }

    /**
     * Prépare et exécute une requête UPDATE
     * @return int Nombre de lignes affectées
     */
    public function update($table, $data, $where, $whereParams = [])
    {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        $setString = implode(', ', $set);

        $sql = "UPDATE {$table} SET {$setString} WHERE {$where}";
        $stmt = $this->connection->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        foreach ($whereParams as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Prépare et exécute une requête SELECT
     * @return array Résultats de la requête
     */
    public function select($table, $fields = '*', $where = '', $whereParams = [], $orderBy = '', $limit = '')
    {
        $sql = "SELECT {$fields} FROM {$table}";

        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }

        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if (!empty($limit)) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->connection->prepare($sql);

        foreach ($whereParams as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Exécute une requête personnalisée
     * @return PDOStatement
     */
    public function query($sql, $params = [])
    {
        $stmt = $this->connection->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Commence une transaction
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Valide une transaction
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Annule une transaction
     */
    public function rollback()
    {
        return $this->connection->rollback();
    }

    /**
     * Empêche le clonage de l'instance (Singleton)
     */
    private function __clone() {}

    /**
     * Empêche la désérialisation de l'instance (Singleton)
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
