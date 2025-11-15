<?php
namespace App\Models;

use App\Core\Model;
use Exception;

/**
 * Responsibility Model
 * Manages position responsibilities and shared tasks
 * ABO-WBO Management System - Responsibility Management
 */
class Responsibility extends Model
{
    protected $table = 'responsibilities';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'key_name', 'name_en', 'name_om', 'description_en', 'description_om',
        'responsibility_type', 'category', 'level_scope', 'position_scope',
        'is_shared', 'priority', 'frequency', 'metadata', 'status'
    ];
    
    protected $casts = [
        'metadata' => 'json',
        'is_shared' => 'boolean',
        'priority' => 'integer',
        'frequency' => 'integer'
    ];
    
    // Shared responsibilities (5 core areas applied to ALL positions)
    const SHARED_RESPONSIBILITIES = [
        'qaboo_yaii' => [
            'name_en' => 'Meetings Management',
            'name_om' => 'Qaboo Ya\'ii',
            'description_en' => 'Organize, coordinate, and manage meetings at respective level',
            'description_om' => 'Ya\'iiwwan qabuu, qindoomuu fi bulchuu'
        ],
        'karoora' => [
            'name_en' => 'Planning & Strategic Development',
            'name_om' => 'Karoora',
            'description_en' => 'Develop strategic plans and coordinate implementation',
            'description_om' => 'Karoora istraatijiikaa qopheessuu fi hojiirra oolchuu'
        ],
        'gabaasa' => [
            'name_en' => 'Reporting & Documentation',
            'name_om' => 'Gabaasa',
            'description_en' => 'Prepare reports and maintain proper documentation',
            'description_om' => 'Gabaasa qopheessuu fi galmee sirrii eeguu'
        ],
        'projectoota' => [
            'name_en' => 'Projects & Initiatives',
            'name_om' => 'Projectoota',
            'description_en' => 'Lead and participate in organizational projects',
            'description_om' => 'Projectoota jaarmayaa hoogganii fi keessatti hirmaachuu'
        ],
        'gamaggama' => [
            'name_en' => 'Evaluation & Assessment',
            'name_om' => 'Gamaggama',
            'description_en' => 'Evaluate performance and assess organizational effectiveness',
            'description_om' => 'Raawwii madaaluu fi bu\'uura jaarmayaa qoruu'
        ]
    ];
    
    // Individual position responsibilities (specific to each position)
    const POSITION_RESPONSIBILITIES = [
        'dura_taa' => [
            'name_en' => 'Chairperson/President',
            'name_om' => 'Dura Ta\'aa',
            'responsibilities' => [
                'leadership_vision' => ['name_en' => 'Leadership & Vision', 'name_om' => 'Hogganummaa fi Muldhata'],
                'strategic_direction' => ['name_en' => 'Strategic Direction', 'name_om' => 'Kallattii Istraatijiikaa'],
                'representation' => ['name_en' => 'Organizational Representation', 'name_om' => 'Bakka Bu\'insa Jaarmayaa'],
                'coordination' => ['name_en' => 'Inter-level Coordination', 'name_om' => 'Qindoomina Sadarkaa-gidduu'],
                'decision_making' => ['name_en' => 'Executive Decision Making', 'name_om' => 'Murtii Raawwataa']
            ]
        ],
        'barreessaa' => [
            'name_en' => 'Secretary',
            'name_om' => 'Barreessaa',
            'responsibilities' => [
                'record_keeping' => ['name_en' => 'Record Keeping', 'name_om' => 'Galmee Eeguu'],
                'communication' => ['name_en' => 'Internal Communication', 'name_om' => 'Quunnamtii Keessaa'],
                'documentation' => ['name_en' => 'Meeting Documentation', 'name_om' => 'Galmee Ya\'ii'],
                'correspondence' => ['name_en' => 'Official Correspondence', 'name_om' => 'Xalayaa Ofiisaa'],
                'scheduling' => ['name_en' => 'Scheduling & Coordination', 'name_om' => 'Saganteessuu fi Qindoomina']
            ]
        ],
        'ijaarsaa_siyaasa' => [
            'name_en' => 'Organization & Political Affairs',
            'name_om' => 'Ijaarsaa fi Siyaasa',
            'responsibilities' => [
                'organizational_development' => ['name_en' => 'Organizational Development', 'name_om' => 'Guddinaa Jaarmayaa'],
                'policy_development' => ['name_en' => 'Policy Development', 'name_om' => 'Imaammata Qopheessuu'],
                'member_development' => ['name_en' => 'Member Development', 'name_om' => 'Guddinaa Miseensotaa'],
                'training_coordination' => ['name_en' => 'Training Coordination', 'name_om' => 'Qindoomina Leenjii'],
                'political_engagement' => ['name_en' => 'Political Engagement', 'name_om' => 'Hirmaannaa Siyaasaa']
            ]
        ],
        'dinagdee' => [
            'name_en' => 'Finance & Economic Affairs',
            'name_om' => 'Dinagdee',
            'responsibilities' => [
                'financial_management' => ['name_en' => 'Financial Management', 'name_om' => 'Bulchiinsa Maallaqaa'],
                'budget_planning' => ['name_en' => 'Budget Planning', 'name_om' => 'Karoora Baajataa'],
                'donation_management' => ['name_en' => 'Donation Management', 'name_om' => 'Bulchiinsa Kennaawwanii'],
                'financial_reporting' => ['name_en' => 'Financial Reporting', 'name_om' => 'Gabaasa Maallaqaa'],
                'audit_coordination' => ['name_en' => 'Audit Coordination', 'name_om' => 'Qindoomina Tohannoo']
            ]
        ],
        'mediyaa_quunnamtii' => [
            'name_en' => 'Media & Communications',
            'name_om' => 'Mediyaa fi Sab-Quunnamtii',
            'responsibilities' => [
                'media_strategy' => ['name_en' => 'Media Strategy', 'name_om' => 'Tooftaa Mediyaa'],
                'content_creation' => ['name_en' => 'Content Creation', 'name_om' => 'Qabiyyee Uumuu'],
                'public_relations' => ['name_en' => 'Public Relations', 'name_om' => 'Hariiroo Sab-Hawaasaa'],
                'digital_presence' => ['name_en' => 'Digital Presence', 'name_om' => 'Argama Dijitaalaa'],
                'communication_coordination' => ['name_en' => 'Communication Coordination', 'name_om' => 'Qindoomina Quunnamtii']
            ]
        ],
        'diplomaasii_hawaasummaa' => [
            'name_en' => 'Public Diplomacy & Community Relations',
            'name_om' => 'Diploomaasii Hawaasummaa',
            'responsibilities' => [
                'community_engagement' => ['name_en' => 'Community Engagement', 'name_om' => 'Hirmaannaa Hawaasaa'],
                'external_relations' => ['name_en' => 'External Relations', 'name_om' => 'Hariiroo Alaa'],
                'partnership_development' => ['name_en' => 'Partnership Development', 'name_om' => 'Guddinaa Tumsa'],
                'diplomatic_coordination' => ['name_en' => 'Diplomatic Coordination', 'name_om' => 'Qindoomina Diploomaasii'],
                'stakeholder_management' => ['name_en' => 'Stakeholder Management', 'name_om' => 'Bulchiinsa Qooda-Qabdootaa']
            ]
        ],
        'tohannoo_keessaa' => [
            'name_en' => 'Internal Audit & Oversight',
            'name_om' => 'Tohannoo Keessaa',
            'responsibilities' => [
                'audit_execution' => ['name_en' => 'Audit Execution', 'name_om' => 'Raawwii Tohannoo'],
                'compliance_monitoring' => ['name_en' => 'Compliance Monitoring', 'name_om' => 'Hordoffii Ajajamuu'],
                'risk_assessment' => ['name_en' => 'Risk Assessment', 'name_om' => 'Madaallii Balaa'],
                'investigation_oversight' => ['name_en' => 'Investigation Oversight', 'name_om' => 'Hordoffii Qorannoo'],
                'transparency_assurance' => ['name_en' => 'Transparency Assurance', 'name_om' => 'Mirkaneessa Iftoomaa']
            ]
        ]
    ];
    
    /**
     * Get all responsibilities with filters
     */
    public function getWithFilters(array $filters = []): array
    {
        $conditions = [];
        $params = [];
        
        // Responsibility type filter
        if (!empty($filters['responsibility_type'])) {
            $conditions[] = "responsibility_type = ?";
            $params[] = $filters['responsibility_type'];
        }
        
        // Category filter
        if (!empty($filters['category'])) {
            $conditions[] = "category = ?";
            $params[] = $filters['category'];
        }
        
        // Level scope filter
        if (!empty($filters['level_scope'])) {
            $conditions[] = "(level_scope = ? OR level_scope = 'all')";
            $params[] = $filters['level_scope'];
        }
        
        // Position scope filter
        if (!empty($filters['position_scope'])) {
            $conditions[] = "(position_scope = ? OR position_scope = 'all')";
            $params[] = $filters['position_scope'];
        }
        
        // Shared responsibilities filter
        if (isset($filters['is_shared'])) {
            $conditions[] = "is_shared = ?";
            $params[] = $filters['is_shared'] ? 1 : 0;
        }
        
        // Status filter
        if (!empty($filters['status'])) {
            $conditions[] = "status = ?";
            $params[] = $filters['status'];
        } else {
            $conditions[] = "status = 'active'";
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $orderBy = "ORDER BY is_shared DESC, priority ASC, name_en ASC";
        
        $query = "SELECT * FROM {$this->table} {$whereClause} {$orderBy}";
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get shared responsibilities (5 core areas)
     */
    public function getSharedResponsibilities(string $levelScope = 'all'): array
    {
        return $this->getWithFilters([
            'is_shared' => true,
            'level_scope' => $levelScope
        ]);
    }
    
    /**
     * Get position-specific responsibilities
     */
    public function getPositionResponsibilities(string $positionKey, string $levelScope = 'all'): array
    {
        return $this->getWithFilters([
            'is_shared' => false,
            'position_scope' => $positionKey,
            'level_scope' => $levelScope
        ]);
    }
    
    /**
     * Get all responsibilities for a position (individual + shared)
     */
    public function getAllPositionResponsibilities(string $positionKey, string $levelScope = 'all'): array
    {
        $individual = $this->getPositionResponsibilities($positionKey, $levelScope);
        $shared = $this->getSharedResponsibilities($levelScope);
        
        return [
            'individual' => $individual,
            'shared' => $shared,
            'total_count' => count($individual) + count($shared)
        ];
    }
    
    /**
     * Create responsibility
     */
    public function createResponsibility(array $data): int
    {
        // Validate required fields
        $required = ['key_name', 'name_en', 'name_om', 'responsibility_type'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '{$field}' is required");
            }
        }
        
        // Set defaults
        $data['category'] = $data['category'] ?? 'general';
        $data['level_scope'] = $data['level_scope'] ?? 'all';
        $data['position_scope'] = $data['position_scope'] ?? 'all';
        $data['is_shared'] = $data['is_shared'] ?? false;
        $data['priority'] = $data['priority'] ?? 1;
        $data['frequency'] = $data['frequency'] ?? 30; // days
        $data['status'] = $data['status'] ?? 'active';
        
        return $this->create($data);
    }
    
    /**
     * Initialize default responsibilities from constants
     */
    public function initializeDefaultResponsibilities(): array
    {
        $created = [];
        
        try {
            // Create shared responsibilities (5 core areas)
            foreach (self::SHARED_RESPONSIBILITIES as $key => $responsibility) {
                $data = [
                    'key_name' => $key,
                    'name_en' => $responsibility['name_en'],
                    'name_om' => $responsibility['name_om'],
                    'description_en' => $responsibility['description_en'],
                    'description_om' => $responsibility['description_om'],
                    'responsibility_type' => 'shared',
                    'category' => 'core',
                    'level_scope' => 'all',
                    'position_scope' => 'all',
                    'is_shared' => true,
                    'priority' => 1,
                    'frequency' => 30
                ];
                
                // Check if already exists
                if (!$this->responsibilityExists($key)) {
                    $id = $this->createResponsibility($data);
                    $created[] = ['type' => 'shared', 'key' => $key, 'id' => $id];
                }
            }
            
            // Create position-specific responsibilities
            foreach (self::POSITION_RESPONSIBILITIES as $positionKey => $position) {
                foreach ($position['responsibilities'] as $respKey => $responsibility) {
                    $data = [
                        'key_name' => $positionKey . '_' . $respKey,
                        'name_en' => $responsibility['name_en'],
                        'name_om' => $responsibility['name_om'],
                        'description_en' => $responsibility['name_en'] . ' responsibilities for ' . $position['name_en'],
                        'description_om' => $responsibility['name_om'] . ' itti gaafatamummaa ' . $position['name_om'],
                        'responsibility_type' => 'individual',
                        'category' => 'position_specific',
                        'level_scope' => 'all',
                        'position_scope' => $positionKey,
                        'is_shared' => false,
                        'priority' => 2,
                        'frequency' => 30
                    ];
                    
                    // Check if already exists
                    if (!$this->responsibilityExists($positionKey . '_' . $respKey)) {
                        $id = $this->createResponsibility($data);
                        $created[] = ['type' => 'individual', 'position' => $positionKey, 'key' => $respKey, 'id' => $id];
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log("Error initializing responsibilities: " . $e->getMessage());
            throw $e;
        }
        
        return $created;
    }
    
    /**
     * Check if responsibility exists
     */
    public function responsibilityExists(string $keyName): bool
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE key_name = ?";
        $result = $this->db->fetch($query, [$keyName]);
        return $result['count'] > 0;
    }
    
    /**
     * Get responsibility statistics
     */
    public function getResponsibilityStats(): array
    {
        $query = "
            SELECT 
                responsibility_type,
                is_shared,
                COUNT(*) as total_responsibilities,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_responsibilities
            FROM {$this->table}
            GROUP BY responsibility_type, is_shared
            ORDER BY is_shared DESC, responsibility_type
        ";
        
        return $this->db->fetchAll($query);
    }
    
    /**
     * Get responsibilities for organizational unit assignment
     */
    public function getResponsibilitiesForAssignment(int $positionId, string $levelScope, int $organizationalUnitId): array
    {
        // Get position details
        $positionModel = new \App\Models\Position();
        $position = $positionModel->find($positionId);
        
        if (!$position) {
            return [];
        }
        
        // Get all responsibilities for this position
        $responsibilities = $this->getAllPositionResponsibilities($position['key_name'], $levelScope);
        
        // Add assignment context
        foreach ($responsibilities['individual'] as &$resp) {
            $resp['assignment_context'] = [
                'position_id' => $positionId,
                'level_scope' => $levelScope,
                'organizational_unit_id' => $organizationalUnitId,
                'responsibility_type' => 'individual'
            ];
        }
        
        foreach ($responsibilities['shared'] as &$resp) {
            $resp['assignment_context'] = [
                'position_id' => $positionId,
                'level_scope' => $levelScope,
                'organizational_unit_id' => $organizationalUnitId,
                'responsibility_type' => 'shared'
            ];
        }
        
        return $responsibilities;
    }
    
    /**
     * Log responsibility activity
     */
    public function logActivity(int $responsibilityId, string $action, array $details = [], ?int $userId = null): void
    {
        $logData = [
            'table_name' => $this->table,
            'record_id' => $responsibilityId,
            'action' => $action,
            'details' => json_encode($details),
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->query(
            "INSERT INTO activity_logs (table_name, record_id, action, details, user_id, ip_address, user_agent, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            array_values($logData)
        );
    }
}