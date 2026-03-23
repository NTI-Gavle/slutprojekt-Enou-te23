<?php
require_once __DIR__ . '/../config/env.php';

function getDBConnection(): PDO {
    static $dbconn = null;
    
    if ($dbconn === null) {
        $env = loadEnv(__DIR__ . '/../.env');
        
        $hostname = $env['DB_HOST'] ?? 'localhost';
        $dbname = $env['DB_NAME'] ?? 'quacko';
        $DB_USER = $env['DB_USER'] ?? 'root';
        $DB_PASSWORD = $env['DB_PASS'] ?? '';
        
        try {
            $dbconn = new PDO(
                "mysql:host={$hostname};dbname={$dbname};charset=utf8mb4",
                $DB_USER,
                $DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('Database connection failed');
        }
    }
    
    return $dbconn;
}

function getDb(): ?PDO {
    try {
        return getDBConnection();
    } catch (Exception $e) {
        return null;
    }
}
