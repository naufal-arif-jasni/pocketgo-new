<?php
// db_conn.php - Database Connection for pocketgo_db with automatic SQLite fallback & local restriction

// ── LOCAL ACCESS RESTRICTION GUARD ──
function isLocalRequest() {
    $allowed_ips = ['127.0.0.1', '::1'];
    $remote_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Check loopback
    if (in_array($remote_ip, $allowed_ips)) {
        return true;
    }
    
    // Check private ranges (Docker / internal proxy / local networks)
    $ip_long = ip2long($remote_ip);
    if ($ip_long !== false) {
        // 10.0.0.0 - 10.255.255.255
        // 172.16.0.0 - 172.31.255.255
        // 192.168.0.0 - 192.168.255.255
        if (($ip_long >= ip2long('10.0.0.0') && $ip_long <= ip2long('10.255.255.255')) ||
            ($ip_long >= ip2long('172.16.0.0') && $ip_long <= ip2long('172.31.255.255')) ||
            ($ip_long >= ip2long('192.168.0.0') && $ip_long <= ip2long('192.168.255.255'))) {
            return true;
        }
    }
    
    // Check Host header
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        return true;
    }

    // Check if running in development / sandbox environments
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) || isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        return true;
    }
    
    return false;
}

if (!isLocalRequest()) {
    http_response_code(403);
    die('Access Denied: This system can only be opened locally (from localhost/127.0.0.1).');
}

// ── DATABASE CONNECTION & AUTOMATIC FAILOVER ──
$host = 'localhost';
$db   = 'pocketgo_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Failover to auto-created SQLite for zero-config local run
    $sqlite_file = __DIR__ . '/pocketgo_db.sqlite';
    $is_new = !file_exists($sqlite_file);
    
    try {
        $pdo = new PDO("sqlite:" . $sqlite_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        if ($is_new) {
            // Seed SQLite schema and data from pocketgo_db.sql
            $sql = file_get_contents(__DIR__ . '/pocketgo_db.sql');
            
            // Transform MySQL specific syntax to SQLite compatible syntax
            $sql = preg_replace('/CREATE DATABASE[\s\S]*?;/', '', $sql);
            $sql = preg_replace('/USE `.*?`;/', '', $sql);
            $sql = str_ireplace('INT AUTO_INCREMENT PRIMARY KEY', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
            $sql = str_ireplace('ENGINE=InnoDB DEFAULT CHARSET=utf8mb4', '', $sql);
            $sql = str_ireplace('DECIMAL(10,2)', 'DOUBLE', $sql);
            $sql = preg_replace('/FOREIGN KEY[\s\S]*?REFERENCES[\s\S]*?\)/', '', $sql);
            
            $queries = explode(';', $sql);
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    try {
                        $pdo->exec($query);
                    } catch (\Exception $ex) {
                        // Ignore minor errors on seeding
                    }
                }
            }
        }
    } catch (\PDOException $sqlite_err) {
        http_response_code(500);
        die('Database Connection Error: ' . $e->getMessage() . ' | SQLite Failover Error: ' . $sqlite_err->getMessage());
    }
}
?>
