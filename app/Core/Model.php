<?php
namespace App\Core;

use App\Utils\Database;

/**
 * Base Model Class
 * ABO-WBO Management System
 */
abstract class Model
{
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $guarded = [];
    protected $timestamps = true;
    protected $dateFormat = 'Y-m-d H:i:s';
    
    protected $db;
    protected $attributes = [];
    protected $original = [];
    protected $changes = [];
    protected $exists = false;
    
    public function __construct(array $attributes = [])
    {
        $this->db = Database::getInstance();
        $this->fill($attributes);
    }
    
    /**
     * Fill model with attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->attributes[$key] = $value;
            }
        }
        
        return $this;
    }
    
    /**
     * Check if attribute is fillable
     */
    protected function isFillable(string $key): bool
    {
        if (in_array($key, $this->guarded)) {
            return false;
        }
        
        if (empty($this->fillable)) {
            return true;
        }
        
        return in_array($key, $this->fillable);
    }
    
    /**
     * Get attribute value
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }
    
    /**
     * Set attribute value
     */
    public function __set(string $key, $value)
    {
        $this->setAttribute($key, $value);
    }
    
    /**
     * Get attribute
     */
    public function getAttribute(string $key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
        
        return null;
    }
    
    /**
     * Set attribute
     */
    public function setAttribute(string $key, $value): void
    {
        if ($this->isFillable($key)) {
            $this->attributes[$key] = $value;
        }
    }
    
    /**
     * Get all attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    /**
     * Check if model exists in database
     */
    public function exists(): bool
    {
        return $this->exists;
    }
    
    /**
     * Save model to database
     */
    public function save(): bool
    {
        if ($this->exists) {
            return $this->performUpdate();
        } else {
            return $this->performInsert();
        }
    }
    
