<?php
namespace App\Models;

use App\Core\Model;

/**
 * IndividualResponsibility Model
 * Manages individual responsibilities for each position (5 per position)
 * ABO-WBO Management System - Responsibility Management
 */
class IndividualResponsibility extends Model
{
    protected $table = 'individual_responsibilities';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'position_key', 'category', 'name_en', 'name_om', 'description_en', 'description_om',
        'sort_order', 'status'
    ];

    // Individual responsibility categories (5 core areas per position)
    const CATEGORIES = [
        'gabaasa' => [
            'name_en' => 'Reporting & Documentation',
            'name_om' => 'Gabaasa fi Galmee'
        ],
        'gamaaggama' => [
            'name_en' => 'Evaluation & Assessment',
            'name_om' => 'Gamaaggama fi Madaallii'
        ],
        'karoora' => [
            'name_en' => 'Planning & Strategic Development',
            'name_om' => 'Karoora fi Misooma Tarsiimoo'
        ],
        'projektoota' => [
            'name_en' => 'Projects & Initiatives',
            'name_om' => 'Pirojektii fi Jalqabni'
        ],
        'qaboo_yaii' => [
            'name_en' => 'Meetings Management',
            'name_om' => 'Bulchiinsa Qaboo Ya\'ii'
        ]
    ];

    /**
     * Get all individual responsibilities with filters
     */
    public function getWithFilters(array $filters = []): array
    {
        $conditions = [];
        $params = [];
        
        // Position filter
        if (!empty($filters['position_key'])) {
            $conditions[] = "position_key = ?";
            $params[] = $filters['position_key'];
        }
        
        // Category filter
        if (!empty($filters['category'])) {
            $conditions[] = "category = ?";
            $params[] = $filters['category'];
        }
        
        // Status filter
        if (!empty($filters['status'])) {
            $conditions[] = "status = ?";
            $params[] = $filters['status'];
        } else {
            $conditions[] = "status = 'active'";
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $orderBy = "ORDER BY position_key ASC, sort_order ASC, category ASC";
        
        $query = "SELECT * FROM {$this->table} {$whereClause} {$orderBy}";
        
        return $this->db->fetchAll($query, $params);
    }

    /**
     * Get responsibilities by position
     */
    public function getByPosition(string $positionKey): array
    {
        return $this->getWithFilters(['position_key' => $positionKey]);
    }

    /**
     * Get responsibilities by category
     */
    public function getByCategory(string $category): array  
    {
        return $this->getWithFilters(['category' => $category]);
    }

    /**
     * Get all responsibilities grouped by position
     */
    public function getAllGroupedByPosition(): array
    {
        $query = "
            SELECT ir.*, p.name_en as position_name_en, p.name_om as position_name_om
            FROM {$this->table} ir
            LEFT JOIN positions p ON ir.position_key = p.key_name
            WHERE ir.status = 'active' AND p.status = 'active'
            ORDER BY p.sort_order ASC, ir.sort_order ASC
        ";
        
        $responsibilities = $this->db->fetchAll($query);
        
        // Group by position
        $grouped = [];
        foreach ($responsibilities as $responsibility) {
            $positionKey = $responsibility['position_key'];
            if (!isset($grouped[$positionKey])) {
                $grouped[$positionKey] = [
                    'position' => [
                        'key' => $positionKey,
                        'name_en' => $responsibility['position_name_en'],
                        'name_om' => $responsibility['position_name_om']
                    ],
                    'responsibilities' => []
                ];
            }
            $grouped[$positionKey]['responsibilities'][] = $responsibility;
        }
        
        return $grouped;
    }

    /**
     * Validate that all 5 categories exist for a position
     */
    public function validatePositionResponsibilities(string $positionKey): array
    {
        $existing = $this->getByPosition($positionKey);
        $existingCategories = array_column($existing, 'category');
        $requiredCategories = array_keys(self::CATEGORIES);
        
        $missing = array_diff($requiredCategories, $existingCategories);
        $extra = array_diff($existingCategories, $requiredCategories);
        
        return [
            'valid' => empty($missing) && empty($extra) && count($existing) === 5,
            'missing' => $missing,
            'extra' => $extra,
            'count' => count($existing),
            'required_count' => 5
        ];
    }

    /**
     * Create missing responsibilities for a position
     */
    public function createMissingForPosition(string $positionKey): array
    {
        $validation = $this->validatePositionResponsibilities($positionKey);
        $created = [];
        
        if (!empty($validation['missing'])) {
            $position = $this->db->fetch("SELECT * FROM positions WHERE key_name = ?", [$positionKey]);
            if (!$position) {
                throw new \Exception("Position not found: {$positionKey}");
            }
            
            $sortOrder = 1;
            foreach ($validation['missing'] as $category) {
                $categoryInfo = self::CATEGORIES[$category];
                
                $data = [
                    'position_key' => $positionKey,
                    'category' => $category,
                    'name_en' => $categoryInfo['name_en'] . ' (' . $position['name_en'] . ')',
                    'name_om' => $categoryInfo['name_om'] . ' (' . $position['name_om'] . ')',
                    'description_en' => 'Manage ' . strtolower($categoryInfo['name_en']) . ' for ' . $position['name_en'] . ' position',
                    'description_om' => $categoryInfo['name_om'] . ' ' . $position['name_om'] . ' sadarkaa irratti',
                    'sort_order' => $sortOrder++,
                    'status' => 'active'
                ];
                
                $id = $this->create($data);
                $created[] = array_merge($data, ['id' => $id]);
            }
        }
        
        return $created;
    }

    /**
     * Get responsibility matrix (positions vs categories)
     */
    public function getResponsibilityMatrix(): array
    {
        $query = "
            SELECT 
                p.key_name as position_key,
                p.name_en as position_name_en,
                p.name_om as position_name_om,
                p.sort_order as position_order,
                ir.category,
                ir.name_en as responsibility_name_en,
                ir.name_om as responsibility_name_om,
                ir.sort_order as responsibility_order
            FROM positions p
            LEFT JOIN {$this->table} ir ON p.key_name = ir.position_key AND ir.status = 'active'
            WHERE p.status = 'active'
            ORDER BY p.sort_order ASC, ir.sort_order ASC
        ";
        
        $results = $this->db->fetchAll($query);
        
        // Build matrix
        $matrix = [];
        foreach ($results as $row) {
            $positionKey = $row['position_key'];
            if (!isset($matrix[$positionKey])) {
                $matrix[$positionKey] = [
                    'position' => [
                        'key' => $positionKey,
                        'name_en' => $row['position_name_en'],
                        'name_om' => $row['position_name_om'],
                        'order' => $row['position_order']
                    ],
                    'responsibilities' => []
                ];
            }
            
            if ($row['category']) {
                $matrix[$positionKey]['responsibilities'][$row['category']] = [
                    'category' => $row['category'],
                    'name_en' => $row['responsibility_name_en'],
                    'name_om' => $row['responsibility_name_om'],
                    'order' => $row['responsibility_order']
                ];
            }
        }
        
        return $matrix;
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        $query = "
            SELECT 
                COUNT(*) as total_responsibilities,
                COUNT(DISTINCT position_key) as positions_with_responsibilities,
                COUNT(DISTINCT category) as categories_covered,
                AVG(CASE WHEN status = 'active' THEN 1 ELSE 0 END) * 100 as active_percentage
            FROM {$this->table}
        ";
        
        $stats = $this->db->fetch($query);
        
        // Calculate expected totals
        $totalPositions = $this->db->fetchColumn("SELECT COUNT(*) FROM positions WHERE status = 'active'");
        $expectedTotal = $totalPositions * 5; // 5 responsibilities per position
        
        $stats['expected_total'] = $expectedTotal;
        $stats['completion_percentage'] = $expectedTotal > 0 ? ($stats['total_responsibilities'] / $expectedTotal) * 100 : 0;
        
        return $stats;
    }
}