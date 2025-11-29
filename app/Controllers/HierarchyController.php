<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Godina;
use App\Models\Gamta;
use App\Models\Gurmu;
use App\Utils\Database;
use App\Utils\HierarchyCodeGenerator;
use PDO;

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
    protected $codeGenerator;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->godinaModel = new Godina();
        $this->gamtaModel = new Gamta();
        $this->gurmuModel = new Gurmu();
        $this->codeGenerator = new HierarchyCodeGenerator();
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
        
        echo $this->render('hierarchy/index_modern', [
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
            $pdo = $this->db->getPdo();
            
            $stmt = $pdo->query("
                SELECT g.*, COUNT(ga.id) as gamta_count 
                FROM godinas g 
                LEFT JOIN gamtas ga ON g.id = ga.godina_id 
                WHERE g.status = 'active' 
                GROUP BY g.id 
                ORDER BY g.name
            ");
            $godinas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $pdo->query("
                SELECT ga.*, g.name as godina_name, COUNT(gu.id) as gurmu_count 
                FROM gamtas ga 
                INNER JOIN godinas g ON ga.godina_id = g.id 
                LEFT JOIN gurmus gu ON ga.id = gu.gamta_id 
                WHERE ga.status = 'active' 
                GROUP BY ga.id 
                ORDER BY g.name, ga.name
            ");
            $gamtas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $pdo->query("
                SELECT gu.*, ga.name as gamta_name, g.name as godina_name,
                       COALESCE(gu.address, '') as location
                FROM gurmus gu 
                INNER JOIN gamtas ga ON gu.gamta_id = ga.id 
                INNER JOIN godinas g ON ga.godina_id = g.id 
                WHERE gu.status = 'active' 
                ORDER BY g.name, ga.name, gu.name
            ");
            $gurmus = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo $this->render('hierarchy.admin', [
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
        
        echo $this->render('hierarchy.tree', [
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
            
            echo $this->render('hierarchy.create', [
                'title' => 'Create Gurmu',
                'type' => 'gurmu',
                'gamtas' => $gamtas,
                'gamta_id' => $godinaId
            ]);
            return;
        }
        
        if ($type === 'gamta') {
            $godinas = $this->godinaModel->getActive();
            $godinaId = $_GET['godina_id'] ?? null;
            
            echo $this->render('hierarchy.create', [
                'title' => 'Create Gamta',
                'type' => 'gamta',
                'godinas' => $godinas,
                'godina_id' => $godinaId
            ]);
            return;
        }
        
        echo $this->render('hierarchy.create', [
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
                return;
            }
            
            // Get users in this gurmu
            $users = $this->getUsersByGurmu($id);
            
            // Get statistics
            $stats = $this->getGurmuStats($id);
            
            echo $this->render('hierarchy.show-modern', [
                'title' => 'Gurmu Details - ' . $gurmu['name'],
                'type' => 'gurmu',
                'unit' => $gurmu,
                'users' => $users,
                'stats' => $stats
            ]);
            return;
        }
        
        if ($type === 'gamta') {
            $gamta = $this->gamtaModel->findWithRelations($id);
            
            if (!$gamta) {
                $this->redirectWithMessage('/hierarchy', 'Gamta not found.', 'error');
                return;
            }
            
            // Get gurmus in this gamta
            $gurmus = $this->getGurmusByGamta($id);
            
            // Get users in this gamta
            $users = $this->getUsersByGamta($id);
            
            // Get statistics
            $stats = $this->getGamtaStats($id);
            
            echo $this->render('hierarchy.show-modern', [
                'title' => 'Gamta Details - ' . $gamta['name'],
                'type' => 'gamta',
                'unit' => $gamta,
                'gurmus' => $gurmus,
                'users' => $users,
                'stats' => $stats
            ]);
            return;
        }
        
        // Show Godina details
        $godina = $this->godinaModel->findWithRelations($id);
        
        if (!$godina) {
            $this->redirectWithMessage('/hierarchy', 'Godina not found.', 'error');
            return;
        }
        
        // Get gamtas in this godina
        $gamtas = $this->getGamtasByGodina($id);
        
        // Get statistics
        $stats = $this->getGodinaStats($id);
        
        echo $this->render('hierarchy.show-modern', [
            'title' => 'Godina Details - ' . $godina['name'],
            'type' => 'godina',
            'unit' => $godina,
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
            
            echo $this->render('hierarchy.edit', [
                'title' => 'Edit Gurmu',
                'type' => 'gurmu',
                'unit' => $gurmu,
                'gamtas' => $gamtas
            ]);
            return;
        }
        
        if ($type === 'gamta') {
            $gamta = $this->gamtaModel->find($id);
            
            if (!$gamta) {
                $this->redirectWithMessage('/hierarchy', 'Gamta not found.', 'error');
            }
            
            $godinas = $this->godinaModel->getActive();
            
            echo $this->render('hierarchy.edit', [
                'title' => 'Edit Gamta',
                'type' => 'gamta',
                'unit' => $gamta,
                'godinas' => $godinas
            ]);
            return;
        }
        
        $godina = $this->godinaModel->find($id);
        
        if (!$godina) {
            $this->redirectWithMessage('/hierarchy', 'Godina not found.', 'error');
        }
        
        echo $this->render('hierarchy.edit', [
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
     * Show form to create new Godina
     */
    public function createGodina()
    {
        $this->requireAuth();
        
        echo $this->render('hierarchy/create_godina', [
            'title' => 'Create New Godina',
            'page_title' => 'Create New Godina'
        ]);
    }
    
    /**
     * Show form to create new Gamta
     */
    public function createGamta()
    {
        $this->requireAuth();
        
        // Get all godinas for dropdown
        $godinas = $this->db->fetchAll("SELECT id, name, code FROM godinas WHERE status = 'active' ORDER BY name");
        
        echo $this->render('hierarchy/create_gamta', [
            'title' => 'Create New Gamta',
            'page_title' => 'Create New Gamta',
            'godinas' => $godinas
        ]);
    }
    
    /**
     * Show form to create new Gurmu
     */
    public function createGurmu()
    {
        $this->requireAuth();
        
        // Get all godinas and gamtas for dropdowns
        $godinas = $this->db->fetchAll("SELECT id, name, code FROM godinas WHERE status = 'active' ORDER BY name");
        $gamtas = $this->db->fetchAll("SELECT id, godina_id, name, code FROM gamtas WHERE status = 'active' ORDER BY name");
        
        echo $this->render('hierarchy/create_gurmu', [
            'title' => 'Create New Gurmu',
            'page_title' => 'Create New Gurmu',
            'godinas' => $godinas,
            'gamtas' => $gamtas
        ]);
    }
    
    /**
     * Store new Godina
     */
    public function storeGodina()
    {
        $data = $this->validate([
            'name' => 'required|min:2|max:100',
            'code' => 'max:10', // Make code optional - will be auto-generated
            'description' => 'max:500',
            'contact_email' => 'email',
            'contact_phone' => 'max:20',
            'address' => 'max:500',
            'website' => 'url|max:255',
            'status' => 'in:active,inactive'
        ]);
        
        // Auto-generate code if not provided
        if (empty($data['code'])) {
            $data['code'] = $this->codeGenerator->generateGodinaCode($data['name']);
        } else {
            // Validate uniqueness if code is provided
            $existing = $this->db->fetch("SELECT id FROM godinas WHERE code = ?", [$data['code']]);
            if ($existing) {
                $this->redirectBack(['code' => 'This code is already in use. Leave blank for auto-generation.']);
                return;
            }
            $data['code'] = strtoupper($data['code']);
        }
        
        // Set defaults
        $data['status'] = $data['status'] ?? 'active';
        $data['created_by'] = auth_user()['id'];
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['global_id'] = 1; // Default global organization ID
        
        $godinaId = $this->godinaModel->create($data);
        
        if ($godinaId) {
            log_activity('godina.created', "Created Godina: {$data['name']} ({$data['code']})", ['godina_id' => $godinaId]);
            $this->redirectWithMessage('/hierarchy', 'Godina created successfully with code: ' . $data['code'], 'success');
        } else {
            $this->redirectBack(['general' => 'Failed to create Godina. Please try again.']);
        }
    }
    
    /**
     * Store new Gamta
     */
    public function storeGamta()
    {
        $data = $this->validate([
            'godina_id' => 'required|exists:godinas,id',
            'name' => 'required|min:2|max:100',
            'code' => 'max:10', // Make code optional - will be auto-generated
            'description' => 'max:500',
            'contact_email' => 'email',
            'contact_phone' => 'max:20',
            'address' => 'max:500',
            'website' => 'url|max:255',
            'status' => 'in:active,inactive'
        ]);
        
        // Auto-generate code if not provided
        if (empty($data['code'])) {
            $data['code'] = $this->codeGenerator->generateGamtaCode($data['godina_id'], $data['name']);
        } else {
            // Validate uniqueness if code is provided
            $existing = $this->db->fetch("SELECT id FROM gamtas WHERE code = ?", [$data['code']]);
            if ($existing) {
                $this->redirectBack(['code' => 'This code is already in use. Leave blank for auto-generation.']);
                return;
            }
            $data['code'] = strtoupper($data['code']);
        }
        
        // Set defaults
        $data['status'] = $data['status'] ?? 'active';
        $data['created_by'] = auth_user()['id'];
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $gamtaId = $this->gamtaModel->create($data);
        
        if ($gamtaId) {
            log_activity('gamta.created', "Created Gamta: {$data['name']} ({$data['code']})", ['gamta_id' => $gamtaId]);
            $this->redirectWithMessage('/hierarchy', 'Gamta created successfully with code: ' . $data['code'], 'success');
        } else {
            $this->redirectBack(['general' => 'Failed to create Gamta. Please try again.']);
        }
    }
    
    /**
     * Store new Gurmu
     */
    public function storeGurmu()
    {
        $data = $this->validate([
            'gamta_id' => 'required|exists:gamtas,id',
            'name' => 'required|min:2|max:100',
            'code' => 'max:10', // Make code optional - will be auto-generated
            'description' => 'max:500',
            'contact_email' => 'email',
            'contact_phone' => 'max:20',
            'address' => 'max:500',
            'website' => 'url|max:255',
            'status' => 'in:active,inactive'
        ]);
        
        // Auto-generate code if not provided
        if (empty($data['code'])) {
            $data['code'] = $this->codeGenerator->generateGurmuCode($data['gamta_id'], $data['name']);
        } else {
            // Validate uniqueness if code is provided
            $existing = $this->db->fetch("SELECT id FROM gurmus WHERE code = ?", [$data['code']]);
            if ($existing) {
                $this->redirectBack(['code' => 'This code is already in use. Leave blank for auto-generation.']);
                return;
            }
            $data['code'] = strtoupper($data['code']);
        }
        
        // Set defaults
        $data['status'] = $data['status'] ?? 'active';
        $data['created_by'] = auth_user()['id'];
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $gurmuId = $this->gurmuModel->create($data);
        
        if ($gurmuId) {
            log_activity('gurmu.created', "Created Gurmu: {$data['name']} ({$data['code']})", ['gurmu_id' => $gurmuId]);
            $this->redirectWithMessage('/hierarchy', 'Gurmu created successfully with code: ' . $data['code'], 'success');
        } else {
            $this->redirectBack(['general' => 'Failed to create Gurmu. Please try again.']);
        }
    }

    /**
     * Show interactive tree view
     */
    public function tree()
    {
        $this->requireAuth();
        
        echo $this->render('hierarchy/tree', [
            'title' => 'Organizational Hierarchy Tree',
            'pageTitle' => 'Organizational Hierarchy Tree'
        ]);
    }

    /**
     * Get tree data for visualization
     */
    public function getTreeData()
    {
        $this->requireAuth();
        
        try {
            // Fetch all active hierarchy data
            $godinas = $this->db->fetchAll(
                "SELECT id, name, code, description, contact_email, contact_phone, address 
                 FROM godinas WHERE status = 'active' ORDER BY name"
            );

            $gamtas = $this->db->fetchAll(
                "SELECT id, godina_id, name, code, description, contact_email, contact_phone, address 
                 FROM gamtas WHERE status = 'active' ORDER BY name"
            );

            $gurmus = $this->db->fetchAll(
                "SELECT id, gamta_id, name, code, description, contact_email, contact_phone, address 
                 FROM gurmus WHERE status = 'active' ORDER BY name"
            );

            // Get user counts for each level
            $godinaUsers = $this->db->fetchAll(
                "SELECT god.id as godina_id, COUNT(DISTINCT ua.user_id) as user_count 
                 FROM godinas god
                 LEFT JOIN user_assignments ua ON god.id = ua.godina_id AND ua.status = 'active'
                 LEFT JOIN users u ON ua.user_id = u.id AND u.status = 'active'
                 WHERE god.status = 'active'
                 GROUP BY god.id"
            );

            $gamtaUsers = $this->db->fetchAll(
                "SELECT gam.id as gamta_id, COUNT(DISTINCT ua.user_id) as user_count 
                 FROM gamtas gam
                 LEFT JOIN user_assignments ua ON gam.id = ua.gamta_id AND ua.status = 'active'
                 LEFT JOIN users u ON ua.user_id = u.id AND u.status = 'active'
                 WHERE gam.status = 'active'
                 GROUP BY gam.id"
            );

            $gurmuUsers = $this->db->fetchAll(
                "SELECT gur.id as gurmu_id, COUNT(DISTINCT ua.user_id) as user_count 
                 FROM gurmus gur
                 LEFT JOIN user_assignments ua ON gur.id = ua.gurmu_id AND ua.status = 'active'
                 LEFT JOIN users u ON ua.user_id = u.id AND u.status = 'active'
                 WHERE gur.status = 'active'
                 GROUP BY gur.id"
            );

            // Index user counts by ID for quick lookup
            $godinaUserCount = [];
            foreach ($godinaUsers as $gu) {
                $godinaUserCount[$gu['godina_id']] = $gu['user_count'];
            }

            $gamtaUserCount = [];
            foreach ($gamtaUsers as $gu) {
                $gamtaUserCount[$gu['gamta_id']] = $gu['user_count'];
            }

            $gurmuUserCount = [];
            foreach ($gurmuUsers as $gu) {
                $gurmuUserCount[$gu['gurmu_id']] = $gu['user_count'];
            }

            // Build tree structure
            $tree = [
                'name' => 'ABO-WBO Global',
                'type' => 'global',
                'id' => 'global',
                'children' => []
            ];

            foreach ($godinas as $godina) {
                $godinaNode = [
                    'name' => $godina['name'],
                    'code' => $godina['code'],
                    'type' => 'godina',
                    'id' => 'godina-' . $godina['id'],
                    'dbId' => $godina['id'],
                    'description' => $godina['description'],
                    'contact_email' => $godina['contact_email'],
                    'contact_phone' => $godina['contact_phone'],
                    'address' => $godina['address'],
                    'userCount' => $godinaUserCount[$godina['id']] ?? 0,
                    'children' => []
                ];

                // Add gamtas for this godina
                foreach ($gamtas as $gamta) {
                    if ($gamta['godina_id'] == $godina['id']) {
                        $gamtaNode = [
                            'name' => $gamta['name'],
                            'code' => $gamta['code'],
                            'type' => 'gamta',
                            'id' => 'gamta-' . $gamta['id'],
                            'dbId' => $gamta['id'],
                            'description' => $gamta['description'],
                            'contact_email' => $gamta['contact_email'],
                            'contact_phone' => $gamta['contact_phone'],
                            'address' => $gamta['address'],
                            'userCount' => $gamtaUserCount[$gamta['id']] ?? 0,
                            'children' => []
                        ];

                        // Add gurmus for this gamta
                        foreach ($gurmus as $gurmu) {
                            if ($gurmu['gamta_id'] == $gamta['id']) {
                                $gurmuNode = [
                                    'name' => $gurmu['name'],
                                    'code' => $gurmu['code'],
                                    'type' => 'gurmu',
                                    'id' => 'gurmu-' . $gurmu['id'],
                                    'dbId' => $gurmu['id'],
                                    'description' => $gurmu['description'],
                                    'contact_email' => $gurmu['contact_email'],
                                    'contact_phone' => $gurmu['contact_phone'],
                                    'address' => $gurmu['address'],
                                    'userCount' => $gurmuUserCount[$gurmu['id']] ?? 0
                                ];

                                $gamtaNode['children'][] = $gurmuNode;
                            }
                        }

                        $godinaNode['children'][] = $gamtaNode;
                    }
                }

                $tree['children'][] = $godinaNode;
            }

            header('Content-Type: application/json');
            echo json_encode($tree);
            exit;

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
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
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM gamtas WHERE godina_id = ? AND status != 'deleted'");
        $stmt->execute([$id]);
        $gamtaCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
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
        $pdo = $this->db->getPdo();
        
        // Total Godinas
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM godinas WHERE status != 'deleted'");
        $stats['total_godinas'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Active Godinas
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM godinas WHERE status = 'active'");
        $stats['active_godinas'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total Gamtas
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM gamtas WHERE status != 'deleted'");
        $stats['total_gamtas'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Active Gamtas
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM gamtas WHERE status = 'active'");
        $stats['active_gamtas'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total Gurmus
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM gurmus WHERE status != 'deleted'");
        $stats['total_gurmus'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Active Gurmus
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM gurmus WHERE status = 'active'");
        $stats['active_gurmus'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Users assigned to hierarchy (members are managed at Gurmu level)
        $stmt = $pdo->query("SELECT COUNT(DISTINCT u.id) as count FROM users u 
             INNER JOIN user_assignments ua ON u.id = ua.user_id 
             WHERE ua.status = 'active' AND u.status = 'active'");
        $stats['assigned_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Unassigned users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users u 
             WHERE u.status = 'active' 
             AND u.id NOT IN (SELECT DISTINCT user_id FROM user_assignments WHERE status = 'active')");
        $stats['unassigned_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
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
                "SELECT gm.*, 
                        COUNT(DISTINCT gr.id) as gurmu_count,
                        (SELECT COUNT(DISTINCT ua.user_id) 
                         FROM user_assignments ua 
                         INNER JOIN users u ON ua.user_id = u.id 
                         WHERE ua.gamta_id = gm.id AND ua.status = 'active' AND u.status = 'active') as user_count
                 FROM gamtas gm
                 LEFT JOIN gurmus gr ON gm.id = gr.gamta_id AND gr.status != 'deleted'
                 WHERE gm.godina_id = ? AND gm.status != 'deleted'
                 GROUP BY gm.id
                 ORDER BY gm.name",
                [$godina['id']]
            );
            
            $gamtaChildren = [];
            foreach ($gamtas as $gamta) {
                $gurmus = $this->db->fetchAll(
                    "SELECT gr.*,
                            (SELECT COUNT(DISTINCT ua.user_id) 
                             FROM user_assignments ua 
                             INNER JOIN users u ON ua.user_id = u.id 
                             WHERE ua.gurmu_id = gr.id AND ua.status = 'active' AND u.status = 'active') as user_count
                     FROM gurmus gr
                     WHERE gr.gamta_id = ? AND gr.status != 'deleted'
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
            "SELECT u.*, GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as position_title
             FROM users u
             JOIN gurmus gu ON u.gurmu_id = gu.id
             LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
             LEFT JOIN positions p ON ua.position_id = p.id
             WHERE gu.gamta_id = ? AND u.status = 'active'
             GROUP BY u.id
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
            "SELECT g.*, COUNT(gu.id) as gurmu_count
             FROM gamtas g
             LEFT JOIN gurmus gu ON g.id = gu.gamta_id AND gu.status != 'deleted'
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
        
        // Total users - join through gurmus since users belong to gurmu
        $stats['total_users'] = $this->db->fetch(
            "SELECT COUNT(DISTINCT u.id) as count FROM users u 
             JOIN gurmus gu ON u.gurmu_id = gu.id
             JOIN gamtas g ON gu.gamta_id = g.id 
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
        
        // Total users - join through gurmus
        $stats['total_users'] = $this->db->fetch(
            "SELECT COUNT(DISTINCT u.id) as count 
             FROM users u 
             JOIN gurmus gu ON u.gurmu_id = gu.id
             WHERE gu.gamta_id = ? AND u.status = 'active'",
            [$gamtaId]
        )['count'];
        
        // Users by role
        $roleStats = $this->db->fetchAll(
            "SELECT u.role, COUNT(DISTINCT u.id) as count 
             FROM users u
             JOIN gurmus gu ON u.gurmu_id = gu.id
             WHERE gu.gamta_id = ? AND u.status = 'active' 
             GROUP BY u.role",
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
            "SELECT u.*, GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as position_name
             FROM users u
             LEFT JOIN user_assignments ua ON u.id = ua.user_id AND ua.status = 'active'
             LEFT JOIN positions p ON ua.position_id = p.id
             WHERE u.gurmu_id = ? AND u.status = 'active'
             GROUP BY u.id
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