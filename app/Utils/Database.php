<?php
namespace App\Utils;

use PDO;
use PDOException;

/**
 * Database Connection and Query Manager
 * ABO-WBO Management System
 */
class Database
{
    private static $instance = null;
    private $pdo;
    private $config;
    
    private function __construct()
    {
        $this->config = config('database');
        $this->connect();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    private function connect(): void
    {
        $dsn = sprintf(
            "mysql:host=%s;port=%s;dbname=%s;charset=%s",
            $this->config['host'],
            $this->config['port'] ?? '3306',
            $this->config['name'],
            $this->config['charset']
        );
        
        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            // Merge with additional options from config if they exist
            if (isset($this->config['options']) && is_array($this->config['options'])) {
                $options = array_merge($options, $this->config['options']);
            }
            
            $this->pdo = new PDO($dsn, $this->config['user'], $this->config['pass'], $options);
            
        } catch (PDOException $e) {
            log_error("Database connection failed", ['error' => $e->getMessage()]);
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
    
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            
            // Log query in debug mode
            if (config('debug')) {
                log_info("Database Query", ['sql' => $sql, 'params' => $params]);
            }
            
            $stmt->execute($params);
            return $stmt;
            
        } catch (PDOException $e) {
            log_error("Database query failed", [
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            throw new \Exception("Database query failed: " . $e->getMessage());
        }
    }
    
    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function fetchColumn(string $sql, array $params = [], int $column = 0)
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn($column);
    }
    
    public function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = ':' . implode(', :', $columns);
        $columnList = implode(', ', $columns);
        
        $sql = "INSERT INTO {$table} ({$columnList}) VALUES ({$placeholders})";
        
        $this->query($sql, $data);
        return (int) $this->pdo->lastInsertId();
    }
    
    public function update(string $table, array $data, array $where): int
    {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setParts);
        
        $whereParts = [];
        $whereData = [];
        foreach ($where as $column => $value) {
            $whereKey = "where_{$column}";
            $whereParts[] = "{$column} = :{$whereKey}";
            $whereData[$whereKey] = $value;
        }
        $whereClause = implode(' AND ', $whereParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$whereClause}";
        
        $stmt = $this->query($sql, array_merge($data, $whereData));
        return $stmt->rowCount();
    }
    
    public function delete(string $table, array $where): int
    {
        $whereParts = [];
        foreach (array_keys($where) as $column) {
            $whereParts[] = "{$column} = :{$column}";
        }
        $whereClause = implode(' AND ', $whereParts);
        
        $sql = "DELETE FROM {$table} WHERE {$whereClause}";
        
        $stmt = $this->query($sql, $where);
        return $stmt->rowCount();
    }
    
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
    
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }
    
    public function commit(): bool
    {
        return $this->pdo->commit();
    }
    
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }
    
    public function transaction(callable $callback)
    {
        $this->beginTransaction();
        
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
            
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    public function tableExists(string $table): bool
    {
        $sql = "SHOW TABLES LIKE :table";
        $result = $this->fetch($sql, ['table' => $table]);
        return $result !== null;
    }
    
    public function columnExists(string $table, string $column): bool
    {
        $sql = "SHOW COLUMNS FROM {$table} LIKE :column";
        $result = $this->fetch($sql, ['column' => $column]);
        return $result !== null;
    }
    
    /**
     * Build a paginated query
     */
    public function paginate(string $sql, array $params = [], int $page = 1, int $perPage = 20): array
    {
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as count_query";
        $total = (int) $this->fetchColumn($countSql, $params);
        
        // Calculate pagination
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        // Get paginated results
        $paginatedSql = "{$sql} LIMIT {$perPage} OFFSET {$offset}";
        $data = $this->fetchAll($paginatedSql, $params);
        
        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_more_pages' => $page < $totalPages,
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ]
        ];
    }
    
    /**
     * Execute raw SQL (for migrations, etc.)
     */
    public function exec(string $sql): int
    {
        try {
            return $this->pdo->exec($sql);
        } catch (PDOException $e) {
            log_error("Database exec failed", ['sql' => $sql, 'error' => $e->getMessage()]);
            throw new \Exception("Database exec failed: " . $e->getMessage());
        }
    }
    
    /**
     * Check database connection
     */
    public function isConnected(): bool
    {
        try {
            $this->pdo->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get database version info
     */
    public function getVersion(): string
    {
        return $this->fetchColumn('SELECT VERSION()');
    }
    
    public function __destruct()
    {
        $this->pdo = null;
    }
}