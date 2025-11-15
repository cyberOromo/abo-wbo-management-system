<?php
$pageTitle = $title ?? 'Hierarchy Tree View';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/dashboard'],
    ['title' => 'Hierarchy', 'url' => '/hierarchy'],
    ['title' => 'Tree View', 'url' => '/hierarchy/tree', 'active' => true]
];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-diagram-2 me-2"></i>
        Organizational Hierarchy Tree
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-outline-secondary" id="expandAllBtn">
                <i class="bi bi-arrows-expand me-1"></i>
                Expand All
            </button>
            <button type="button" class="btn btn-outline-secondary" id="collapseAllBtn">
                <i class="bi bi-arrows-collapse me-1"></i>
                Collapse All
            </button>
        </div>
        <div class="btn-group me-2">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-funnel me-1"></i>
                Filter
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" data-filter="all">Show All</a></li>
                <li><a class="dropdown-item" href="#" data-filter="active">Active Only</a></li>
                <li><a class="dropdown-item" href="#" data-filter="inactive">Inactive Only</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" data-filter="with-users">With Users</a></li>
                <li><a class="dropdown-item" href="#" data-filter="empty">Empty Units</a></li>
            </ul>
        </div>
        <div class="btn-group">
            <a href="/hierarchy" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Overview
            </a>
        </div>
    </div>
</div>

<!-- Tree Controls -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search hierarchy...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end gap-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="showUserCount" checked>
                                <label class="form-check-label" for="showUserCount">
                                    Show User Count
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enableDragDrop">
                                <label class="form-check-label" for="enableDragDrop">
                                    Enable Drag & Drop
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hierarchy Tree -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-diagram-3 me-2"></i>
                    Interactive Hierarchy Tree
                </h5>
            </div>
            <div class="card-body">
                <!-- Loading State -->
                <div id="loading-state" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading organizational hierarchy...</p>
                </div>
                
                <!-- Error State -->
                <div id="error-state" class="text-center py-5" style="display: none;">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    <h4 class="text-danger mt-3">Failed to Load Hierarchy</h4>
                    <p class="text-muted">There was an error loading the organizational hierarchy.</p>
                    <button class="btn btn-outline-primary" onclick="loadHierarchyTree()">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Try Again
                    </button>
                </div>
                
                <!-- Empty State -->
                <div id="empty-state" class="text-center py-5" style="display: none;">
                    <i class="bi bi-diagram-3 text-muted" style="font-size: 3rem;"></i>
                    <h4 class="text-muted mt-3">No Hierarchy Data</h4>
                    <p class="text-muted">No organizational units have been created yet.</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="/hierarchy/create?type=godina" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>
                            Create First Godina
                        </a>
                    </div>
                </div>
                
                <!-- Tree Container -->
                <div id="hierarchy-tree" style="display: none;">
                    <!-- Tree content will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Legend -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Legend</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <div class="legend-item godina-legend me-3"></div>
                            <span><i class="bi bi-globe me-1"></i> Godina (Regional Unit)</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="legend-item gamta-legend me-3"></div>
                            <span><i class="bi bi-house me-1"></i> Gamta (Local Unit)</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success me-2">Active</span>
                            <span class="text-muted">Unit is active and operational</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-secondary me-2">Inactive</span>
                            <span class="text-muted">Unit is temporarily inactive</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Context Menu -->
<div class="context-menu" id="contextMenu" style="display: none;">
    <ul class="list-unstyled mb-0">
        <li><a href="#" class="context-item" data-action="view">
            <i class="bi bi-eye me-2"></i>View Details
        </a></li>
        <li><a href="#" class="context-item" data-action="edit">
            <i class="bi bi-pencil me-2"></i>Edit
        </a></li>
        <li><hr class="my-1"></li>
        <li><a href="#" class="context-item" data-action="add-gamta">
            <i class="bi bi-plus me-2"></i>Add Gamta
        </a></li>
        <li><a href="#" class="context-item" data-action="assign-users">
            <i class="bi bi-people me-2"></i>Assign Users
        </a></li>
        <li><hr class="my-1"></li>
        <li><a href="#" class="context-item text-danger" data-action="delete">
            <i class="bi bi-trash me-2"></i>Delete
        </a></li>
    </ul>
