<?php

namespace App\Repositories;

use PDO;
use PDOStatement;
use Exception;
use InvalidArgumentException;

/**
 * BaseRepository - Abstract base repository class
 * 
 * Provides common database operations and query building functionality
 * for all repository classes with security, caching, and performance features.
 * 
 * @package App\Repositories
 * @version 1.0.0
 */
abstract class BaseRepository
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $guarded = ['id', 'created_at', 'updated_at'];
    protected array $casts = [];
    protected bool $timestamps = true;
    protected bool $softDeletes = false;
    protected string $deletedAtColumn = 'deleted_at';
    
    // Query builder properties
    protected array $wheres = [];
    protected array $joins = [];
    protected array $orderBy = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected array $groupBy = [];
    protected array $having = [];
    
    // Caching
    protected bool $cacheEnabled = false;
    protected int $cacheLifetime = 3600; // 1 hour
    protected string $cachePrefix = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = \App\Utils\Database::getInstance();
        $this->cachePrefix = strtolower(class_basename(static::class)) . '_';
        
        if (empty($this->table)) {
            throw new Exception('Table name must be defined in repository');
        }
    }

    /**
     * Find record by primary key
     * 
     * @param mixed $id Primary key value
     * @param array $columns Columns to select
     * @return array|null Record or null if not found
     */
    public function find($id, array $columns = ['*']): ?array
    {
        $cacheKey = $this->cachePrefix . "find_{$id}";
        
        if ($this->cacheEnabled) {
            $cached = $this->getFromCache($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }
        
        $sql = "SELECT " . implode(', ', $columns) . " FROM {$this->table} WHERE {$this->primaryKey} = ?";
        
        if ($this->softDeletes) {
            $sql .= " AND {$this->deletedAtColumn} IS NULL";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $result = $this->castAttributes($result);
            
            if ($this->cacheEnabled) {
                $this->putInCache($cacheKey, $result);
            }
        }
        
        return $result ?: null;
    }

    /**
     * Find record by primary key or throw exception
     * 
     * @param mixed $id Primary key value
     * @param array $columns Columns to select
     * @return array Record
     * @throws Exception
     */
    public function findOrFail($id, array $columns = ['*']): array
    {
        $result = $this->find($id, $columns);
        
        if (!$result) {
            throw new Exception("Record not found in {$this->table} with {$this->primaryKey} = {$id}");
        }
        
        return $result;
    }

    /**
     * Find first record matching conditions
     * 
     * @param array $conditions Where conditions
     * @param array $columns Columns to select
     * @return array|null Record or null if not found
     */
    public function findWhere(array $conditions, array $columns = ['*']): ?array
    {
        $this->resetQuery();
        
        foreach ($conditions as $column => $value) {
            $this->where($column, $value);
        }
        
        return $this->first($columns);
    }

    /**
     * Get first record
     * 
     * @param array $columns Columns to select
     * @return array|null Record or null if not found
     */
    public function first(array $columns = ['*']): ?array
    {
        $this->limit(1);
        $results = $this->get($columns);
        
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Get all records matching current query
     * 
     * @param array $columns Columns to select
     * @return array Array of records
     */
    public function get(array $columns = ['*']): array
    {
        $sql = $this->buildSelectQuery($columns);
        $bindings = $this->getBindings();
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cast attributes for each result
        $results = array_map([$this, 'castAttributes'], $results);
        
        $this->resetQuery();
        
        return $results;
    }

    /**
     * Get paginated results
     * 
     * @param int $perPage Items per page
     * @param int $page Current page
     * @param array $columns Columns to select
     * @return array Paginated results with metadata
     */
    public function paginate(int $perPage = 15, int $page = 1, array $columns = ['*']): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = $this->buildCountQuery();
        $bindings = $this->getBindings();
        
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($bindings);
        $total = $stmt->fetchColumn();
        
        // Get records
        $this->limit($perPage)->offset($offset);
        $records = $this->get($columns);
        
        $lastPage = ceil($total / $perPage);
        
        return [
            'data' => $records,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
            'has_next_page' => $page < $lastPage,
            'has_prev_page' => $page > 1,
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }

    /**
     * Create new record
     * 
     * @param array $data Record data
     * @return array Created record
     */
    public function create(array $data): array
    {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        $id = $this->db->lastInsertId();
        
        // Clear cache
        if ($this->cacheEnabled) {
            $this->clearCache();
        }
        
        return $this->find($id);
    }

    /**
     * Update record by primary key
     * 
     * @param mixed $id Primary key value
     * @param array $data Update data
     * @return bool Success status
     */
    public function update($id, array $data): bool
    {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        if (empty($data)) {
            return true;
        }
        
        $setPairs = array_map(fn($column) => "{$column} = ?", array_keys($data));
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setPairs) . " WHERE {$this->primaryKey} = ?";
        
        $bindings = array_merge(array_values($data), [$id]);
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);
        
        // Clear cache
        if ($this->cacheEnabled) {
            $this->clearCache();
        }
        
        return $result;
    }

    /**
     * Delete record by primary key
     * 
     * @param mixed $id Primary key value
     * @return bool Success status
     */
    public function delete($id): bool
    {
        if ($this->softDeletes) {
            return $this->update($id, [$this->deletedAtColumn => date('Y-m-d H:i:s')]);
        }
        
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$id]);
        
        // Clear cache
        if ($this->cacheEnabled) {
            $this->clearCache();
        }
        
        return $result;
    }

    /**
     * Restore soft deleted record
     * 
     * @param mixed $id Primary key value
     * @return bool Success status
     */
    public function restore($id): bool
    {
        if (!$this->softDeletes) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} SET {$this->deletedAtColumn} = NULL WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$id]);
        
        // Clear cache
        if ($this->cacheEnabled) {
            $this->clearCache();
        }
        
        return $result;
    }

    /**
     * Force delete record (permanent delete)
     * 
     * @param mixed $id Primary key value
     * @return bool Success status
     */
    public function forceDelete($id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$id]);
        
        // Clear cache
        if ($this->cacheEnabled) {
            $this->clearCache();
        }
        
        return $result;
    }

    /**
     * Count records matching current query
     * 
     * @return int Record count
     */
    public function count(): int
    {
        $sql = $this->buildCountQuery();
        $bindings = $this->getBindings();
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        
        $count = $stmt->fetchColumn();
        $this->resetQuery();
        
        return $count;
    }

    /**
     * Check if records exist matching current query
     * 
     * @return bool Existence status
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }

    // Query Builder Methods

    /**
     * Add WHERE condition
     * 
     * @param string $column Column name
     * @param mixed $operator Operator or value if no operator
     * @param mixed $value Value (optional if operator is the value)
     * @param string $boolean Boolean operator (AND/OR)
     * @return self
     */
    public function where(string $column, $operator, $value = null, string $boolean = 'AND'): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];
        
        return $this;
    }

    /**
     * Add OR WHERE condition
     * 
     * @param string $column Column name
     * @param mixed $operator Operator or value
     * @param mixed $value Value (optional)
     * @return self
     */
    public function orWhere(string $column, $operator, $value = null): self
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * Add WHERE IN condition
     * 
     * @param string $column Column name
     * @param array $values Values array
     * @param string $boolean Boolean operator
     * @return self
     */
    public function whereIn(string $column, array $values, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];
        
        return $this;
    }

    /**
     * Add WHERE NOT IN condition
     * 
     * @param string $column Column name
     * @param array $values Values array
     * @param string $boolean Boolean operator
     * @return self
     */
    public function whereNotIn(string $column, array $values, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type' => 'not_in',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];
        
        return $this;
    }

    /**
     * Add WHERE NULL condition
     * 
     * @param string $column Column name
     * @param string $boolean Boolean operator
     * @return self
     */
    public function whereNull(string $column, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type' => 'null',
            'column' => $column,
            'boolean' => $boolean
        ];
        
        return $this;
    }

    /**
     * Add WHERE NOT NULL condition
     * 
     * @param string $column Column name
     * @param string $boolean Boolean operator
     * @return self
     */
    public function whereNotNull(string $column, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type' => 'not_null',
            'column' => $column,
            'boolean' => $boolean
        ];
        
        return $this;
    }

    /**
     * Add WHERE BETWEEN condition
     * 
     * @param string $column Column name
     * @param mixed $min Minimum value
     * @param mixed $max Maximum value
     * @param string $boolean Boolean operator
     * @return self
     */
    public function whereBetween(string $column, $min, $max, string $boolean = 'AND'): self
    {
        $this->wheres[] = [
            'type' => 'between',
            'column' => $column,
            'min' => $min,
            'max' => $max,
            'boolean' => $boolean
        ];
        
        return $this;
    }

    /**
     * Add LIKE condition
     * 
     * @param string $column Column name
     * @param string $pattern Search pattern
     * @param string $boolean Boolean operator
     * @return self
     */
    public function whereLike(string $column, string $pattern, string $boolean = 'AND'): self
    {
        return $this->where($column, 'LIKE', $pattern, $boolean);
    }

    /**
     * Add JOIN clause
     * 
     * @param string $table Table to join
     * @param string $first First column
     * @param string $operator Join operator
     * @param string $second Second column
     * @param string $type Join type
     * @return self
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = [
            'type' => $type,
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];
        
        return $this;
    }

    /**
     * Add LEFT JOIN clause
     * 
     * @param string $table Table to join
     * @param string $first First column
     * @param string $operator Join operator
     * @param string $second Second column
     * @return self
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * Add RIGHT JOIN clause
     * 
     * @param string $table Table to join
     * @param string $first First column
     * @param string $operator Join operator
     * @param string $second Second column
     * @return self
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    /**
     * Add ORDER BY clause
     * 
     * @param string $column Column name
     * @param string $direction Sort direction
     * @return self
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy[] = [
            'column' => $column,
            'direction' => strtoupper($direction)
        ];
        
        return $this;
    }

    /**
     * Add GROUP BY clause
     * 
     * @param string $column Column name
     * @return self
     */
    public function groupBy(string $column): self
    {
        $this->groupBy[] = $column;
        return $this;
    }

    /**
     * Add HAVING clause
     * 
     * @param string $column Column name
     * @param mixed $operator Operator or value
     * @param mixed $value Value (optional)
     * @return self
     */
    public function having(string $column, $operator, $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->having[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];
        
        return $this;
    }

    /**
     * Set LIMIT
     * 
     * @param int $limit Limit value
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set OFFSET
     * 
     * @param int $offset Offset value
     * @return self
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Build SELECT query
     * 
     * @param array $columns Columns to select
     * @return string SQL query
     */
    protected function buildSelectQuery(array $columns = ['*']): string
    {
        $sql = "SELECT " . implode(', ', $columns) . " FROM {$this->table}";
        
        // Add JOINs
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }
        
        // Add WHERE conditions
        $sql .= $this->buildWhereClause();
        
        // Add GROUP BY
        if (!empty($this->groupBy)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBy);
        }
        
        // Add HAVING
        if (!empty($this->having)) {
            $havingConditions = [];
            foreach ($this->having as $having) {
                $havingConditions[] = "{$having['column']} {$having['operator']} ?";
            }
            $sql .= " HAVING " . implode(' AND ', $havingConditions);
        }
        
        // Add ORDER BY
        if (!empty($this->orderBy)) {
            $orderConditions = [];
            foreach ($this->orderBy as $order) {
                $orderConditions[] = "{$order['column']} {$order['direction']}";
            }
            $sql .= " ORDER BY " . implode(', ', $orderConditions);
        }
        
        // Add LIMIT and OFFSET
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }
        
        return $sql;
    }

    /**
     * Build COUNT query
     * 
     * @return string SQL query
     */
    protected function buildCountQuery(): string
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        
        // Add JOINs
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }
        
        // Add WHERE conditions
        $sql .= $this->buildWhereClause();
        
        return $sql;
    }

    /**
     * Build WHERE clause
     * 
     * @return string WHERE clause
     */
    protected function buildWhereClause(): string
    {
        $conditions = [];
        
        // Add soft delete condition
        if ($this->softDeletes) {
            $conditions[] = "{$this->table}.{$this->deletedAtColumn} IS NULL";
        }
        
        // Add custom WHERE conditions
        foreach ($this->wheres as $where) {
            $boolean = empty($conditions) ? '' : " {$where['boolean']} ";
            
            switch ($where['type']) {
                case 'basic':
                    $conditions[] = $boolean . "{$where['column']} {$where['operator']} ?";
                    break;
                case 'in':
                    $placeholders = str_repeat('?,', count($where['values']) - 1) . '?';
                    $conditions[] = $boolean . "{$where['column']} IN ({$placeholders})";
                    break;
                case 'not_in':
                    $placeholders = str_repeat('?,', count($where['values']) - 1) . '?';
                    $conditions[] = $boolean . "{$where['column']} NOT IN ({$placeholders})";
                    break;
                case 'null':
                    $conditions[] = $boolean . "{$where['column']} IS NULL";
                    break;
                case 'not_null':
                    $conditions[] = $boolean . "{$where['column']} IS NOT NULL";
                    break;
                case 'between':
                    $conditions[] = $boolean . "{$where['column']} BETWEEN ? AND ?";
                    break;
            }
        }
        
        return empty($conditions) ? '' : ' WHERE ' . implode('', $conditions);
    }

    /**
     * Get query bindings
     * 
     * @return array Binding values
     */
    protected function getBindings(): array
    {
        $bindings = [];
        
        foreach ($this->wheres as $where) {
            switch ($where['type']) {
                case 'basic':
                    $bindings[] = $where['value'];
                    break;
                case 'in':
                case 'not_in':
                    $bindings = array_merge($bindings, $where['values']);
                    break;
                case 'between':
                    $bindings[] = $where['min'];
                    $bindings[] = $where['max'];
                    break;
            }
        }
        
        // Add HAVING bindings
        foreach ($this->having as $having) {
            $bindings[] = $having['value'];
        }
        
        return $bindings;
    }

    /**
     * Reset query builder
     * 
     * @return void
     */
    protected function resetQuery(): void
    {
        $this->wheres = [];
        $this->joins = [];
        $this->orderBy = [];
        $this->groupBy = [];
        $this->having = [];
        $this->limit = null;
        $this->offset = null;
    }

    /**
     * Filter data to only fillable columns
     * 
     * @param array $data Input data
     * @return array Filtered data
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return array_diff_key($data, array_flip($this->guarded));
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Cast attributes according to casts configuration
     * 
     * @param array $attributes Attributes array
     * @return array Casted attributes
     */
    protected function castAttributes(array $attributes): array
    {
        foreach ($this->casts as $key => $cast) {
            if (isset($attributes[$key])) {
                $attributes[$key] = $this->castAttribute($attributes[$key], $cast);
            }
        }
        
        return $attributes;
    }

    /**
     * Cast single attribute
     * 
     * @param mixed $value Attribute value
     * @param string $cast Cast type
     * @return mixed Casted value
     */
    protected function castAttribute($value, string $cast)
    {
        if ($value === null) {
            return null;
        }
        
        switch ($cast) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'array':
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'object':
                return is_string($value) ? json_decode($value) : $value;
            case 'date':
                return date('Y-m-d', strtotime($value));
            case 'datetime':
                return date('Y-m-d H:i:s', strtotime($value));
            default:
                return $value;
        }
    }

    // Cache Methods

    /**
     * Get from cache
     * 
     * @param string $key Cache key
     * @return mixed Cached value or null
     */
    protected function getFromCache(string $key)
    {
        if (!$this->cacheEnabled) {
            return null;
        }
        
        $cacheFile = $this->getCacheFilePath($key);
        
        if (!file_exists($cacheFile)) {
            return null;
        }
        
        if (time() - filemtime($cacheFile) > $this->cacheLifetime) {
            unlink($cacheFile);
            return null;
        }
        
        $content = file_get_contents($cacheFile);
        return $content ? unserialize($content) : null;
    }

    /**
     * Put in cache
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @return bool Success status
     */
    protected function putInCache(string $key, $value): bool
    {
        if (!$this->cacheEnabled) {
            return false;
        }
        
        $cacheFile = $this->getCacheFilePath($key);
        $cacheDir = dirname($cacheFile);
        
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        return file_put_contents($cacheFile, serialize($value)) !== false;
    }

    /**
     * Clear cache
     * 
     * @return bool Success status
     */
    protected function clearCache(): bool
    {
        if (!$this->cacheEnabled) {
            return true;
        }
        
        $cacheDir = dirname(__DIR__, 2) . '/storage/cache/repositories/';
        $pattern = $cacheDir . $this->cachePrefix . '*';
        
        $files = glob($pattern);
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }

    /**
     * Get cache file path
     * 
     * @param string $key Cache key
     * @return string Cache file path
     */
    protected function getCacheFilePath(string $key): string
    {
        $cacheDir = dirname(__DIR__, 2) . '/storage/cache/repositories/';
        return $cacheDir . $key . '.cache';
    }

    /**
     * Execute raw SQL query
     * 
     * @param string $sql SQL query
     * @param array $bindings Query bindings
     * @return PDOStatement
     */
    protected function query(string $sql, array $bindings = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        
        return $stmt;
    }

    /**
     * Begin database transaction
     * 
     * @return bool Success status
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Commit database transaction
     * 
     * @return bool Success status
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Rollback database transaction
     * 
     * @return bool Success status
     */
    public function rollback(): bool
    {
        return $this->db->rollback();
    }

    /**
     * Get class basename
     * 
     * @param string $class Class name
     * @return string Base class name
     */
    private function class_basename(string $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
}