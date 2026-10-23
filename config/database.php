<?php
/**
 * Database Configuration — SkillBridge
 * 
 * Uses PDO for secure database access.
 * Update the constants below to match your XAMPP MySQL setup.
 * 
 * IMPORTANT: In production, move credentials to environment
 * variables or a file outside the web root.
 */

// Database credentials — change these for your environment
define('DB_HOST', 'localhost');
define('DB_NAME', 'skillbridge');
define('DB_USER', 'root');
define('DB_PASS', '');          // Default XAMPP MySQL has no password
define('DB_CHARSET', 'utf8mb4');

/**
 * Get a PDO database connection if available, or null if database is not yet connected.
 * This allows Stage 2 frontend testing with sample data before Stage 3 DB setup.
 *
 * @return PDO|null
 */
function getDBOrNull(): ?PDO {
    static $pdo = null;
    static $attempted = false;
    
    if ($pdo === null && !$attempted) {
        $attempted = true;
        
        // 1. Try MySQL Connection First (Standard XAMPP default)
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            return $pdo;
        } catch (PDOException $e) {
            // MySQL not accessible or credentials needed; fallback to embedded database
        }

        // 2. Fallback to embedded relational database (zero configuration)
        $sqliteFile = __DIR__ . '/../database/skillbridge.db';
        if (file_exists($sqliteFile)) {
            try {
                $pdo = new PDO('sqlite:' . $sqliteFile);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $pdo->exec('PRAGMA foreign_keys = ON;');
                // SQLite MySQL compatibility functions
                $pdo->sqliteCreateFunction('CURDATE', fn() => date('Y-m-d'));
                $pdo->sqliteCreateFunction('NOW', fn() => date('Y-m-d H:i:s'));
                $pdo->sqliteCreateFunction('FIELD', function(...$args) {
                    if (count($args) < 2) return 0;
                    $val = array_shift($args);
                    $pos = array_search($val, $args, false);
                    return $pos === false ? 0 : $pos + 1;
                });
                return $pdo;
            } catch (PDOException $e) {
                $pdo = null;
            }
        }
    }
    return $pdo;
}

/**
 * Get a PDO database connection.
 * Uses a static variable to reuse the same connection within a request.
 *
 * @return PDO
 * @throws PDOException if connection fails
 */
function getDB(): PDO {
    $db = getDBOrNull();
    if ($db === null) {
        die('Database connection failed. In Stage 2, ensure MySQL is running or use sample data mode.');
    }
    return $db;
}