</div>

<style>
.hierarchy-tree {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.tree-node {
    margin: 0.5rem 0;
    position: relative;
}

.node-content {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
}

.node-content:hover {
    transform: translateX(3px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.godina-node .node-content {
    background-color: #e3f2fd;
    border-left: 4px solid #2196f3;
    font-weight: 600;
}

.gamta-node .node-content {
    background-color: #f3e5f5;
    border-left: 4px solid #9c27b0;
    margin-left: 2rem;
}

.node-content.active {
    border-left-color: #4caf50;
}

.node-content.inactive {
    opacity: 0.6;
    border-left-color: #9e9e9e;
}

.node-toggle {
    width: 20px;
    height: 20px;
    border: none;
    background: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.5rem;
    border-radius: 50%;
    transition: background-color 0.2s;
}

.node-toggle:hover {
    background-color: rgba(0,0,0,0.1);
}

.node-info {
    flex-grow: 1;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.node-title {
    font-weight: 500;
    margin: 0;
}

.node-subtitle {
    font-size: 0.875rem;
    color: #6c757d;
    margin: 0;
}

.node-badges {
    display: flex;
    gap: 0.25rem;
    align-items: center;
}

.node-actions {
    display: flex;
    gap: 0.25rem;
    opacity: 0;
    transition: opacity 0.2s;
}

.node-content:hover .node-actions {
    opacity: 1;
}

.node-children {
    margin-top: 0.5rem;
    margin-left: 1rem;
    border-left: 2px dotted #dee2e6;
    padding-left: 1rem;
}

.node-children.collapsed {
    display: none;
}

.legend-item {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    display: inline-block;
}

.godina-legend {
    background-color: #e3f2fd;
    border-left: 4px solid #2196f3;
}

.gamta-legend {
    background-color: #f3e5f5;
    border-left: 4px solid #9c27b0;
}

.context-menu {
    position: fixed;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    z-index: 1050;
    min-width: 160px;
    padding: 0.5rem 0;
}

.context-item {
    display: block;
    padding: 0.375rem 1rem;
    color: #212529;
    text-decoration: none;
    transition: background-color 0.2s;
}

.context-item:hover {
    background-color: #f8f9fa;
    color: #212529;
}

.tree-node.highlighted .node-content {
    background-color: #fff3cd !important;
    border-left-color: #ffc107 !important;
}

.tree-node.drag-over .node-content {
    background-color: #d1ecf1 !important;
    border-left-color: #17a2b8 !important;
}

/* Animations */
@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
    }
    to {
        opacity: 1;
        max-height: 500px;
    }
}

.node-children {
    animation: slideDown 0.3s ease-out;
}
</style>

<script>
let hierarchyData = [];
let filteredData = [];
let currentFilter = 'all';
let showUserCount = true;
let enableDragDrop = false;
let contextMenuTarget = null;

document.addEventListener('DOMContentLoaded', function() {
    loadHierarchyTree();
    initializeEventListeners();
});

function initializeEventListeners() {
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', handleSearch);
    document.getElementById('clearSearch').addEventListener('click', clearSearch);
    
    // Control buttons
    document.getElementById('expandAllBtn').addEventListener('click', expandAll);
    document.getElementById('collapseAllBtn').addEventListener('click', collapseAll);
    
    // Settings toggles
    document.getElementById('showUserCount').addEventListener('change', toggleUserCount);
    document.getElementById('enableDragDrop').addEventListener('change', toggleDragDrop);
    
    // Filter options
    document.querySelectorAll('[data-filter]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            setFilter(this.dataset.filter);
        });
    });
    
    // Context menu
    document.addEventListener('click', hideContextMenu);
    document.addEventListener('contextmenu', function(e) {
        if (e.target.closest('.node-content')) {
            e.preventDefault();
            showContextMenu(e, e.target.closest('.tree-node'));
        }
    });
}

