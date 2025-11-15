<?php
namespace App\Models;

use App\Core\Model;

/**
 * SharedResponsibility Model
 * Manages shared responsibilities for executive teams (5 per level)
 * ABO-WBO Management System - Shared Team Responsibility Management
 */
class SharedResponsibility extends Model
{
    protected $table = 'shared_responsibilities';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'level_scope', 'category', 'name_en', 'name_om', 'description_en', 'description_om',
        'sort_order', 'status'
    ];

    // Shared responsibility categories (5 core areas for team collaboration)
    const CATEGORIES = [
        'gabaasa' => [
            'name_en' => 'Collective Reporting & Documentation',
            'name_om' => 'Gabaasa fi Galmee Waliigalaa'
        ],
        'gamaaggama' => [
            'name_en' => 'Team Evaluation & Assessment',
            'name_om' => 'Gamaaggama fi Madaallii Garee'
        ],
        'karoora' => [
            'name_en' => 'Collaborative Planning & Strategic Development',
            'name_om' => 'Karoora Tumsaa fi Misooma Tarsiimoo'
        ],
        'projektoota' => [
            'name_en' => 'Joint Projects & Initiatives',
            'name_om' => 'Pirojektii fi Jalqabni Waliigalaa'
        ],
        'qaboo_yaii' => [
            'name_en' => 'Shared Meetings Management',
            'name_om' => 'Bulchiinsa Qaboo Ya\'ii Qoodamaa'
        ]
    ];

    // Level scopes
    const LEVEL_SCOPES = [
        'global' => [
            'name_en' => 'Global Level',
            'name_om' => 'Sadarkaa Addunyaa'
        ],
        'godina' => [
            'name_en' => 'Regional Level',
            'name_om' => 'Sadarkaa Godina'
        ],
        'gamta' => [
            'name_en' => 'Country/Sub-Regional Level',
            'name_om' => 'Sadarkaa Gamta'
        ],
        'gurmu' => [
            'name_en' => 'Local Unit Level',
            'name_om' => 'Sadarkaa Gurmu'
        ],
        'all' => [
            'name_en' => 'All Levels',
            'name_om' => 'Saddarkaa Hunda'
        ]
    ];

    /**
     * Get all shared responsibilities with filters
     */
    public function getWithFilters(array $filters = []): array
    {
        $conditions = [];
        $params = [];
        
        // Level scope filter
        if (!empty($filters['level_scope'])) {
            $conditions[] = "(level_scope = ? OR level_scope = 'all')";
            $params[] = $filters['level_scope'];
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
        $orderBy = "ORDER BY level_scope ASC, sort_order ASC, category ASC";
        
        $query = "SELECT * FROM {$this->table} {$whereClause} {$orderBy}";
        
        return $this->db->fetchAll($query, $params);
    }

    /**
     * Get responsibilities by level scope
     */
    public function getByLevel(string $levelScope): array
    {
        return $this->getWithFilters(['level_scope' => $levelScope]);
    }

    /**
     * Get responsibilities by category
     */
    public function getByCategory(string $category): array  
    {
        return $this->getWithFilters(['category' => $category]);
    }

    /**
     * Get all responsibilities grouped by level
     */
    public function getAllGroupedByLevel(): array
    {
        $query = "
            SELECT *
            FROM {$this->table}
            WHERE status = 'active'
            ORDER BY CASE 
                WHEN level_scope = 'global' THEN 1
                WHEN level_scope = 'godina' THEN 2
                WHEN level_scope = 'gamta' THEN 3
                WHEN level_scope = 'gurmu' THEN 4
                WHEN level_scope = 'all' THEN 5
            END, sort_order ASC
        ";
        
        $responsibilities = $this->db->fetchAll($query);
        
        // Group by level
        $grouped = [];
        foreach ($responsibilities as $responsibility) {
            $levelScope = $responsibility['level_scope'];
            if (!isset($grouped[$levelScope])) {
                $levelInfo = self::LEVEL_SCOPES[$levelScope] ?? ['name_en' => $levelScope, 'name_om' => $levelScope];
                $grouped[$levelScope] = [
                    'level' => [
                        'scope' => $levelScope,
                        'name_en' => $levelInfo['name_en'],
                        'name_om' => $levelInfo['name_om']
                    ],
                    'responsibilities' => []
                ];
            }
            $grouped[$levelScope]['responsibilities'][] = $responsibility;
        }
        
        return $grouped;
    }

    /**
     * Validate that all 5 categories exist for a level
     */
    public function validateLevelResponsibilities(string $levelScope): array
    {
        $existing = $this->getByLevel($levelScope);
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
     * Create missing responsibilities for a level
     */
    public function createMissingForLevel(string $levelScope): array
    {
        $validation = $this->validateLevelResponsibilities($levelScope);
        $created = [];
        
        if (!empty($validation['missing'])) {
            $levelInfo = self::LEVEL_SCOPES[$levelScope] ?? ['name_en' => $levelScope, 'name_om' => $levelScope];
            
            $sortOrder = 1;
            foreach ($validation['missing'] as $category) {
                $categoryInfo = self::CATEGORIES[$category];
                
                $data = [
                    'level_scope' => $levelScope,
                    'category' => $category,
                    'name_en' => $categoryInfo['name_en'] . ' (' . $levelInfo['name_en'] . ')',
                    'name_om' => $categoryInfo['name_om'] . ' (' . $levelInfo['name_om'] . ')',
                    'description_en' => 'Collaborative ' . strtolower($categoryInfo['name_en']) . ' at ' . $levelInfo['name_en'],
                    'description_om' => $categoryInfo['name_om'] . ' ' . $levelInfo['name_om'] . ' keessatti',
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
     * Get responsibility matrix (levels vs categories)
     */
    public function getResponsibilityMatrix(): array
    {
        $query = "
            SELECT 
                level_scope,
                category,
                name_en as responsibility_name_en,
                name_om as responsibility_name_om,
                sort_order as responsibility_order
            FROM {$this->table}
            WHERE status = 'active'
            ORDER BY CASE 
                WHEN level_scope = 'global' THEN 1
                WHEN level_scope = 'godina' THEN 2
                WHEN level_scope = 'gamta' THEN 3
                WHEN level_scope = 'gurmu' THEN 4
                WHEN level_scope = 'all' THEN 5
            END, sort_order ASC
        ";
        
        $results = $this->db->fetchAll($query);
        
        // Build matrix
        $matrix = [];
        foreach ($results as $row) {
            $levelScope = $row['level_scope'];
            if (!isset($matrix[$levelScope])) {
                $levelInfo = self::LEVEL_SCOPES[$levelScope] ?? ['name_en' => $levelScope, 'name_om' => $levelScope];
                $matrix[$levelScope] = [
                    'level' => [
                        'scope' => $levelScope,
                        'name_en' => $levelInfo['name_en'],
                        'name_om' => $levelInfo['name_om']
                    ],
                    'responsibilities' => []
                ];
            }
            
            $matrix[$levelScope]['responsibilities'][$row['category']] = [
                'category' => $row['category'],
                'name_en' => $row['responsibility_name_en'],
                'name_om' => $row['responsibility_name_om'],
                'order' => $row['responsibility_order']
            ];
        }
        
        return $matrix;
    }

    /**
     * Get responsibilities applicable to a specific organizational unit
     */
    public function getForOrganizationalUnit(string $unitType, int $unitId): array
    {
        // Get responsibilities for the specific level and 'all' levels
        $responsibilities = $this->getWithFilters(['level_scope' => $unitType]);
        
        // Add metadata about the organizational unit
        $unitInfo = $this->getOrganizationalUnitInfo($unitType, $unitId);
        
        return [
            'unit' => $unitInfo,
            'responsibilities' => $responsibilities
        ];
    }

    /**
     * Get organizational unit information
     */
    private function getOrganizationalUnitInfo(string $unitType, int $unitId): array
    {
        $table = $unitType . 's'; // godinas, gamtas, gurmus
        $query = "SELECT * FROM {$table} WHERE id = ?";
        $unit = $this->db->fetch($query, [$unitId]);
        
        if (!$unit) {
            throw new \Exception("Organizational unit not found: {$unitType} #{$unitId}");
        }
        
        // Get hierarchy information
        $hierarchyPath = $this->getUnitHierarchyPath($unitType, $unitId);
        
        return [
            'id' => $unitId,
            'type' => $unitType,
            'name' => $unit['name'],
            'code' => $unit['code'],
            'hierarchy_path' => $hierarchyPath
        ];
    }

    /**
     * Get unit hierarchy path
     */
    private function getUnitHierarchyPath(string $unitType, int $unitId): array
    {
        $path = [];
        
        switch ($unitType) {
            case 'gurmu':
                $gurmu = $this->db->fetch("SELECT * FROM gurmus WHERE id = ?", [$unitId]);
                if ($gurmu) {
                    $path[] = ['type' => 'gurmu', 'id' => $gurmu['id'], 'name' => $gurmu['name']];
                    $gamta = $this->db->fetch("SELECT * FROM gamtas WHERE id = ?", [$gurmu['gamta_id']]);
                    if ($gamta) {
                        $path[] = ['type' => 'gamta', 'id' => $gamta['id'], 'name' => $gamta['name']];
                        $godina = $this->db->fetch("SELECT * FROM godinas WHERE id = ?", [$gamta['godina_id']]);
                        if ($godina) {
                            $path[] = ['type' => 'godina', 'id' => $godina['id'], 'name' => $godina['name']];
                        }
                    }
                }
                break;
                
            case 'gamta':
                $gamta = $this->db->fetch("SELECT * FROM gamtas WHERE id = ?", [$unitId]);
                if ($gamta) {
                    $path[] = ['type' => 'gamta', 'id' => $gamta['id'], 'name' => $gamta['name']];
                    $godina = $this->db->fetch("SELECT * FROM godinas WHERE id = ?", [$gamta['godina_id']]);
                    if ($godina) {
                        $path[] = ['type' => 'godina', 'id' => $godina['id'], 'name' => $godina['name']];
                    }
                }
                break;
                
            case 'godina':
                $godina = $this->db->fetch("SELECT * FROM godinas WHERE id = ?", [$unitId]);
                if ($godina) {
                    $path[] = ['type' => 'godina', 'id' => $godina['id'], 'name' => $godina['name']];
                }
                break;
        }
        
        // Add global level
        $path[] = ['type' => 'global', 'id' => 1, 'name' => 'Global'];
        
        return array_reverse($path); // Global -> Godina -> Gamta -> Gurmu
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        $query = "
            SELECT 
                COUNT(*) as total_responsibilities,
                COUNT(DISTINCT level_scope) as levels_covered,
                COUNT(DISTINCT category) as categories_covered,
                AVG(CASE WHEN status = 'active' THEN 1 ELSE 0 END) * 100 as active_percentage
            FROM {$this->table}
        ";
        
        $stats = $this->db->fetch($query);
        
        // Calculate expected totals (5 categories × levels)
        $totalLevels = count(self::LEVEL_SCOPES);
        $expectedTotal = $totalLevels * 5; // 5 responsibilities per level
        
        $stats['expected_total'] = $expectedTotal;
        $stats['completion_percentage'] = $expectedTotal > 0 ? ($stats['total_responsibilities'] / $expectedTotal) * 100 : 0;
        
        return $stats;
    }
}