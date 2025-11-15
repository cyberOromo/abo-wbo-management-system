<?php
$currentPage = 'position-management';
?>

<!-- Modern Position Management Interface -->
<style>
    .position-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }
    
    .position-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    }
    
    .position-card.filled {
        border-left: 4px solid var(--primary-green);
    }
    
    .position-card.vacant {
        border-left: 4px solid #f59e0b;
    }
    
    .position-card.pending {
        border-left: 4px solid var(--primary-red);
    }
    
    .position-header {
        background: linear-gradient(135deg, #f8fafc 0%, white 100%);
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #e5e7eb;
        position: relative;
    }
    
    .position-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .position-level-badge {
        padding: 0.35rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .level-godina {
        background: rgba(45, 80, 22, 0.1);
        color: var(--primary-green);
        border: 1px solid rgba(45, 80, 22, 0.2);
    }
    
    .level-gamta {
        background: rgba(59, 130, 246, 0.1);
        color: #1e40af;
        border: 1px solid rgba(59, 130, 246, 0.2);
    }
    
    .level-gurmu {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }
    
    .level-executive {
        background: rgba(139, 21, 56, 0.1);
        color: var(--primary-red);
        border: 1px solid rgba(139, 21, 56, 0.2);
    }
    
    .position-status-badge {
        padding: 0.25rem 0.85rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-filled {
        background: rgba(45, 80, 22, 0.1);
        color: var(--primary-green);
        border: 1px solid rgba(45, 80, 22, 0.2);
    }
    
    .status-vacant {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }
    
    .status-pending {
        background: rgba(139, 21, 56, 0.1);
        color: var(--primary-red);
        border: 1px solid rgba(139, 21, 56, 0.2);
    }
    
    .status-suspended {
        background: rgba(107, 114, 128, 0.1);
        color: #374151;
        border: 1px solid rgba(107, 114, 128, 0.2);
    }
    
    .position-holder {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem 2rem;
    }
    
    .holder-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.5rem;
        border: 4px solid white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        position: relative;
    }
    
    .holder-avatar.vacant {
        background: linear-gradient(135deg, #d1d5db, #9ca3af);
        color: #374151;
    }
    
    .appointment-date {
        position: absolute;
        bottom: -8px;
        right: -8px;
        width: 24px;
        height: 24px;
        background: var(--primary-green);
        border-radius: 50%;
        border: 3px solid white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 700;
        color: white;
    }
    
    .position-responsibilities {
        padding: 0 2rem 1.5rem;
    }
    
    .responsibility-item {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        display: flex;
        justify-content-between;
        align-items: center;
        transition: all 0.2s ease;
    }
    
    .responsibility-item:hover {
        background: white;
        border-color: var(--primary-green);
    }
    
    .responsibility-text {
        font-size: 0.9rem;
        color: #374151;
    }
    
    .responsibility-priority {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    
    .priority-high {
        background: var(--primary-red);
    }
    
    .priority-medium {
        background: #f59e0b;
    }
    
    .priority-low {
        background: var(--primary-green);
    }
    
    .position-metrics {
        padding: 1.5rem 2rem;
        border-top: 1px solid #e5e7eb;
        background: linear-gradient(135deg, #f8fafc 0%, white 100%);
        border-radius: 0 0 16px 16px;
    }
    
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        text-align: center;
    }
    
    .metric-item {
        padding: 0.5rem;
    }
    
    .metric-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-green);
        margin-bottom: 0.25rem;
    }
    
    .metric-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .org-structure {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        margin-bottom: 2rem;
    }
    
    .structure-level {
        margin-bottom: 2rem;
        position: relative;
    }
    
    .structure-level::after {
        content: '';
        position: absolute;
        bottom: -1rem;
        left: 50%;
        width: 2px;
        height: 1rem;
        background: linear-gradient(to bottom, var(--primary-green), transparent);
        transform: translateX(-50%);
    }
    
    .structure-level:last-child::after {
        display: none;
    }
    
    .level-header {
        text-align: center;
        margin-bottom: 1.5rem;
    }
    
    .level-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }
    
    .level-positions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .create-position-btn {
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 25px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(45, 80, 22, 0.2);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .create-position-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(45, 80, 22, 0.3);
        color: white;
    }
    
    .position-hierarchy-tree {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        overflow-x: auto;
    }
    
    .tree-node {
        position: relative;
        text-align: center;
        margin: 1rem;
        padding: 1rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
        min-width: 200px;
    }
    
    .tree-node:hover {
        border-color: var(--primary-green);
        box-shadow: 0 4px 12px rgba(45, 80, 22, 0.15);
    }
    
    .tree-children {
        display: flex;
        justify-content: center;
        gap: 2rem;
        margin-top: 2rem;
        flex-wrap: wrap;
    }
    
    .position-search-bar {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        border: 1px solid #e5e7eb;
    }
    
    .assignments-timeline {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }
    
    .assignment-item {
        display: flex;
        align-items: start;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .assignment-item:last-child {
        border-bottom: none;
    }
    
    .assignment-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-green), var(--primary-green-light));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    
    .assignment-content {
        flex-grow: 1;
    }
    
    .assignment-title {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .assignment-description {
        color: #6b7280;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }
    
    .assignment-timestamp {
        color: #9ca3af;
        font-size: 0.75rem;
    }
    
    .position-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .position-stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }
    
    .position-stat-card:hover {
        border-color: var(--primary-green);
        box-shadow: 0 4px 12px rgba(45, 80, 22, 0.15);
    }
</style>

<div class="page-header">
    <h1 class="page-title">Position Management</h1>
    <p class="page-description">Define and manage organizational positions with hierarchical structure and responsibility assignments</p>
</div>

<!-- Position Statistics -->
<div class="position-stats-grid">
    <div class="position-stat-card">
        <div class="stats-number" style="color: var(--primary-green);"><?= $position_stats['total_positions'] ?? 0 ?></div>
        <div class="text-muted fw-500">Total Positions</div>
    </div>
    <div class="position-stat-card">
        <div class="stats-number" style="color: #10b981;"><?= $position_stats['filled_positions'] ?? 0 ?></div>
        <div class="text-muted fw-500">Filled</div>
    </div>
    <div class="position-stat-card">
        <div class="stats-number" style="color: #f59e0b;"><?= $position_stats['vacant_positions'] ?? 0 ?></div>
        <div class="text-muted fw-500">Vacant</div>
    </div>
    <div class="position-stat-card">
        <div class="stats-number" style="color: var(--primary-red);"><?= $position_stats['pending_assignments'] ?? 0 ?></div>
        <div class="text-muted fw-500">Pending</div>
    </div>
    <div class="position-stat-card">
        <div class="stats-number" style="color: #3b82f6;"><?= $position_stats['executive_positions'] ?? 0 ?></div>
        <div class="text-muted fw-500">Executive</div>
    </div>
    <div class="position-stat-card">
        <div class="stats-number" style="color: #8b5cf6;"><?= $position_stats['leadership_positions'] ?? 0 ?></div>
        <div class="text-muted fw-500">Leadership</div>
    </div>
</div>

<!-- Search and Control Panel -->
<div class="position-search-bar">
    <div class="row align-items-center g-3">
        <div class="col-md-4">
            <div class="view-toggle">
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="viewMode" id="cardView" autocomplete="off" checked>
                    <label class="btn btn-outline-secondary" for="cardView">
                        <i class="bi bi-grid-3x2-gap"></i> Cards
                    </label>
                    
                    <input type="radio" class="btn-check" name="viewMode" id="structureView" autocomplete="off">
                    <label class="btn btn-outline-secondary" for="structureView">
                        <i class="bi bi-diagram-3"></i> Structure
                    </label>
                    
                    <input type="radio" class="btn-check" name="viewMode" id="treeView" autocomplete="off">
                    <label class="btn btn-outline-secondary" for="treeView">
                        <i class="bi bi-tree"></i> Tree
                    </label>
                </div>
            </div>
        </div>
        
        <div class="col-md-5">
            <div class="d-flex gap-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="positionSearch" placeholder="Search positions, holders, responsibilities...">
                </div>
                
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="filled">✅ Filled</option>
                    <option value="vacant">⚠️ Vacant</option>
                    <option value="pending">⏳ Pending</option>
                    <option value="suspended">💤 Suspended</option>
                </select>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="d-flex gap-2 justify-content-end">
                <?php if ($can_create ?? true): ?>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-plus-circle"></i> Create
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" onclick="showCreatePositionModal()">
                                <i class="bi bi-person-badge"></i> New Position
                            </a></li>
                            <li><a class="dropdown-item" onclick="showAssignmentModal()">
                                <i class="bi bi-person-gear"></i> Assign Position
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" onclick="showBulkAssignModal()">
                                <i class="bi bi-people"></i> Bulk Assignment
                            </a></li>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-gear"></i> Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/positions/export?format=pdf">
                            <i class="bi bi-file-pdf"></i> Export Chart
                        </a></li>
                        <li><a class="dropdown-item" href="/positions/export?format=excel">
                            <i class="bi bi-file-excel"></i> Export Data
                        </a></li>
                        <li><a class="dropdown-item" href="/positions/vacancy-report">
                            <i class="bi bi-clipboard-data"></i> Vacancy Report
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" onclick="syncWithHierarchy()">
                            <i class="bi bi-arrow-repeat"></i> Sync with Hierarchy
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card View (Default) -->
<div id="cardViewContainer">
    <div class="row g-4" id="positionsGrid">
        <?php if (!empty($positions)): ?>
            <?php foreach ($positions as $position): ?>
                <div class="col-xl-4 col-lg-6 col-md-6 position-item" 
                     data-status="<?= $position['status'] ?? 'vacant' ?>" 
                     data-level="<?= $position['level'] ?? 'gurmu' ?>">
                    <div class="position-card <?= $position['status'] ?? 'vacant' ?>">
                        <!-- Position Header -->
                        <div class="position-header">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="position-title">
                                        <i class="bi bi-person-badge"></i>
                                        <?= htmlspecialchars($position['title'] ?? 'Position Title') ?>
                                    </div>
                                    <div class="position-level-badge level-<?= $position['level'] ?? 'gurmu' ?>">
                                        <?= getPositionLevelIcon($position['level'] ?? 'gurmu') ?> 
                                        <?= ucfirst($position['level'] ?? 'gurmu') ?> Level
                                    </div>
                                </div>
                                
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-ghost" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="/positions/<?= $position['id'] ?>">
                                            <i class="bi bi-eye"></i> View Details
                                        </a></li>
                                        <li><a class="dropdown-item" href="/positions/<?= $position['id'] ?>/edit">
                                            <i class="bi bi-pencil"></i> Edit Position
                                        </a></li>
                                        <li><a class="dropdown-item" onclick="assignPosition(<?= $position['id'] ?>)">
                                            <i class="bi bi-person-gear"></i> Assign/Change Holder
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="/positions/<?= $position['id'] ?>/responsibilities">
                                            <i class="bi bi-list-check"></i> Manage Responsibilities
                                        </a></li>
                                        <?php if (($position['status'] ?? 'vacant') === 'filled'): ?>
                                            <li><a class="dropdown-item text-warning" onclick="vacatePosition(<?= $position['id'] ?>)">
                                                <i class="bi bi-person-x"></i> Vacate Position
                                            </a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="position-status-badge status-<?= $position['status'] ?? 'vacant' ?>">
                                <?= getPositionStatusIcon($position['status'] ?? 'vacant') ?> 
                                <?= ucfirst($position['status'] ?? 'vacant') ?>
                            </div>
                        </div>
                        
                        <!-- Position Holder -->
                        <div class="position-holder">
                            <?php if (($position['status'] ?? 'vacant') === 'filled'): ?>
                                <div class="holder-avatar">
                                    <?= strtoupper(substr($position['holder_name'] ?? 'U', 0, 1)) ?>
                                    <div class="appointment-date" title="Appointed <?= date('M Y', strtotime($position['appointed_at'] ?? '')) ?>">
                                        <?= date('j', strtotime($position['appointed_at'] ?? '')) ?>
                                    </div>
                                </div>
                                
                                <div class="flex-grow-1">
                                    <h6 class="fw-600 mb-1"><?= htmlspecialchars($position['holder_name'] ?? 'Unknown') ?></h6>
                                    <div class="text-muted mb-2"><?= htmlspecialchars($position['holder_email'] ?? 'No email') ?></div>
                                    <div class="d-flex gap-2 align-items-center">
                                        <small class="text-muted">Appointed:</small>
                                        <small class="fw-500"><?= date('M j, Y', strtotime($position['appointed_at'] ?? '')) ?></small>
                                    </div>
                                    <div class="d-flex gap-2 align-items-center">
                                        <small class="text-muted">Term:</small>
                                        <small class="fw-500"><?= htmlspecialchars($position['term_length'] ?? 'Indefinite') ?></small>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="holder-avatar vacant">
                                    <i class="bi bi-person-x"></i>
                                </div>
                                
                                <div class="flex-grow-1">
                                    <h6 class="fw-600 mb-1 text-muted">Position Vacant</h6>
                                    <div class="text-muted mb-2">No current assignment</div>
                                    <button class="btn btn-sm btn-outline-primary" onclick="assignPosition(<?= $position['id'] ?>)">
                                        <i class="bi bi-person-gear"></i> Assign Holder
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Position Responsibilities -->
                        <div class="position-responsibilities">
                            <h6 class="fw-600 mb-3">📋 Key Responsibilities</h6>
                            <?php 
                            $responsibilities = json_decode($position['responsibilities'] ?? '[]', true);
                            if (!empty($responsibilities)): 
                                foreach (array_slice($responsibilities, 0, 3) as $responsibility): 
                            ?>
                                <div class="responsibility-item">
                                    <span class="responsibility-text"><?= htmlspecialchars($responsibility['text'] ?? 'Responsibility') ?></span>
                                    <div class="responsibility-priority priority-<?= $responsibility['priority'] ?? 'medium' ?>"></div>
                                </div>
                            <?php 
                                endforeach;
                                if (count($responsibilities) > 3):
                            ?>
                                <div class="text-center mt-2">
                                    <small class="text-muted">+<?= count($responsibilities) - 3 ?> more responsibilities</small>
                                </div>
                            <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-2 text-muted">
                                    <i class="bi bi-list-check"></i>
                                    <div>No responsibilities defined</div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Position Metrics -->
                        <div class="position-metrics">
                            <div class="metrics-grid">
                                <div class="metric-item">
                                    <div class="metric-value"><?= $position['task_count'] ?? 0 ?></div>
                                    <div class="metric-label">Tasks</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value"><?= $position['report_count'] ?? 0 ?></div>
                                    <div class="metric-label">Reports</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value"><?= $position['meetings_count'] ?? 0 ?></div>
                                    <div class="metric-label">Meetings</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-person-badge" style="font-size: 4rem; color: var(--gray-400);"></i>
                    </div>
                    <h4 class="text-muted mb-2">No Positions Defined</h4>
                    <p class="text-muted mb-4">Create organizational positions to build your leadership structure</p>
                    <?php if ($can_create ?? true): ?>
                        <button class="create-position-btn" onclick="showCreatePositionModal()">
                            <i class="bi bi-plus-circle"></i> Create First Position
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Structure View (Hidden by default) -->
<div id="structureViewContainer" style="display: none;">
    <div class="org-structure">
        <!-- Executive Level -->
        <div class="structure-level">
            <div class="level-header">
                <div class="level-title">
                    <span class="position-level-badge level-executive">🎭 Executive Level</span>
                </div>
            </div>
            <div class="level-positions">
                <?php if (!empty($executive_positions ?? [])): ?>
                    <?php foreach ($executive_positions as $position): ?>
                        <div class="position-card <?= $position['status'] ?? 'vacant' ?>">
                            <div class="position-header">
                                <div class="position-title">
                                    <i class="bi bi-star"></i>
                                    <?= htmlspecialchars($position['title']) ?>
                                </div>
                                <div class="position-status-badge status-<?= $position['status'] ?? 'vacant' ?>">
                                    <?= ucfirst($position['status'] ?? 'vacant') ?>
                                </div>
                            </div>
                            <div class="position-holder" style="min-height: 100px;">
                                <?php if (($position['status'] ?? 'vacant') === 'filled'): ?>
                                    <div class="holder-avatar">
                                        <?= strtoupper(substr($position['holder_name'] ?? 'E', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h6 class="fw-600"><?= htmlspecialchars($position['holder_name'] ?? 'Executive') ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($position['department'] ?? 'Executive') ?></small>
                                    </div>
                                <?php else: ?>
                                    <div class="holder-avatar vacant">
                                        <i class="bi bi-person-x"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-600 text-muted">Vacant</h6>
                                        <small class="text-muted">Needs assignment</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Godina Level -->
        <div class="structure-level">
            <div class="level-header">
                <div class="level-title">
                    <span class="position-level-badge level-godina">🏛️ Godina Level</span>
                </div>
            </div>
            <div class="level-positions">
                <?php if (!empty($godina_positions ?? [])): ?>
                    <?php foreach ($godina_positions as $position): ?>
                        <div class="position-card <?= $position['status'] ?? 'vacant' ?>">
                            <div class="position-header">
                                <div class="position-title">
                                    <i class="bi bi-building"></i>
                                    <?= htmlspecialchars($position['title']) ?>
                                </div>
                                <div class="position-status-badge status-<?= $position['status'] ?? 'vacant' ?>">
                                    <?= ucfirst($position['status'] ?? 'vacant') ?>
                                </div>
                            </div>
                            <div class="position-holder">
                                <?php if (($position['status'] ?? 'vacant') === 'filled'): ?>
                                    <div class="holder-avatar">
                                        <?= strtoupper(substr($position['holder_name'] ?? 'G', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h6 class="fw-600"><?= htmlspecialchars($position['holder_name'] ?? 'Godina Leader') ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($position['unit_name'] ?? 'Godina Unit') ?></small>
                                    </div>
                                <?php else: ?>
                                    <div class="holder-avatar vacant">
                                        <i class="bi bi-person-x"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-600 text-muted">Vacant</h6>
                                        <small class="text-muted"><?= htmlspecialchars($position['unit_name'] ?? 'Godina Unit') ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Gamta Level -->
        <div class="structure-level">
            <div class="level-header">
                <div class="level-title">
                    <span class="position-level-badge level-gamta">🏢 Gamta Level</span>
                </div>
            </div>
            <div class="level-positions">
                <?php if (!empty($gamta_positions ?? [])): ?>
                    <?php foreach (array_slice($gamta_positions, 0, 6) as $position): ?>
                        <div class="position-card <?= $position['status'] ?? 'vacant' ?>">
                            <div class="position-header">
                                <div class="position-title">
                                    <i class="bi bi-people"></i>
                                    <?= htmlspecialchars($position['title']) ?>
                                </div>
                                <div class="position-status-badge status-<?= $position['status'] ?? 'vacant' ?>">
                                    <?= ucfirst($position['status'] ?? 'vacant') ?>
                                </div>
                            </div>
                            <div class="position-holder">
                                <?php if (($position['status'] ?? 'vacant') === 'filled'): ?>
                                    <div class="holder-avatar">
                                        <?= strtoupper(substr($position['holder_name'] ?? 'M', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h6 class="fw-600"><?= htmlspecialchars($position['holder_name'] ?? 'Gamta Leader') ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($position['unit_name'] ?? 'Gamta Unit') ?></small>
                                    </div>
                                <?php else: ?>
                                    <div class="holder-avatar vacant">
                                        <i class="bi bi-person-x"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-600 text-muted">Vacant</h6>
                                        <small class="text-muted"><?= htmlspecialchars($position['unit_name'] ?? 'Gamta Unit') ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Gurmu Level -->
        <div class="structure-level">
            <div class="level-header">
                <div class="level-title">
                    <span class="position-level-badge level-gurmu">👥 Gurmu Level</span>
                </div>
            </div>
            <div class="level-positions">
                <?php if (!empty($gurmu_positions ?? [])): ?>
                    <?php foreach (array_slice($gurmu_positions, 0, 9) as $position): ?>
                        <div class="position-card <?= $position['status'] ?? 'vacant' ?>">
                            <div class="position-header">
                                <div class="position-title">
                                    <i class="bi bi-person-lines-fill"></i>
                                    <?= htmlspecialchars($position['title']) ?>
                                </div>
                                <div class="position-status-badge status-<?= $position['status'] ?? 'vacant' ?>">
                                    <?= ucfirst($position['status'] ?? 'vacant') ?>
                                </div>
                            </div>
                            <div class="position-holder">
                                <?php if (($position['status'] ?? 'vacant') === 'filled'): ?>
                                    <div class="holder-avatar" style="width: 48px; height: 48px; font-size: 1.1rem;">
                                        <?= strtoupper(substr($position['holder_name'] ?? 'M', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h6 class="fw-600"><?= htmlspecialchars($position['holder_name'] ?? 'Gurmu Member') ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($position['unit_name'] ?? 'Gurmu Unit') ?></small>
                                    </div>
                                <?php else: ?>
                                    <div class="holder-avatar vacant" style="width: 48px; height: 48px; font-size: 1rem;">
                                        <i class="bi bi-person-x"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-600 text-muted">Vacant</h6>
                                        <small class="text-muted"><?= htmlspecialchars($position['unit_name'] ?? 'Gurmu Unit') ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Tree View (Hidden by default) -->
<div id="treeViewContainer" style="display: none;">
    <div class="position-hierarchy-tree">
        <div class="level-header">
            <div class="level-title">
                <i class="bi bi-diagram-3"></i>
                Organizational Position Hierarchy
            </div>
            <p class="text-muted">Interactive tree view of all positions and their relationships</p>
        </div>
        
        <div class="tree-node">
            <h5 class="fw-700 mb-2">Executive Leadership</h5>
            <span class="position-level-badge level-executive">🎭 Executive</span>
        </div>
        
        <div class="tree-children">
            <div class="tree-node">
                <h6 class="fw-600 mb-2">Godina Positions</h6>
                <span class="position-level-badge level-godina">🏛️ Godina</span>
                <div class="mt-2">
                    <small class="text-muted"><?= count($godina_positions ?? []) ?> positions</small>
                </div>
                
                <div class="tree-children">
                    <div class="tree-node">
                        <h6 class="fw-500 mb-2">Gamta Positions</h6>
                        <span class="position-level-badge level-gamta">🏢 Gamta</span>
                        <div class="mt-2">
                            <small class="text-muted"><?= count($gamta_positions ?? []) ?> positions</small>
                        </div>
                        
                        <div class="tree-children">
                            <div class="tree-node">
                                <h6 class="fw-500 mb-2">Gurmu Positions</h6>
                                <span class="position-level-badge level-gurmu">👥 Gurmu</span>
                                <div class="mt-2">
                                    <small class="text-muted"><?= count($gurmu_positions ?? []) ?> positions</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Position Activities Panel -->
<div class="row g-4 mt-4">
    <div class="col-md-8">
        <div class="assignments-timeline">
            <h5 class="mb-4 fw-600">🔄 Recent Position Activities</h5>
            <?php if (!empty($recent_assignments ?? [])): ?>
                <?php foreach (array_slice($recent_assignments, 0, 8) as $assignment): ?>
                    <div class="assignment-item">
                        <div class="assignment-icon">
                            <i class="bi bi-<?= getAssignmentIcon($assignment['type'] ?? 'person-gear') ?>"></i>
                        </div>
                        
                        <div class="assignment-content">
                            <div class="assignment-title">
                                <?= htmlspecialchars($assignment['title'] ?? 'Position Assignment') ?>
                            </div>
                            <div class="assignment-description">
                                <?= htmlspecialchars($assignment['description'] ?? 'Position activity occurred') ?>
                            </div>
                            <div class="assignment-timestamp">
                                <?= date('M j, Y g:i A', strtotime($assignment['created_at'] ?? '')) ?>
                            </div>
                        </div>
                        
                        <div class="position-status-badge status-<?= $assignment['status'] ?? 'filled' ?>">
                            <?= ucfirst($assignment['status'] ?? 'filled') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                    <div class="mt-2">No recent position activities</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="assignments-timeline">
            <h5 class="mb-4 fw-600">📊 Position Insights</h5>
            
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Fill Rate</span>
                    <span class="fw-600"><?= ($position_metrics['fill_rate'] ?? 72) ?>%</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-success" style="width: <?= ($position_metrics['fill_rate'] ?? 72) ?>%"></div>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Assignment Efficiency</span>
                    <span class="fw-600"><?= ($position_metrics['assignment_efficiency'] ?? 84) ?>%</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-info" style="width: <?= ($position_metrics['assignment_efficiency'] ?? 84) ?>%"></div>
                </div>
            </div>
            
            <div class="row g-3">
                <div class="col-6">
                    <div class="text-center p-2">
                        <div class="fw-600" style="color: var(--primary-green);"><?= $position_metrics['active_assignments'] ?? 45 ?></div>
                        <small class="text-muted">Active Assignments</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center p-2">
                        <div class="fw-600" style="color: #f59e0b;"><?= $position_metrics['pending_assignments'] ?? 7 ?></div>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center p-2">
                        <div class="fw-600" style="color: #8b5cf6;"><?= $position_metrics['leadership_roles'] ?? 18 ?></div>
                        <small class="text-muted">Leadership</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center p-2">
                        <div class="fw-600" style="color: #3b82f6;"><?= $position_metrics['operational_roles'] ?? 27 ?></div>
                        <small class="text-muted">Operational</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Position Modal -->
<div class="modal fade" id="createPositionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Position</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/positions/create">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-500">Position Title *</label>
                            <input type="text" name="title" class="form-control" required placeholder="Enter position title">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Level *</label>
                            <select name="level" class="form-select" required>
                                <option value="">Select level</option>
                                <option value="executive">🎭 Executive</option>
                                <option value="godina">🏛️ Godina</option>
                                <option value="gamta">🏢 Gamta</option>
                                <option value="gurmu">👥 Gurmu</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-500">Description</label>
                            <textarea name="description" class="form-control" rows="3" 
                                      placeholder="Describe the position's role, objectives, and scope..."></textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Department</label>
                            <select name="department" class="form-select">
                                <option value="">Select department</option>
                                <option value="administration">Administration</option>
                                <option value="finance">Finance</option>
                                <option value="operations">Operations</option>
                                <option value="community">Community Affairs</option>
                                <option value="governance">Governance</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-500">Organizational Unit</label>
                            <select name="unit_id" class="form-select">
                                <option value="">Select unit</option>
                                <!-- Populated via AJAX based on level selection -->
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Term Length</label>
                            <select name="term_length" class="form-select">
                                <option value="indefinite">Indefinite</option>
                                <option value="1_year">1 Year</option>
                                <option value="2_years">2 Years</option>
                                <option value="3_years">3 Years</option>
                                <option value="4_years">4 Years</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Reports To</label>
                            <select name="reports_to" class="form-select">
                                <option value="">No reporting relationship</option>
                                <!-- Populated with existing positions -->
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-500">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="normal">Normal</option>
                                <option value="high">High Priority</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-500">Key Responsibilities</label>
                            <div class="responsibilities-input">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" name="responsibilities[]" placeholder="Enter responsibility">
                                    <select class="form-select" name="responsibility_priorities[]" style="max-width: 120px;">
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-danger" onclick="removeResponsibility(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addResponsibility()">
                                <i class="bi bi-plus"></i> Add Responsibility
                            </button>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_leadership" value="1" id="isLeadership">
                                <label class="form-check-label fw-500" for="isLeadership">
                                    This is a leadership position
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Position
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View switching functionality
    const cardView = document.getElementById('cardView');
    const structureView = document.getElementById('structureView');
    const treeView = document.getElementById('treeView');
    const cardContainer = document.getElementById('cardViewContainer');
    const structureContainer = document.getElementById('structureViewContainer');
    const treeContainer = document.getElementById('treeViewContainer');
    
    cardView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'block';
            structureContainer.style.display = 'none';
            treeContainer.style.display = 'none';
        }
    });
    
    structureView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'none';
            structureContainer.style.display = 'block';
            treeContainer.style.display = 'none';
        }
    });
    
    treeView.addEventListener('change', function() {
        if (this.checked) {
            cardContainer.style.display = 'none';
            structureContainer.style.display = 'none';
            treeContainer.style.display = 'block';
        }
    });
    
    // Search and filter functionality
    const searchInput = document.getElementById('positionSearch');
    const statusFilter = document.getElementById('statusFilter');
    
    function applyFilters() {
        const searchValue = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        
        document.querySelectorAll('.position-item, .position-card').forEach(item => {
            const text = item.textContent.toLowerCase();
            const showSearch = !searchValue || text.includes(searchValue);
            const showStatus = !statusValue || item.dataset.status === statusValue;
            
            item.style.display = showSearch && showStatus ? 'block' : 'none';
        });
    }
    
    searchInput.addEventListener('input', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
});

function showCreatePositionModal() {
    new bootstrap.Modal(document.getElementById('createPositionModal')).show();
}

function addResponsibility() {
    const container = document.querySelector('.responsibilities-input');
    const newInput = document.createElement('div');
    newInput.className = 'input-group mb-2';
    newInput.innerHTML = `
        <input type="text" class="form-control" name="responsibilities[]" placeholder="Enter responsibility">
        <select class="form-select" name="responsibility_priorities[]" style="max-width: 120px;">
            <option value="low">Low</option>
            <option value="medium" selected>Medium</option>
            <option value="high">High</option>
        </select>
        <button type="button" class="btn btn-outline-danger" onclick="removeResponsibility(this)">
            <i class="bi bi-trash"></i>
        </button>
    `;
    container.appendChild(newInput);
}

function removeResponsibility(button) {
    button.closest('.input-group').remove();
}

function assignPosition(positionId) {
    // Would show assignment modal
    alert('Position assignment modal would open here');
}

function vacatePosition(positionId) {
    if (confirm('Vacate this position? The current holder will be removed.')) {
        fetch(`/positions/${positionId}/vacate`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.ok ? location.reload() : alert('Error vacating position'))
        .catch(() => alert('Error vacating position'));
    }
}

function syncWithHierarchy() {
    if (confirm('Sync positions with organizational hierarchy? This will update position assignments.')) {
        fetch('/positions/sync-hierarchy', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.ok ? location.reload() : alert('Error syncing with hierarchy'))
        .catch(() => alert('Error syncing with hierarchy'));
    }
}
</script>

<?php
// Helper functions for UI
function getPositionLevelIcon($level) {
    return [
        'executive' => '🎭',
        'godina' => '🏛️',
        'gamta' => '🏢',
        'gurmu' => '👥'
    ][$level] ?? '👥';
}

function getPositionStatusIcon($status) {
    return [
        'filled' => '✅',
        'vacant' => '⚠️',
        'pending' => '⏳',
        'suspended' => '💤'
    ][$status] ?? '⚠️';
}

function getAssignmentIcon($type) {
    return [
        'assignment' => 'person-gear',
        'promotion' => 'arrow-up-circle',
        'transfer' => 'arrow-left-right',
        'termination' => 'person-x',
        'appointment' => 'person-check'
    ][$type] ?? 'person-gear';
}
?>