    /**
     * Perform insert operation
     */
    protected function performInsert(): bool
    {
        $attributes = $this->attributes;
        
        if ($this->timestamps) {
            $now = date($this->dateFormat);
            $attributes['created_at'] = $now;
            $attributes['updated_at'] = $now;
        }
        
        try {
            $id = $this->db->insert($this->table, $attributes);
            
            $this->attributes[$this->primaryKey] = $id;
            $this->exists = true;
            $this->syncOriginal();
            
            return true;
            
        } catch (\Exception $e) {
            log_error("Model insert failed", [
                'model' => get_class($this),
                'table' => $this->table,
                'attributes' => $attributes,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Perform update operation
     */
    protected function performUpdate(): bool
    {
        $changes = $this->getChanges();
        if (empty($changes)) {
            return true; // No changes to save
        }
        
        if ($this->timestamps) {
            $changes['updated_at'] = date($this->dateFormat);
        }
        
        try {
            $affected = $this->db->update(
                $this->table,
                $changes,
                [$this->primaryKey => $this->attributes[$this->primaryKey]]
            );
            
            if ($affected > 0) {
                $this->syncOriginal();
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            log_error("Model update failed", [
                'model' => get_class($this),
                'table' => $this->table,
                'changes' => $changes,
                'id' => $this->attributes[$this->primaryKey] ?? null,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Delete model from database
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }
        
        try {
            $affected = $this->db->delete(
                $this->table,
                [$this->primaryKey => $this->attributes[$this->primaryKey]]
            );
            
            if ($affected > 0) {
                $this->exists = false;
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            log_error("Model delete failed", [
                'model' => get_class($this),
                'table' => $this->table,
                'id' => $this->attributes[$this->primaryKey] ?? null,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get changed attributes
     */
    public function getChanges(): array
    {
        $changes = [];
        
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $changes[$key] = $value;
            }
        }
        
        return $changes;
    }
    
    /**
     * Sync original attributes
     */
    protected function syncOriginal(): void
    {
        $this->original = $this->attributes;
    }
    
    /**
     * Create new model instance
     */
    public static function create(array $attributes = []): ?self
    {
        $model = new static($attributes);
        
        if ($model->save()) {
            return $model;
        }
        
        return null;
    }
    
    /**
     * Find model by primary key
     */
    public static function find($id): ?self
    {
        $model = new static();
        
        $result = $model->db->fetch(
            "SELECT * FROM {$model->table} WHERE {$model->primaryKey} = ?",
            [$id]
        );
        
        if ($result) {
            return static::newFromBuilder($result);
        }
        
        return null;
    }
    
    /**
     * Find model by primary key or fail
     */
    public static function findOrFail($id): self
    {
        $model = static::find($id);
        
        if (!$model) {
            throw new \Exception("Model not found with ID: {$id}");
        }
        
        return $model;
    }
    
    /**
     * Find first model matching conditions
     */
    public static function where(string $column, $operator, $value = null): QueryBuilder
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $model = new static();
        return (new QueryBuilder($model))->where($column, $operator, $value);
    }
    
    /**
     * Get all models
     */
    public static function all(): array
    {
        $model = new static();
        
        $results = $model->db->fetchAll("SELECT * FROM {$model->table}");
        
        return array_map(function ($result) {
            return static::newFromBuilder($result);
        }, $results);
    }
    
    /**
     * Count models
     */
    public static function count(): int
    {
        $model = new static();
        return (int) $model->db->fetchColumn("SELECT COUNT(*) FROM {$model->table}");
    }
    
    /**
     * Create model instance from database result
     */
    public static function newFromBuilder(array $attributes): self
    {
        $model = new static();
        $model->attributes = $attributes;
        $model->original = $attributes;
        $model->exists = true;
        
        return $model;
    }
    
    /**
     * Convert model to array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
    
    /**
     * Convert model to JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
    
    /**
     * Get table name
     */
    public function getTable(): string
    {
        return $this->table;
    }
    
    /**
     * Get primary key
     */
    public function getKeyName(): string
    {
        return $this->primaryKey;
    }
    
    /**
     * Get primary key value
     */
    public function getKey()
    {
        return $this->getAttribute($this->primaryKey);
    }
    
    /**
     * Insert a new record with data
     */
    public function insert(array $data): ?int
    {
        try {
            if ($this->timestamps) {
                $data['created_at'] = date($this->dateFormat);
                $data['updated_at'] = date($this->dateFormat);
            }
            
            return $this->db->insert($this->table, $data);
        } catch (\Exception $e) {
            log_error("Model insert failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update model by ID
     */
    public function update($id, array $data): bool
    {
        try {
            if ($this->timestamps) {
                $data['updated_at'] = date($this->dateFormat);
            }
            
            $affected = $this->db->update($this->table, $data, [$this->primaryKey => $id]);

            if ($affected > 0) {
                return true;
            }

            return $this->findRecord($id) !== null;
        } catch (\Exception $e) {
            log_error("Model update failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find record by ID (returns array)
     */
    public function findRecord($id): ?array
    {
        try {
            return $this->db->fetch("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]);
        } catch (\Exception $e) {
            log_error("Model findRecord failed: " . $e->getMessage());
            return null;
        }
    }
}

/**
 * Simple Query Builder for Model
 */
class QueryBuilder
{
    protected $model;
    protected $db;
    protected $wheres = [];
    protected $orders = [];
    protected $limit;
    protected $offset;
    
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->db = Database::getInstance();
    }
    
    public function where(string $column, string $operator, $value): self
    {
        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'AND'
        ];
        
        return $this;
    }
    
    public function orWhere(string $column, string $operator, $value): self
    {
        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'OR'
        ];
        
        return $this;
    }
    
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtoupper($direction)
        ];
        
        return $this;
    }
    
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }
    
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }
    
    public function first(): ?Model
    {
        $this->limit(1);
        $results = $this->get();
        
        return $results[0] ?? null;
    }
    
    public function get(): array
    {
        $sql = "SELECT * FROM {$this->model->getTable()}";
        $params = [];
        
        // Build WHERE clause
        if (!empty($this->wheres)) {
            $whereClauses = [];
            foreach ($this->wheres as $index => $where) {
                $paramKey = "param_{$index}";
                $boolean = $index === 0 ? '' : " {$where['boolean']} ";
                $whereClauses[] = $boolean . "{$where['column']} {$where['operator']} :{$paramKey}";
                $params[$paramKey] = $where['value'];
            }
            $sql .= " WHERE " . implode('', $whereClauses);
        }
        
        // Build ORDER BY clause
        if (!empty($this->orders)) {
            $orderClauses = [];
            foreach ($this->orders as $order) {
                $orderClauses[] = "{$order['column']} {$order['direction']}";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        }
        
        // Add LIMIT and OFFSET
        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
            if ($this->offset) {
                $sql .= " OFFSET {$this->offset}";
            }
        }
        
        $results = $this->db->fetchAll($sql, $params);
        
        return array_map(function ($result) {
            return $this->model::newFromBuilder($result);
        }, $results);
    }
    
    public function count(): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->model->getTable()}";
        $params = [];
        
        // Build WHERE clause
        if (!empty($this->wheres)) {
            $whereClauses = [];
            foreach ($this->wheres as $index => $where) {
                $paramKey = "param_{$index}";
                $boolean = $index === 0 ? '' : " {$where['boolean']} ";
                $whereClauses[] = $boolean . "{$where['column']} {$where['operator']} :{$paramKey}";
                $params[$paramKey] = $where['value'];
            }
            $sql .= " WHERE " . implode('', $whereClauses);
        }
        
        return (int) $this->db->fetchColumn($sql, $params);
    }
    
    public function delete(): int
    {
        $sql = "DELETE FROM {$this->model->getTable()}";
        $params = [];
        
        // Build WHERE clause
        if (!empty($this->wheres)) {
            $whereClauses = [];
            foreach ($this->wheres as $index => $where) {
                $paramKey = "param_{$index}";
                $boolean = $index === 0 ? '' : " {$where['boolean']} ";
                $whereClauses[] = $boolean . "{$where['column']} {$where['operator']} :{$paramKey}";
                $params[$paramKey] = $where['value'];
            }
            $sql .= " WHERE " . implode('', $whereClauses);
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount();
    }
}