function loadHierarchyTree() {
    showLoadingState();
    
    fetch('/hierarchy/tree/data')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hierarchyData = data.data;
                filteredData = [...hierarchyData];
                renderHierarchyTree();
                hideLoadingState();
            } else {
                showErrorState();
            }
        })
        .catch(error => {
            console.error('Error loading hierarchy:', error);
            showErrorState();
        });
}

function showLoadingState() {
    document.getElementById('loading-state').style.display = 'block';
    document.getElementById('error-state').style.display = 'none';
    document.getElementById('empty-state').style.display = 'none';
    document.getElementById('hierarchy-tree').style.display = 'none';
}

function showErrorState() {
    document.getElementById('loading-state').style.display = 'none';
    document.getElementById('error-state').style.display = 'block';
    document.getElementById('empty-state').style.display = 'none';
    document.getElementById('hierarchy-tree').style.display = 'none';
}

function showEmptyState() {
    document.getElementById('loading-state').style.display = 'none';
    document.getElementById('error-state').style.display = 'none';
    document.getElementById('empty-state').style.display = 'block';
    document.getElementById('hierarchy-tree').style.display = 'none';
}

function hideLoadingState() {
    document.getElementById('loading-state').style.display = 'none';
    document.getElementById('error-state').style.display = 'none';
    
    if (hierarchyData.length === 0) {
        showEmptyState();
    } else {
        document.getElementById('empty-state').style.display = 'none';
        document.getElementById('hierarchy-tree').style.display = 'block';
    }
}

function renderHierarchyTree() {
    const container = document.getElementById('hierarchy-tree');
    
    if (filteredData.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-muted">No hierarchy units match the current filter.</div>';
        return;
    }
    
    let html = '<div class="hierarchy-tree">';
    
    filteredData.forEach(godina => {
        html += renderGodinaNode(godina);
    });
    
    html += '</div>';
    container.innerHTML = html;
    
    // Add event listeners to tree nodes
    addTreeEventListeners();
}

function renderGodinaNode(godina) {
    const hasChildren = godina.children && godina.children.length > 0;
    const userCount = hasChildren ? godina.children.reduce((sum, gamta) => sum + (parseInt(gamta.user_count) || 0), 0) : 0;
    
    let html = `
        <div class="tree-node godina-node" data-id="${godina.id}" data-type="godina">
            <div class="node-content ${godina.status}" ${enableDragDrop ? 'draggable="true"' : ''}>
                ${hasChildren ? `<button class="node-toggle" data-target="godina-${godina.id}">
                    <i class="bi bi-chevron-down"></i>
                </button>` : '<div style="width: 20px;"></div>'}
                
                <div class="node-info">
                    <i class="bi bi-globe text-primary me-2"></i>
                    <div>
                        <div class="node-title">${godina.name}</div>
                        <div class="node-subtitle">${godina.code} • ${godina.location || 'No location'}</div>
                    </div>
                </div>
                
                <div class="node-badges">
                    <span class="badge ${godina.status === 'active' ? 'bg-success' : 'bg-secondary'}">${godina.status}</span>
                    ${showUserCount ? `<span class="badge bg-info">${userCount} users</span>` : ''}
                    ${hasChildren ? `<span class="badge bg-primary">${godina.children.length} gamtas</span>` : ''}
                </div>
                
                <div class="node-actions">
                    <a href="/hierarchy/${godina.id}?type=godina" class="btn btn-sm btn-outline-primary" title="View Details">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="/hierarchy/${godina.id}/edit?type=godina" class="btn btn-sm btn-outline-warning" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
            </div>
    `;
    
    if (hasChildren) {
        html += `<div class="node-children" id="godina-${godina.id}">`;
        godina.children.forEach(gamta => {
            html += renderGamtaNode(gamta);
        });
        html += '</div>';
    }
    
    html += '</div>';
    return html;
}

