<?php
namespace App\Models;

use App\Core\Model;

/**
 * Global Model
 * Represents the highest tier in the ABO-WBO hierarchy
 * Global → Godina → Gamta → Gurmu
 */
class GlobalModel extends Model
{
    protected $table = 'globals';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name', 'code', 'description', 'headquarters_address',
        'contact_email', 'contact_phone', 'website', 'established_date',
        'mission_statement', 'vision_statement', 'status',
        'fiscal_year_start', 'fiscal_year_end', 'metadata'
    ];
    
    protected $dates = [
        'established_date', 'fiscal_year_start', 'fiscal_year_end', 
        'created_at', 'updated_at'
    ];
    
    protected $casts = [
        'metadata' => 'json'
    ];
    
    /**
     * Get all Godinas under this Global organization
     */
    public function godinas()
    {
        return $this->hasMany('App\Models\Godina', 'global_id');
    }
    
    /**
     * Get all Gamtas under this Global organization (through Godinas)
     */
    public function gamtas()
    {
        $sql = "SELECT g.* FROM gamtas g 
                INNER JOIN godinas gd ON g.godina_id = gd.id 
                WHERE gd.global_id = ? AND g.status = 'active'
                ORDER BY gd.name, g.name";
        return $this->query($sql, [$this->id]);
    }
    
    /**
     * Get all Gurmus under this Global organization (through hierarchy)
     */
    public function gurmus()
    {
        $sql = "SELECT gu.* FROM gurmus gu
                INNER JOIN gamtas ga ON gu.gamta_id = ga.id
                INNER JOIN godinas gd ON ga.godina_id = gd.id
                WHERE gd.global_id = ? AND gu.status = 'active'
                ORDER BY gd.name, ga.name, gu.name";
        return $this->query($sql, [$this->id]);
    }
    
    /**
     * Get all users in this Global organization
     */
    public function users()
    {
        $sql = "SELECT u.* FROM users u
                LEFT JOIN user_assignments ua ON u.id = ua.user_id
                LEFT JOIN gurmus gu ON ua.gurmu_id = gu.id
                LEFT JOIN gamtas ga ON (ua.gamta_id = ga.id OR gu.gamta_id = ga.id)
                LEFT JOIN godinas gd ON (ua.godina_id = gd.id OR ga.godina_id = gd.id)
                WHERE (gd.global_id = ? OR ua.global_id = ?) AND u.status = 'active'
                GROUP BY u.id
                ORDER BY u.first_name, u.last_name";
        return $this->query($sql, [$this->id, $this->id]);
    }
    
    /**
     * Get comprehensive statistics for this Global organization
     */
    public function getStatistics()
    {
        $stats = [];
        
        // Basic hierarchy counts
        $stats['total_godinas'] = $this->countRelated('godinas', 'global_id');
        $stats['total_gamtas'] = $this->countGamtas();
        $stats['total_gurmus'] = $this->countGurmus();
        $stats['total_users'] = $this->countUsers();
        
        // Position statistics
        $stats['positions'] = $this->getPositionStatistics();
        
        // Activity statistics
        $stats['active_tasks'] = $this->countActiveTasks();
        $stats['completed_tasks'] = $this->countCompletedTasks();
        $stats['upcoming_meetings'] = $this->countUpcomingMeetings();
        $stats['recent_donations'] = $this->getRecentDonations();
        
        // Financial summary
        $stats['financial'] = $this->getFinancialSummary();
        
        return $stats;
    }
    
    /**
     * Count Gamtas under this Global organization
     */
    private function countGamtas()
    {
        $sql = "SELECT COUNT(g.id) as count FROM gamtas g 
                INNER JOIN godinas gd ON g.godina_id = gd.id 
                WHERE gd.global_id = ? AND g.status = 'active'";
        $result = $this->query($sql, [$this->id]);
        return $result[0]['count'] ?? 0;
    }
    
    /**
     * Count Gurmus under this Global organization
     */
    private function countGurmus()
    {
        $sql = "SELECT COUNT(gu.id) as count FROM gurmus gu
                INNER JOIN gamtas ga ON gu.gamta_id = ga.id
                INNER JOIN godinas gd ON ga.godina_id = gd.id
                WHERE gd.global_id = ? AND gu.status = 'active'";
        $result = $this->query($sql, [$this->id]);
        return $result[0]['count'] ?? 0;
    }
    
    /**
     * Count users in this Global organization
     */
    private function countUsers()
    {
        $sql = "SELECT COUNT(DISTINCT u.id) as count FROM users u
                LEFT JOIN user_assignments ua ON u.id = ua.user_id
                LEFT JOIN gurmus gu ON ua.gurmu_id = gu.id
                LEFT JOIN gamtas ga ON (ua.gamta_id = ga.id OR gu.gamta_id = ga.id)
                LEFT JOIN godinas gd ON (ua.godina_id = gd.id OR ga.godina_id = gd.id)
                WHERE (gd.global_id = ? OR ua.global_id = ?) AND u.status = 'active'";
        $result = $this->query($sql, [$this->id, $this->id]);
        return $result[0]['count'] ?? 0;
    }
    
    /**
     * Get position statistics across the organization
     */
    private function getPositionStatistics()
    {
        $sql = "SELECT 
                    p.name as position_name,
                p.name as position_name,
                p.level_scope,
                COUNT(ua.id) as filled_positions,
                p.max_holders
                FROM positions p
                LEFT JOIN user_assignments ua ON p.id = ua.position_id
                LEFT JOIN users u ON ua.user_id = u.id AND u.status = 'active'
                WHERE p.status = 'active'
                GROUP BY p.id, p.name, p.level_scope, p.max_holders
                ORDER BY p.level_scope, p.name";
        return $this->query($sql);
    }
    
    /**
     * Count active tasks across the organization
     */
    private function countActiveTasks()
    {
        // This would require tasks table - placeholder for now
        return 0;
    }
    
    /**
     * Count completed tasks across the organization
     */
    private function countCompletedTasks()
    {
        // This would require tasks table - placeholder for now
        return 0;
    }
    
    /**
     * Count upcoming meetings across the organization
     */
    private function countUpcomingMeetings()
    {
        // This would require meetings table - placeholder for now
        return 0;
    }
    
    /**
     * Get recent donations summary
     */
    private function getRecentDonations()
    {
        // This would require donations table - placeholder for now
        return [
            'total_amount' => 0,
            'count' => 0,
            'last_30_days' => 0
        ];
    }
    
    /**
     * Get financial summary for the organization
     */
    private function getFinancialSummary()
    {
        return [
            'total_donations' => 0,
            'monthly_donations' => 0,
            'pending_approvals' => 0,
            'total_budget' => 0,
            'allocated_budget' => 0,
            'remaining_budget' => 0
        ];
    }
    
    /**
     * Get the default/main Global organization
     */
    public static function getDefault()
    {
        return static::where('status', 'active')
                    ->orderBy('id', 'ASC')
                    ->first() ?? static::createDefault();
    }
    
    /**
     * Create default Global organization if none exists
     */
    private static function createDefault()
    {
        return static::create([
            'name' => 'ABO-WBO Global Organization',
            'code' => 'ABO-WBO-GLOBAL',
            'description' => 'Global ABO-WBO Organization - Oromo Liberation Front Support Organization',
            'contact_email' => 'info@abo-wbo.org',
            'website' => 'https://abo-wbo.org',
            'status' => 'active'
        ]);
    }
    
    /**
     * Validation rules for Global model
     */
    public static function validationRules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:globals,code'],
            'contact_email' => ['email', 'max:255'],
            'contact_phone' => ['string', 'max:50'],
            'website' => ['url', 'max:255'],
            'status' => ['in:active,inactive,maintenance']
        ];
    }
    
    /**
     * Get hierarchical structure for this Global organization
     */
    public function getHierarchicalStructure()
    {
        $structure = [
            'global' => [
                'id' => $this->id,
                'name' => $this->name,
                'code' => $this->code,
                'type' => 'global'
            ],
            'godinas' => []
        ];
        
        $godinas = $this->godinas();
        foreach ($godinas as $godina) {
            $godinaModel = new \App\Models\Godina();
            $godinaData = $godinaModel->find($godina['id']);
            
            $structure['godinas'][] = [
                'id' => $godina['id'],
                'name' => $godina['name'],
                'code' => $godina['code'],
                'type' => 'godina',
                'gamtas' => $godinaModel->getGamtasWithGurmus()
            ];
        }
        
        return $structure;
    }
}