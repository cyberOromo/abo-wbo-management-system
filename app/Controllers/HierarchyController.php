<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Godina;
use App\Models\Gamta;
use App\Models\Gurmu;
use App\Utils\Database;

/**
 * Hierarchy Controller
 * ABO-WBO Management System - Organizational Hierarchy Management
 */
class HierarchyController extends Controller
{
    protected $godinaModel;
    protected $gamtaModel;
    protected $gurmuModel;
    protected $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->godinaModel = new Godina();
        $this->gamtaModel = new Gamta();
        $this->gurmuModel = new Gurmu();
    }
    
    /**
     * Display hierarchy overview
     */
    public function index()
    {
        $this->requireAuth();
        
        // Get hierarchy statistics
        $stats = $this->getHierarchyStats();
        
        // Get recent activity
        $recentActivity = $this->getRecentHierarchyActivity();
        
        return $this->render('hierarchy/index_modern', [
            'title' => 'Organizational Hierarchy',
            'stats' => $stats,
            'hierarchy_stats' => $stats,
            'gamta_units' => $this->gamtaModel->getActive(),
            'gurmu_units' => $this->gurmuModel->getActive(),
            'godina_units' => $this->godinaModel->getActive(),
            'key_positions' => $this->getKeyPositions(),
            'hierarchy_metrics' => $this->getHierarchyMetrics(),
            'can_create' => true,
            'recentActivity' => $recentActivity
        ]);
    }
    
    /**
     * Admin Hierarchy Management (Legacy Support)
     * This method provides the same functionality as the old admin-hierarchy.php
     */
    public function adminHierarchy()
    {
        $this->requireAuth();
        $this->requireRole('admin'); // Require admin role
        
        try {
            // Get all hierarchy data with proper joins to avoid undefined key errors
            $godinas = $this->db->fetchAll("
                SELECT g.*, COUNT(ga.id) as gamta_count 
                FROM godinas g 
                LEFT JOIN gamtas ga ON g.id = ga.godina_id 
                WHERE g.status = 'active' 
                GROUP BY g.id 
                ORDER BY g.name
            ");
            
            $gamtas = $this->db->fetchAll("
                SELECT ga.*, g.name as godina_name, COUNT(gu.id) as gurmu_count 
                FROM gamtas ga 
                INNER JOIN godinas g ON ga.godina_id = g.id 
                LEFT JOIN gurmus gu ON ga.id = gu.gamta_id 
                WHERE ga.status = 'active' 
                GROUP BY ga.id 
                ORDER BY g.name, ga.name
            ");
            
            $gurmus = $this->db->fetchAll("
                SELECT gu.*, ga.name as gamta_name, g.name as godina_name,
                       COALESCE(gu.address, '') as location
                FROM gurmus gu 
                INNER JOIN gamtas ga ON gu.gamta_id = ga.id 
                INNER JOIN godinas g ON ga.godina_id = g.id 
                WHERE gu.status = 'active' 
                ORDER BY g.name, ga.name, gu.name
            ");
            
            return $this->render('hierarchy.admin', [
                'title' => 'System Admin - Hierarchy Management',
                'godinas' => $godinas,
                'gamtas' => $gamtas,
                'gurmus' => $gurmus
            ]);
            
        } catch (\Exception $e) {
            log_error('Admin hierarchy error: ' . $e->getMessage());
            return $this->error('Failed to load hierarchy data: ' . $e->getMessage());
        }
    }
    
    /**
     * Show hierarchy tree view
     */
    public function treeView()
    {
        $this->requireAuth();
        
        // Get complete hierarchy tree
        $hierarchyTree = $this->buildHierarchyTree();
        
        return $this->render('hierarchy.tree', [
            'title' => 'Hierarchy Tree View',
            'hierarchyTree' => $hierarchyTree
        ]);
    }
    
    /**
     * Get hierarchy tree data (JSON for AJAX)
     */
    public function treeData()
    {
        $this->requireAuth();
        
        $tree = $this->buildHierarchyTree();
        $this->json(['success' => true, 'data' => $tree]);
    }
    
    /**
     * Show create hierarchy form
     */
    public function create()
    {
        $this->requireAuth();
        $this->requirePermission('hierarchy.manage');
        
        $type = $_GET['type'] ?? 'godina';
        
        if ($type === 'gurmu') {
            $gamtas = $this->gamtaModel->getActive();
            $godinaId = $_GET['gamta_id'] ?? null;
            
            return $this->render('hierarchy.create', [
                'title' => 'Create Gurmu',
                'type' => 'gurmu',
                'gamtas' => $gamtas,
                'gamta_id' => $godinaId
            ]);
        }
        
        if ($type === 'gamta') {
            $godinas = $this->godinaModel->getActive();
            $godinaId = $_GET['godina_id'] ?? null;
            
            return $this->render('hierarchy.create', [
                'title' => 'Create Gamta',
                'type' => 'gamta',
                'godinas' => $godinas,
                'godina_id' => $godinaId
            ]);
        }
        
        return $this->render('hierarchy.create', [
            'title' => 'Create Godina',
            'type' => 'godina'
        ]);
    }
    
    /**
     * Store new hierarchy item
     */
    public function store()
    {
        $this->requireAuth();
        $this->requirePermission('hierarchy.manage');
        $this->requireCsrf();
        
        $type = $_POST['type'] ?? 'godina';
        
        try {
            if ($type === 'godina') {
                $this->storeGodina();
            } elseif ($type === 'gamta') {
                $this->storeGamta();
            } elseif ($type === 'gurmu') {
                $this->storeGurmu();
            } else {
                $this->redirectBack(['general' => 'Invalid hierarchy type specified.']);
            }
        } catch (\Exception $e) {
            log_error('Hierarchy creation error: ' . $e->getMessage());
            $this->redirectBack(['general' => 'An error occurred while creating the hierarchy item.']);
        }
    }
    
    /**
     * Show hierarchy item details
     */
    public function show($id)
    {
        $this->requireAuth();
        
        $type = $_GET['type'] ?? 'godina';
        
        if ($type === 'gurmu') {
            $gurmu = $this->gurmuModel->findWithRelations($id);
            
            if (!$gurmu) {
                $this->redirectWithMessage('/hierarchy', 'Gurmu not found.', 'error');
            }
            
            // Get users in this gurmu
            $users = $this->getUsersByGurmu($id);
            
            // Get statistics
            $stats = $this->getGurmuStats($id);
            
            return $this->render('hierarchy.show', [
                'title' => 'Gurmu Details - ' . $gurmu['name'],
                'type' => 'gurmu',
                'unit' => $gurmu,
                'users' => $users,
                'stats' => $stats
            ]);
        }
        
        if ($type === 'gamta') {
            $gamta = $this->gamtaModel->findWithRelations($id);
            
            if (!$gamta) {
                $this->redirectWithMessage('/hierarchy', 'Gamta not found.', 'error');
            }
            
            // Get gurmus in this gamta
            $gurmus = $this->getGurmusByGamta($id);
            
            // Get statistics
            $stats = $this->getGamtaStats($id);
            
            return $this->render('hierarchy.show', [
                'title' => 'Gamta Details - ' . $gamta['name'],
                'type' => 'gamta',
                'unit' => $gamta,
                'gurmus' => $gurmus,
                'stats' => $stats
            ]);
        }
        
        // Show Godina details
        $godina = $this->godinaModel->findWithRelations($id);
        
        if (!$godina) {
            $this->redirectWithMessage('/hierarchy', 'Godina not found.', 'error');
        }
        
        // Get gamtas in this godina
        $gamtas = $this->getGamtasByGodina($id);
        
        // Get statistics
        $stats = $this->getGodinaStats($id);
        
        return $this->render('hierarchy.show-godina', [
            'title' => 'Godina Details - ' . $godina['name'],
            'godina' => $godina,
            'gamtas' => $gamtas,
            'stats' => $stats
        ]);
    }
    
    /**
     * Show edit hierarchy form
     */
    public function edit($id)
    {
        $this->requireAuth();
        $this->requirePermission('hierarchy.manage');
        
        $type = $_GET['type'] ?? 'godina';
        
        if ($type === 'gurmu') {
            $gurmu = $this->gurmuModel->find($id);
            
            if (!$gurmu) {
                $this->redirectWithMessage('/hierarchy', 'Gurmu not found.', 'error');
            }
            
            $gamtas = $this->gamtaModel->getActive();
            
            return $this->render('hierarchy.edit', [
                'title' => 'Edit Gurmu',
                'type' => 'gurmu',
                'unit' => $gurmu,
                'gamtas' => $gamtas
            ]);
        }
        
        if ($type === 'gamta') {
            $gamta = $this->gamtaModel->find($id);
            
            if (!$gamta) {
                $this->redirectWithMessage('/hierarchy', 'Gamta not found.', 'error');
            }
            
            $godinas = $this->godinaModel->getActive();
            
            return $this->render('hierarchy.edit', [
                'title' => 'Edit Gamta',
                'type' => 'gamta',
                'unit' => $gamta,
                'godinas' => $godinas
            ]);
        }
        
        $godina = $this->godinaModel->find($id);
        
        if (!$godina) {
            $this->redirectWithMessage('/hierarchy', 'Godina not found.', 'error');
        }
        
        return $this->render('hierarchy.edit', [
            'title' => 'Edit Godina',
            'type' => 'godina',
            'unit' => $godina
        ]);
    }
    
    /**
     * Update hierarchy item
     */
    public function update($id)
    {
        $this->requireAuth();
        $this->requirePermission('hierarchy.manage');
        $this->requireCsrf();
        
        $type = $_GET['type'] ?? 'godina';
        
        try {
            if ($type === 'gurmu') {
                $this->updateGurmu($id);
            } elseif ($type === 'gamta') {
                $this->updateGamta($id);
            } else {
                $this->updateGodina($id);
            }
        } catch (\Exception $e) {
            log_error('Hierarchy update error: ' . $e->getMessage());
            $this->redirectBack(['general' => 'An error occurred while updating the hierarchy item.']);
        }
    }
    
    /**
     * Delete hierarchy item
     */
    public function destroy($id)
    {
        $this->requireAuth();
        $this->requirePermission('hierarchy.manage');
        $this->requireCsrf();
        
        $type = $_GET['type'] ?? 'godina';
        
        try {
            if ($type === 'gurmu') {
                $this->deleteGurmu($id);
            } elseif ($type === 'gamta') {
                $this->deleteGamta($id);
            } else {
                $this->deleteGodina($id);
            }
        } catch (\Exception $e) {
            log_error('Hierarchy deletion error: ' . $e->getMessage());
            $this->redirectWithMessage('/hierarchy', 'An error occurred while deleting the hierarchy item.', 'error');
        }
    }
    
    /**
     * Store new Godina
     */
    private function storeGodina()
    {
        $data = $this->validate([
            'name' => 'required|min:2|max:100',
            'code' => 'required|min:2|max:10|unique:godinas,code',
            'description' => 'max:500',
            'location' => 'max:255',
            'contact_person' => 'max:100',
            'contact_email' => 'email',
            'contact_phone' => 'max:20',
            'status' => 'required|in:active,inactive'
        ]);
        
        $data['created_by'] = auth_user()['id'];
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $godinaId = $this->godinaModel->create($data);
        
        if ($godinaId) {
            log_activity('godina.created', "Created Godina: {$data['name']}", ['godina_id' => $godinaId]);
            $this->redirectWithMessage('/hierarchy', 'Godina created successfully!', 'success');
        } else {
            $this->redirectBack(['general' => 'Failed to create Godina. Please try again.']);
        }
    }
    
    /**
     * Store new Gamta
     */
    private function storeGamta()
    {
        $data = $this->validate([
            'godina_id' => 'required|exists:godinas,id',
            'name' => 'required|min:2|max:100',
            'code' => 'required|min:2|max:10|unique:gamtas,code',
            'description' => 'max:500',
            'location' => 'max:255',
            'contact_person' => 'max:100',
            'contact_email' => 'email',
            'contact_phone' => 'max:20',
            'status' => 'required|in:active,inactive'
        ]);
        
        $data['created_by'] = auth_user()['id'];
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $gamtaId = $this->gamtaModel->create($data);
        
        if ($gamtaId) {
            log_activity('gamta.created', "Created Gamta: {$data['name']}", ['gamta_id' => $gamtaId]);
            $this->redirectWithMessage('/hierarchy', 'Gamta created successfully!', 'success');
        } else {
            $this->redirectBack(['general' => 'Failed to create Gamta. Please try again.']);
        }
    }
    
    /**
     * Store new Gurmu
     */
    private function storeGurmu()
    {
        $data = $this->validate([
            'gamta_id' => 'required|exists:gamtas,id',
            'name' => 'required|min:2|max:100',
            'code' => 'required|min:2|max:10|unique:gurmus,code',
            'description' => 'max:500',
            'contact_email' => 'email',
            'contact_phone' => 'max:20',
            'address' => 'max:500',
            'website' => 'url|max:255',
            'meeting_schedule' => 'max:255',
            'membership_fee' => 'numeric|min:0|max:999999.99',
            'currency' => 'max:3',
            'status' => 'required|in:active,inactive'
        ]);
        
        $data['created_by'] = auth_user()['id'];
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Set defaults
        $data['membership_fee'] = $data['membership_fee'] ?? 0.00;
        $data['currency'] = $data['currency'] ?? 'USD';
        
        try {
            $gurmuId = Gurmu::createGurmu($data);
            
            log_activity('gurmu.created', "Created Gurmu: {$data['name']}", ['gurmu_id' => $gurmuId]);
            $this->redirectWithMessage('/hierarchy', 'Gurmu created successfully!', 'success');
        } catch (\Exception $e) {
            log_error('Gurmu creation error: ' . $e->getMessage());
            $this->redirectBack(['general' => $e->getMessage()]);
        }
    }

    /**
     * Update Godina
     */
    private function updateGodina($id)
    {
        $godina = $this->godinaModel->find($id);
        
        if (!$godina) {
            $this->redirectWithMessage('/hierarchy', 'Godina not found.', 'error');
        }
        
        $data = $this->validate([
            'name' => 'required|min:2|max:100',
            'code' => 'required|min:2|max:10|unique:godinas,code,' . $id,
            'description' => 'max:500',
            'location' => 'max:255',
            'contact_person' => 'max:100',
            'contact_email' => 'email',
            'contact_phone' => 'max:20',
            'status' => 'required|in:active,inactive'
        ]);
        
        $data['updated_by'] = auth_user()['id'];
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $success = $this->godinaModel->update($id, $data);
        
        if ($success) {
            log_activity('godina.updated', "Updated Godina: {$data['name']}", ['godina_id' => $id]);
            $this->redirectWithMessage('/hierarchy', 'Godina updated successfully!', 'success');
        } else {
            $this->redirectBack(['general' => 'Failed to update Godina. Please try again.']);
        }
    }
    
    /**
     * Update Gamta
     */
    private function updateGamta($id)
    {
        $gamta = $this->gamtaModel->find($id);
        
        if (!$gamta) {
            $this->redirectWithMessage('/hierarchy', 'Gamta not found.', 'error');
        }
        
        $data = $this->validate([
            'godina_id' => 'required|exists:godinas,id',
            'name' => 'required|min:2|max:100',
            'code' => 'required|min:2|max:10|unique:gamtas,code,' . $id,
            'description' => 'max:500',
            'location' => 'max:255',
            'contact_person' => 'max:100',
            'contact_email' => 'email',
            'contact_phone' => 'max:20',
            'status' => 'required|in:active,inactive'
        ]);
        
        $data['updated_by'] = auth_user()['id'];
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $success = $this->gamtaModel->update($id, $data);
        
        if ($success) {
            log_activity('gamta.updated', "Updated Gamta: {$data['name']}", ['gamta_id' => $id]);
            $this->redirectWithMessage('/hierarchy', 'Gamta updated successfully!', 'success');
        } else {
            $this->redirectBack(['general' => 'Failed to update Gamta. Please try again.']);
        }
    }
    
    /**
     * Update Gurmu
     */
    private function updateGurmu($id)
    {
        $gurmu = $this->gurmuModel->find($id);
        
        if (!$gurmu) {
            $this->redirectWithMessage('/hierarchy', 'Gurmu not found.', 'error');
        }
        
        $data = $this->validate([
            'gamta_id' => 'required|exists:gamtas,id',
            'name' => 'required|min:2|max:100',
            'code' => 'required|min:2|max:10|unique:gurmus,code,' . $id,
            'description' => 'max:500',
            'contact_email' => 'email',
            'contact_phone' => 'max:20',
            'address' => 'max:500',
            'website' => 'url|max:255',
            'meeting_schedule' => 'max:255',
            'membership_fee' => 'numeric|min:0|max:999999.99',
            'currency' => 'max:3',
            'status' => 'required|in:active,inactive'
        ]);
        
        $data['updated_by'] = auth_user()['id'];
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        try {
            $success = Gurmu::updateGurmu($id, $data);
            
            if ($success) {
                log_activity('gurmu.updated', "Updated Gurmu: {$data['name']}", ['gurmu_id' => $id]);
                $this->redirectWithMessage('/hierarchy', 'Gurmu updated successfully!', 'success');
            } else {
                $this->redirectBack(['general' => 'Failed to update Gurmu. Please try again.']);
            }
        } catch (\Exception $e) {
            log_error('Gurmu update error: ' . $e->getMessage());
            $this->redirectBack(['general' => $e->getMessage()]);
        }
    }

    /**
     * Delete Godina
     */
    private function deleteGodina($id)
    {
        $godina = $this->godinaModel->find($id);
        
        if (!$godina) {
            $this->redirectWithMessage('/hierarchy', 'Godina not found.', 'error');
        }
        
        // Check if Godina has Gamtas
        $gamtaCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM gamtas WHERE godina_id = ? AND status != 'deleted'",
            [$id]
        )['count'];
        
        if ($gamtaCount > 0) {
            $this->redirectWithMessage('/hierarchy', 
                'Cannot delete Godina that contains Gamtas. Please move or delete the Gamtas first.', 
                'error'
            );
        }
        
        // Soft delete
        $success = $this->godinaModel->update($id, [
            'status' => 'deleted',
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_by' => auth_user()['id']
        ]);
        
        if ($success) {
            log_activity('godina.deleted', "Deleted Godina: {$godina['name']}", ['godina_id' => $id]);
            $this->redirectWithMessage('/hierarchy', 'Godina deleted successfully!', 'success');
        } else {
            $this->redirectWithMessage('/hierarchy', 'Failed to delete Godina.', 'error');
        }
    }
    
    /**
     * Delete Gamta
     */
    private function deleteGamta($id)
    {
        $gamta = $this->gamtaModel->find($id);
        
        if (!$gamta) {
            $this->redirectWithMessage('/hierarchy', 'Gamta not found.', 'error');
        }
        
        // Check if Gamta has users
        $userCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM users WHERE gamta_id = ? AND status != 'deleted'",
            [$id]
        )['count'];
        
        if ($userCount > 0) {
            $this->redirectWithMessage('/hierarchy', 
                'Cannot delete Gamta that has assigned users. Please reassign the users first.', 
                'error'
            );
        }
        
        // Soft delete
        $success = $this->gamtaModel->update($id, [
            'status' => 'deleted',
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_by' => auth_user()['id']
        ]);
        
        if ($success) {
            log_activity('gamta.deleted', "Deleted Gamta: {$gamta['name']}", ['gamta_id' => $id]);
            $this->redirectWithMessage('/hierarchy', 'Gamta deleted successfully!', 'success');
        } else {
            $this->redirectWithMessage('/hierarchy', 'Failed to delete Gamta.', 'error');
        }
    }
    
    /**
     * Get hierarchy statistics
     */
    private function getHierarchyStats(): array
    {
        $stats = [];
        
        // Total Godinas
        $stats['total_godinas'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM godinas WHERE status != 'deleted'"
        )['count'];
        
        // Active Godinas
        $stats['active_godinas'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM godinas WHERE status = 'active'"
        )['count'];
        
        // Total Gamtas
        $stats['total_gamtas'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM gamtas WHERE status != 'deleted'"
        )['count'];
        
        // Active Gamtas
        $stats['active_gamtas'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM gamtas WHERE status = 'active'"
        )['count'];
        
        // Total Gurmus
        $stats['total_gurmus'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM gurmus WHERE status != 'deleted'"
        )['count'];
        
        // Active Gurmus
        $stats['active_gurmus'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM gurmus WHERE status = 'active'"
        )['count'];
        
        // Users assigned to hierarchy (members are managed at Gurmu level)
        $stats['assigned_users'] = $this->db->fetch(
            "SELECT COUNT(DISTINCT u.id) as count FROM users u 
             INNER JOIN user_assignments ua ON u.id = ua.user_id 
             WHERE ua.status = 'active' AND u.status = 'active'"
        )['count'];
        
        // Unassigned users
        $stats['unassigned_users'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM users u 
             WHERE u.status = 'active' 
             AND u.id NOT IN (SELECT DISTINCT user_id FROM user_assignments WHERE status = 'active')"
        )['count'];
        
        return $stats;
    }
    
    /**
     * Get key positions across hierarchy
     */
    private function getKeyPositions(): array
    {
        // This would fetch from positions/assignments tables
        // For now, return sample data
        return [
            [
                'title' => 'President',
                'level' => 'godina',
                'holder_name' => 'John Doe',
                'unit_name' => 'Central Godina',
                'status' => 'filled',
                'appointed_at' => '2023-01-01'
            ],
            [
                'title' => 'Vice President',
                'level' => 'godina',
                'holder_name' => 'Jane Smith', 
                'unit_name' => 'Central Godina',
                'status' => 'filled',
                'appointed_at' => '2023-01-01'
            ]
        ];
    }
    
    /**
     * Get hierarchy metrics
     */
    private function getHierarchyMetrics(): array
    {
        return [
            'completeness' => 75,
            'position_coverage' => 68,
            'active_positions' => 24,
            'vacant_positions' => 8,
            'total_units' => 15,
            'godina_count' => 3,
            'gamta_count' => 8,
            'gurmu_count' => 25
        ];
    }
    
    /**
     * Get recent hierarchy activity
     */
    private function getRecentHierarchyActivity(): array
    {
        // TODO: Create activity_logs table for tracking hierarchy changes
        // For now, return recent user assignments as activity
        try {
            return $this->db->fetchAll(
                "SELECT 
                    ua.id,
                    CONCAT('User assignment: ', u.first_name, ' ', u.last_name, ' assigned to ', p.name) as action,
                    ua.created_at,
                    u.first_name,
                    u.last_name,
                    p.name as position_name
                FROM user_assignments ua
                INNER JOIN users u ON ua.user_id = u.id  
                INNER JOIN positions p ON ua.position_id = p.id
                WHERE ua.status = 'active'
                ORDER BY ua.created_at DESC 
                LIMIT 10"
            );
        } catch (Exception $e) {
            // Fallback to empty array if there's any issue
            return [];
        }
    }
    
    /**
     * Build complete hierarchy tree with all 4 levels: Global → Godina → Gamta → Gurmu
     */
    private function buildHierarchyTree(): array
    {
        $godinas = $this->db->fetchAll(
            "SELECT * FROM godinas WHERE status != 'deleted' ORDER BY name"
        );
        
        $tree = [];
        
        foreach ($godinas as $godina) {
            $gamtas = $this->db->fetchAll(
                "SELECT gm.*, COUNT(DISTINCT u.id) as user_count,
                        COUNT(DISTINCT gr.id) as gurmu_count
                 FROM gamtas gm
                 LEFT JOIN gurmus gr ON gm.id = gr.gamta_id AND gr.status != 'deleted'
                 LEFT JOIN users u ON gr.id = u.gurmu_id AND u.status = 'active'
                 WHERE gm.godina_id = ? AND gm.status != 'deleted'
                 GROUP BY gm.id
                 ORDER BY gm.name",
                [$godina['id']]
            );
            
            $gamtaChildren = [];
            foreach ($gamtas as $gamta) {
                $gurmus = $this->db->fetchAll(
                    "SELECT gr.*, COUNT(u.id) as user_count
                     FROM gurmus gr
                     LEFT JOIN users u ON gr.id = u.gurmu_id AND u.status = 'active'
                     WHERE gr.gamta_id = ? AND gr.status != 'deleted'
                     GROUP BY gr.id
                     ORDER BY gr.name",
                    [$gamta['id']]
                );
                
                $gurmuChildren = array_map(function($gurmu) {
                    return [
                        'type' => 'gurmu',
                        'id' => $gurmu['id'],
                        'name' => $gurmu['name'],
                        'code' => $gurmu['code'],
                        'status' => $gurmu['status'],
                        'location' => $gurmu['address'] ?? null,
                        'user_count' => $gurmu['user_count'],
                        'membership_fee' => $gurmu['membership_fee'],
                        'currency' => $gurmu['currency'],
                        'meeting_schedule' => $gurmu['meeting_schedule'],
                        'children' => [] // Gurmus are leaf nodes
                    ];
                }, $gurmus);
                
                $gamtaChildren[] = [
                    'type' => 'gamta',
                    'id' => $gamta['id'],
                    'name' => $gamta['name'],
                    'code' => $gamta['code'],
                    'status' => $gamta['status'],
                    'location' => $gamta['address'] ?? null,
                    'user_count' => $gamta['user_count'],
                    'gurmu_count' => $gamta['gurmu_count'],
                    'children' => $gurmuChildren
                ];
            }
            
            $tree[] = [
                'type' => 'godina',
                'id' => $godina['id'],
                'name' => $godina['name'],
                'code' => $godina['code'],
                'status' => $godina['status'],
                'location' => $godina['address'] ?? null,
                'gamta_count' => count($gamtaChildren),
                'children' => $gamtaChildren
            ];
        }
        
        return $tree;
    }
    
    /**
     * Get users by Gamta
     */
    private function getUsersByGamta($gamtaId): array
    {
        return $this->db->fetchAll(
            "SELECT u.*, p.title as position_title
             FROM users u
             LEFT JOIN positions p ON u.position_id = p.id
             WHERE u.gamta_id = ? AND u.status = 'active'
             ORDER BY u.first_name, u.last_name",
            [$gamtaId]
        );
    }
    
    /**
     * Get Gamtas by Godina
     */
    private function getGamtasByGodina($godinaId): array
    {
        return $this->db->fetchAll(
            "SELECT g.*, COUNT(u.id) as user_count
             FROM gamtas g
             LEFT JOIN users u ON g.id = u.gamta_id AND u.status = 'active'
             WHERE g.godina_id = ? AND g.status != 'deleted'
             GROUP BY g.id
             ORDER BY g.name",
            [$godinaId]
        );
    }
    
    /**
     * Get Godina statistics
     */
    private function getGodinaStats($godinaId): array
    {
        $stats = [];
        
        // Total gamtas
        $stats['total_gamtas'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM gamtas WHERE godina_id = ? AND status != 'deleted'",
            [$godinaId]
        )['count'];
        
        // Active gamtas
        $stats['active_gamtas'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM gamtas WHERE godina_id = ? AND status = 'active'",
            [$godinaId]
        )['count'];
        
        // Total users
        $stats['total_users'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM users u 
             JOIN gamtas g ON u.gamta_id = g.id 
             WHERE g.godina_id = ? AND u.status = 'active'",
            [$godinaId]
        )['count'];
        
        return $stats;
    }
    
    /**
     * Get Gamta statistics
     */
    private function getGamtaStats($gamtaId): array
    {
        $stats = [];
        
        // Total users
        $stats['total_users'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM users WHERE gamta_id = ? AND status = 'active'",
            [$gamtaId]
        )['count'];
        
        // Users by role
        $roleStats = $this->db->fetchAll(
            "SELECT role, COUNT(*) as count FROM users 
             WHERE gamta_id = ? AND status = 'active' 
             GROUP BY role",
            [$gamtaId]
        );
        
        foreach ($roleStats as $role) {
            $stats['roles'][$role['role']] = $role['count'];
        }
        
        return $stats;
    }

    /**
     * Delete Gurmu
     */
    private function deleteGurmu($id)
    {
        $gurmu = $this->gurmuModel->find($id);
        
        if (!$gurmu) {
            $this->redirectWithMessage('/hierarchy', 'Gurmu not found.', 'error');
        }
        
        // Check if Gurmu has users
        $userCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM users WHERE gurmu_id = ? AND status != 'deleted'",
            [$id]
        )['count'];
        
        if ($userCount > 0) {
            $this->redirectWithMessage('/hierarchy', 
                'Cannot delete Gurmu that has assigned users. Please reassign the users first.', 
                'error'
            );
        }
        
        // Soft delete
        $success = $this->gurmuModel->update($id, [
            'status' => 'deleted',
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_by' => auth_user()['id']
        ]);
        
        if ($success) {
            log_activity('gurmu.deleted', "Deleted Gurmu: {$gurmu['name']}", ['gurmu_id' => $id]);
            $this->redirectWithMessage('/hierarchy', 'Gurmu deleted successfully!', 'success');
        } else {
            $this->redirectWithMessage('/hierarchy', 'Failed to delete Gurmu.', 'error');
        }
    }

    /**
     * Get users by Gurmu
     */
    private function getUsersByGurmu($gurmuId): array
    {
        return $this->db->fetchAll(
            "SELECT u.*, p.name_en as position_name
             FROM users u
             LEFT JOIN positions p ON u.position_id = p.id
             WHERE u.gurmu_id = ? AND u.status = 'active'
             ORDER BY u.first_name, u.last_name",
            [$gurmuId]
        );
    }
    
    /**
     * Get Gurmus by Gamta
     */
    private function getGurmusByGamta($gamtaId): array
    {
        return $this->db->fetchAll(
            "SELECT g.*, COUNT(u.id) as user_count
             FROM gurmus g
             LEFT JOIN users u ON g.id = u.gurmu_id AND u.status = 'active'
             WHERE g.gamta_id = ? AND g.status != 'deleted'
             GROUP BY g.id
             ORDER BY g.name",
            [$gamtaId]
        );
    }

    /**
     * Get Gurmu statistics
     */
    private function getGurmuStats($gurmuId): array
    {
        $stats = [];
        
        // Total users
        $stats['total_users'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM users WHERE gurmu_id = ? AND status != 'deleted'",
            [$gurmuId]
        )['count'];
        
        // Active users
        $stats['active_users'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM users WHERE gurmu_id = ? AND status = 'active'",
            [$gurmuId]
        )['count'];
        
        // Pending approval users
        $stats['pending_users'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM users WHERE gurmu_id = ? AND status = 'pending_approval'",
            [$gurmuId]
        )['count'];
        
        // Users by position
        $positionStats = $this->db->fetchAll(
            "SELECT COALESCE(p.name_en, 'Member') as position_name, COUNT(*) as count 
             FROM users u
             LEFT JOIN positions p ON u.position_id = p.id
             WHERE u.gurmu_id = ? AND u.status = 'active' 
             GROUP BY u.position_id, p.name_en
             ORDER BY count DESC",
            [$gurmuId]
        );
        
        $stats['positions'] = [];
        foreach ($positionStats as $position) {
            $stats['positions'][$position['position_name']] = $position['count'];
        }

        // Membership fee statistics
        $gurmu = $this->gurmuModel->find($gurmuId);
        if ($gurmu) {
            $stats['membership_fee'] = $gurmu['membership_fee'];
            $stats['currency'] = $gurmu['currency'];
            $stats['expected_monthly_revenue'] = $stats['active_users'] * $gurmu['membership_fee'];
        }
        
        return $stats;
    }
    
    /**
     * List Godinas for dropdown selection
     */
    public function listGodinas(): void
    {
        try {
            $godinas = $this->db->fetchAll(
                "SELECT id, name, code, description FROM godinas 
                 WHERE status = 'active' 
                 ORDER BY name ASC"
            );
            
            $this->jsonResponse([
                'success' => true,
                'data' => $godinas
            ]);
        } catch (Exception $e) {
            error_log("Error listing Godinas: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to load Godinas'
            ], 500);
        }
    }
    
    /**
     * List Gamtas for dropdown selection
     */
    public function listGamtas(): void
    {
        try {
            $gamtas = $this->db->fetchAll(
                "SELECT id, name, code, description, godina_id FROM gamtas 
                 WHERE status = 'active' 
                 ORDER BY name ASC"
            );
            
            $this->jsonResponse([
                'success' => true,
                'data' => $gamtas
            ]);
        } catch (Exception $e) {
            error_log("Error listing Gamtas: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to load Gamtas'
            ], 500);
        }
    }
    
    /**
     * Send JSON response
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}