function renderGamtaNode(gamta) {
    const hasGurmus = gamta.gurmus && gamta.gurmus.length > 0;
    const gurmusId = `gurmus-${gamta.id}`;
    
    let html = `
        <div class="tree-node gamta-node" data-id="${gamta.id}" data-type="gamta">
            <div class="node-content ${gamta.status}" ${enableDragDrop ? 'draggable="true"' : ''}>
                ${hasGurmus ? `
                    <button class="node-toggle" data-target="${gurmusId}">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                ` : '<div style="width: 20px;"></div>'}
                
                <div class="node-info">
                    <i class="bi bi-house text-purple me-2"></i>
                    <div>
                        <div class="node-title">${gamta.name}</div>
                        <div class="node-subtitle">${gamta.code} • ${gamta.location || 'No location'}</div>
                    </div>
                </div>
                
                <div class="node-badges">
                    <span class="badge ${gamta.status === 'active' ? 'bg-success' : 'bg-secondary'}">${gamta.status}</span>
                    ${showUserCount ? `<span class="badge bg-info">${gamta.user_count || 0} users</span>` : ''}
                    ${hasGurmus ? `<span class="badge bg-warning">${gamta.gurmus.length} gurmus</span>` : ''}
                </div>
                
                <div class="node-actions">
                    <a href="/hierarchy/${gamta.id}?type=gamta" class="btn btn-sm btn-outline-primary" title="View Details">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="/hierarchy/${gamta.id}/edit?type=gamta" class="btn btn-sm btn-outline-warning" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
            </div>
            
            ${hasGurmus ? `
                <div class="tree-children collapsed" id="${gurmusId}">
                    ${gamta.gurmus.map(gurmu => renderGurmuNode(gurmu)).join('')}
                </div>
            ` : ''}
        </div>
    `;
    
    return html;
}

function renderGurmuNode(gurmu) {
    return `
        <div class="tree-node gurmu-node" data-id="${gurmu.id}" data-type="gurmu">
            <div class="node-content ${gurmu.status}" ${enableDragDrop ? 'draggable="true"' : ''}>
                <div style="width: 20px;"></div>
                
                <div class="node-info">
                    <i class="bi bi-people text-warning me-2"></i>
                    <div>
                        <div class="node-title">${gurmu.name}</div>
                        <div class="node-subtitle">${gurmu.code} • ${gurmu.location || 'No location'}</div>
                    </div>
                </div>
                
                <div class="node-badges">
                    <span class="badge ${gurmu.status === 'active' ? 'bg-success' : 'bg-secondary'}">${gurmu.status}</span>
                    ${showUserCount ? `<span class="badge bg-info">${gurmu.user_count || 0} members</span>` : ''}
                </div>
                
                <div class="node-actions">
                    <a href="/hierarchy/${gurmu.id}?type=gurmu" class="btn btn-sm btn-outline-primary" title="View Details">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="/hierarchy/${gurmu.id}/edit?type=gurmu" class="btn btn-sm btn-outline-warning" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
            </div>
        </div>
    `;
}

function addTreeEventListeners() {
    // Toggle functionality
    document.querySelectorAll('.node-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const targetId = this.dataset.target;
            const target = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (target.classList.contains('collapsed')) {
                target.classList.remove('collapsed');
                icon.className = 'bi bi-chevron-down';
            } else {
                target.classList.add('collapsed');
                icon.className = 'bi bi-chevron-right';
            }
        });
    });
    
    // Drag and drop functionality (if enabled)
    if (enableDragDrop) {
        addDragDropListeners();
    }
}

function addDragDropListeners() {
    document.querySelectorAll('[draggable="true"]').forEach(node => {
        node.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', JSON.stringify({
                id: this.closest('.tree-node').dataset.id,
                type: this.closest('.tree-node').dataset.type
            }));
            this.style.opacity = '0.5';
        });
        
        node.addEventListener('dragend', function(e) {
            this.style.opacity = '1';
            document.querySelectorAll('.drag-over').forEach(el => {
                el.classList.remove('drag-over');
            });
        });
        
        node.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.closest('.tree-node').classList.add('drag-over');
        });
        
        node.addEventListener('dragleave', function(e) {
            this.closest('.tree-node').classList.remove('drag-over');
        });
        
        node.addEventListener('drop', function(e) {
            e.preventDefault();
            const dragData = JSON.parse(e.dataTransfer.getData('text/plain'));
            const dropTarget = this.closest('.tree-node');
            
            dropTarget.classList.remove('drag-over');
            handleDrop(dragData, dropTarget);
        });
    });
}

function handleDrop(dragData, dropTarget) {
    const sourceType = dragData.type;
    const targetType = dropTarget.dataset.type;
    
    // Only allow Gamta to be moved to different Godina
    if (sourceType === 'gamta' && targetType === 'godina') {
        const sourceId = dragData.id;
        const targetId = dropTarget.dataset.id;
        
        if (confirm('Move this Gamta to the selected Godina?')) {
            moveGamtaToGodina(sourceId, targetId);
        }
    } else {
        alert('Invalid drop operation. Only Gamtas can be moved between Godinas.');
    }
}

function moveGamtaToGodina(gamtaId, godinaId) {
    fetch('/hierarchy/move-gamta', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
        },
        body: JSON.stringify({
            gamta_id: gamtaId,
            godina_id: godinaId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadHierarchyTree(); // Reload the tree
            alert('Gamta moved successfully!');
        } else {
            alert('Failed to move Gamta: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error moving Gamta:', error);
        alert('An error occurred while moving the Gamta.');
    });
}

function handleSearch() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
    
    if (searchTerm === '') {
        // Reset to current filter
        setFilter(currentFilter);
        return;
    }
    
    // Filter hierarchy based on search term
    filteredData = hierarchyData.filter(godina => {
        const godinaMatches = godina.name.toLowerCase().includes(searchTerm) ||
                             godina.code.toLowerCase().includes(searchTerm) ||
                             (godina.location && godina.location.toLowerCase().includes(searchTerm));
        
        if (godinaMatches) {
            return true;
        }
        
        // Check if any gamta matches
        if (godina.children) {
            const matchingGamtas = godina.children.filter(gamta => 
                gamta.name.toLowerCase().includes(searchTerm) ||
                gamta.code.toLowerCase().includes(searchTerm) ||
                (gamta.location && gamta.location.toLowerCase().includes(searchTerm))
            );
            
            if (matchingGamtas.length > 0) {
                // Return godina with only matching gamtas
                return {
                    ...godina,
                    children: matchingGamtas
                };
            }
        }
        
        return false;
    }).map(godina => {
        if (typeof godina === 'boolean') return godina;
        
        // Filter gamtas within godina
        const filteredGamtas = godina.children ? godina.children.filter(gamta =>
            gamta.name.toLowerCase().includes(searchTerm) ||
            gamta.code.toLowerCase().includes(searchTerm) ||
            (gamta.location && gamta.location.toLowerCase().includes(searchTerm))
        ) : [];
        
        return {
            ...godina,
            children: filteredGamtas
        };
    });
    
    renderHierarchyTree();
    
    // Highlight matching terms
    highlightSearchTerms(searchTerm);
}

function highlightSearchTerms(searchTerm) {
    document.querySelectorAll('.tree-node').forEach(node => {
        const content = node.textContent.toLowerCase();
        if (content.includes(searchTerm)) {
            node.classList.add('highlighted');
        }
    });
}

function clearSearch() {
    document.getElementById('searchInput').value = '';
    setFilter(currentFilter);
}

function setFilter(filterType) {
    currentFilter = filterType;
    
    // Update active filter button
    document.querySelectorAll('[data-filter]').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-filter="${filterType}"]`).classList.add('active');
    
    // Apply filter
    switch (filterType) {
        case 'all':
            filteredData = [...hierarchyData];
            break;
        case 'active':
            filteredData = hierarchyData.filter(godina => godina.status === 'active')
                .map(godina => ({
                    ...godina,
                    children: godina.children ? godina.children.filter(gamta => gamta.status === 'active') : []
                }));
            break;
        case 'inactive':
            filteredData = hierarchyData.filter(godina => godina.status === 'inactive')
                .map(godina => ({
                    ...godina,
                    children: godina.children ? godina.children.filter(gamta => gamta.status === 'inactive') : []
                }));
            break;
        case 'with-users':
            filteredData = hierarchyData.filter(godina => {
                const hasUsersInGamtas = godina.children && godina.children.some(gamta => (gamta.user_count || 0) > 0);
                return hasUsersInGamtas;
            }).map(godina => ({
                ...godina,
                children: godina.children ? godina.children.filter(gamta => (gamta.user_count || 0) > 0) : []
            }));
            break;
        case 'empty':
            filteredData = hierarchyData.filter(godina => {
                const hasEmptyGamtas = godina.children && godina.children.some(gamta => (gamta.user_count || 0) === 0);
                const hasNoGamtas = !godina.children || godina.children.length === 0;
                return hasEmptyGamtas || hasNoGamtas;
            }).map(godina => ({
                ...godina,
                children: godina.children ? godina.children.filter(gamta => (gamta.user_count || 0) === 0) : []
            }));
            break;
    }
    
    renderHierarchyTree();
}

function expandAll() {
    document.querySelectorAll('.node-children.collapsed').forEach(node => {
        node.classList.remove('collapsed');
    });
    document.querySelectorAll('.node-toggle i').forEach(icon => {
        icon.className = 'bi bi-chevron-down';
    });
}

function collapseAll() {
    document.querySelectorAll('.node-children').forEach(node => {
        node.classList.add('collapsed');
    });
    document.querySelectorAll('.node-toggle i').forEach(icon => {
        icon.className = 'bi bi-chevron-right';
    });
}

function toggleUserCount() {
    showUserCount = document.getElementById('showUserCount').checked;
    renderHierarchyTree();
}

function toggleDragDrop() {
    enableDragDrop = document.getElementById('enableDragDrop').checked;
    renderHierarchyTree();
}

function showContextMenu(event, treeNode) {
    contextMenuTarget = treeNode;
    const contextMenu = document.getElementById('contextMenu');
    
    contextMenu.style.display = 'block';
    contextMenu.style.left = event.pageX + 'px';
    contextMenu.style.top = event.pageY + 'px';
    
    // Update context menu based on node type
    const nodeType = treeNode.dataset.type;
    const addGamtaItem = contextMenu.querySelector('[data-action="add-gamta"]');
    
    if (nodeType === 'godina') {
        addGamtaItem.style.display = 'block';
    } else {
        addGamtaItem.style.display = 'none';
    }
}

function hideContextMenu() {
    document.getElementById('contextMenu').style.display = 'none';
    contextMenuTarget = null;
}

// Context menu actions
document.querySelectorAll('.context-item').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (!contextMenuTarget) return;
        
        const action = this.dataset.action;
        const nodeId = contextMenuTarget.dataset.id;
        const nodeType = contextMenuTarget.dataset.type;
        
        switch (action) {
            case 'view':
                window.location.href = `/hierarchy/${nodeId}?type=${nodeType}`;
                break;
            case 'edit':
                window.location.href = `/hierarchy/${nodeId}/edit?type=${nodeType}`;
                break;
            case 'add-gamta':
                window.location.href = `/hierarchy/create?type=gamta&godina_id=${nodeId}`;
                break;
            case 'assign-users':
                window.location.href = `/users?filter=unassigned&assign_to=${nodeType}_${nodeId}`;
                break;
            case 'delete':
                if (confirm('Are you sure you want to delete this organizational unit?')) {
                    deleteHierarchyUnit(nodeId, nodeType);
                }
                break;
        }
        
        hideContextMenu();
    });
});

function deleteHierarchyUnit(id, type) {
    fetch(`/hierarchy/${id}?type=${type}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadHierarchyTree();
            alert('Organizational unit deleted successfully!');
        } else {
            alert('Failed to delete: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error deleting unit:', error);
        alert('An error occurred while deleting the organizational unit.');
    });
}
</script>