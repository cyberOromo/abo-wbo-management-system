<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hierarchical User Registration - System Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .hierarchy-card {
            border-left: 4px solid #0d6efd;
            margin-bottom: 20px;
        }
        .stats-card {
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .recent-registration {
            border-left: 3px solid #28a745;
            padding-left: 15px;
            margin-bottom: 10px;
        }
        .form-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
        .hierarchy-indicator {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .global { background: #e3f2fd; color: #1565c0; }
        .godina { background: #f3e5f5; color: #7b1fa2; }
        .gamta { background: #e8f5e8; color: #2e7d32; }
        .gurmu { background: #fff3e0; color: #ef6c00; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3"><i class="bi bi-diagram-3"></i> Hierarchical User Registration</h1>
                        <p class="text-muted">Create users across the organizational hierarchy with proper position assignments</p>
                    </div>
                    <div>
                        <span class="badge bg-primary">System Admin Panel</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-people-fill display-4 mb-2"></i>
                        <h4><?php echo array_sum($stats['by_role'] ?? []); ?></h4>
                        <p class="mb-0">Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-person-plus-fill display-4 mb-2"></i>
                        <h4><?php echo $stats['recent']['this_month'] ?? 0; ?></h4>
                        <p class="mb-0">This Month</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-week display-4 mb-2"></i>
                        <h4><?php echo $stats['recent']['this_week'] ?? 0; ?></h4>
                        <p class="mb-0">This Week</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-day display-4 mb-2"></i>
                        <h4><?php echo $stats['recent']['today'] ?? 0; ?></h4>
                        <p class="mb-0">Today</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Registration Form -->
            <div class="col-lg-8">
                <div class="card hierarchy-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person-plus"></i> Create New Hierarchical User</h5>
                    </div>
                    <div class="card-body">
                        <form id="hierarchicalRegistrationForm" method="POST" action="/admin/hierarchical-registration/register">
                            <input type="hidden" name="_token" value="<?php echo $_SESSION['_token']; ?>">
                            
                            <!-- Personal Information Section -->
                            <div class="form-section">
                                <h6 class="text-primary mb-3"><i class="bi bi-person-circle"></i> Personal Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="first_name" class="form-label required-field">First Name</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="last_name" class="form-label required-field">Last Name</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label required-field">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" id="phone" name="phone">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="language_preference" class="form-label">Language Preference</label>
                                            <select class="form-select" id="language_preference" name="language_preference">
                                                <option value="en">English</option>
                                                <option value="om">Oromiffa</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="send_welcome_email" name="send_welcome_email" value="1" checked>
                                                <label class="form-check-label" for="send_welcome_email">
                                                    Send welcome email with login credentials
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Role and Hierarchy Section -->
                            <div class="form-section">
                                <h6 class="text-primary mb-3"><i class="bi bi-diagram-3"></i> Role and Hierarchy Assignment</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="role" class="form-label required-field">User Role</label>
                                            <select class="form-select" id="role" name="role" required>
                                                <option value="">Select Role</option>
                                                <option value="executive">Executive</option>
                                                <option value="member">Member</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="level_scope" class="form-label required-field">Hierarchy Level</label>
                                            <select class="form-select" id="level_scope" name="level_scope" required>
                                                <option value="">Select Level</option>
                                                <option value="global">Global Organization</option>
                                                <option value="godina">Godina (Regional)</option>
                                                <option value="gamta">Gamta (Sub-Regional)</option>
                                                <option value="gurmu">Gurmu (Local)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hierarchy Selection -->
                                <div id="hierarchy-selectors">
                                    <!-- Global Selection -->
                                    <div class="mb-3" id="global-selector" style="display: none;">
                                        <label for="global_id" class="form-label">Global Organization</label>
                                        <select class="form-select" id="global_id" name="global_id">
                                            <option value="">Select Global Organization</option>
                                            <?php if (!empty($hierarchy_data['global'])): ?>
                                                <option value="<?php echo $hierarchy_data['global']['id']; ?>">
                                                    <?php echo htmlspecialchars($hierarchy_data['global']['name'] ?? 'ABO-WBO Global'); ?>
                                                </option>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <!-- Godina Selection -->
                                    <div class="mb-3" id="godina-selector" style="display: none;">
                                        <label for="godina_id" class="form-label">Godina (Region)</label>
                                        <select class="form-select" id="godina_id" name="godina_id">
                                            <option value="">Select Godina</option>
                                            <?php if (!empty($hierarchy_data['godinas'])): ?>
                                                <?php foreach ($hierarchy_data['godinas'] as $godina): ?>
                                                    <option value="<?php echo $godina['id']; ?>">
                                                        <?php echo htmlspecialchars($godina['name']) . ' (' . htmlspecialchars($godina['code']) . ')'; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <!-- Gamta Selection -->
                                    <div class="mb-3" id="gamta-selector" style="display: none;">
                                        <label for="gamta_id" class="form-label">Gamta (Sub-Region)</label>
                                        <select class="form-select" id="gamta_id" name="gamta_id">
                                            <option value="">Select Gamta</option>
                                        </select>
                                    </div>

                                    <!-- Gurmu Selection -->
                                    <div class="mb-3" id="gurmu-selector" style="display: none;">
                                        <label for="gurmu_id" class="form-label">Gurmu (Local)</label>
                                        <select class="form-select" id="gurmu_id" name="gurmu_id">
                                            <option value="">Select Gurmu</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Position Assignment Section -->
                            <div class="form-section">
                                <h6 class="text-primary mb-3"><i class="bi bi-award"></i> Position Assignment</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="position_id" class="form-label">Organizational Position</label>
                                            <select class="form-select" id="position_id" name="position_id">
                                                <option value="">No Specific Position</option>
                                                <?php if (!empty($positions)): ?>
                                                    <?php foreach ($positions as $position): ?>
                                                        <option value="<?php echo $position['id']; ?>" 
                                                                data-executive="<?php echo $position['is_executive']; ?>"
                                                                data-hierarchy="<?php echo $position['hierarchy_type']; ?>">
                                                            <?php echo htmlspecialchars($position['name']) . ' (' . htmlspecialchars($position['code']) . ')'; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="appointment_type" class="form-label">Appointment Type</label>
                                            <select class="form-select" id="appointment_type" name="appointment_type">
                                                <option value="appointed">Appointed</option>
                                                <option value="elected">Elected</option>
                                                <option value="volunteer">Volunteer</option>
                                                <option value="permanent">Permanent</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Start Date</label>
                                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary me-2" onclick="resetForm()">
                                    <i class="bi bi-arrow-clockwise"></i> Reset Form
                                </button>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="bi bi-person-plus"></i> Create User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Recent Registrations -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bi bi-clock-history"></i> Recent Registrations</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_registrations)): ?>
                            <?php foreach (array_slice($recent_registrations, 0, 10) as $registration): ?>
                                <div class="recent-registration">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?php echo htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($registration['email']); ?></small>
                                            <br>
                                            <span class="hierarchy-indicator <?php echo $registration['level_scope'] ?? 'global'; ?>">
                                                <?php echo ucfirst($registration['level_scope'] ?? 'global'); ?>
                                            </span>
                                            <?php if (!empty($registration['position_name'])): ?>
                                                <span class="badge bg-info text-dark ms-1"><?php echo htmlspecialchars($registration['position_name']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('M j', strtotime($registration['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No recent registrations found.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistics Breakdown -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bi bi-graph-up"></i> User Statistics</h6>
                    </div>
                    <div class="card-body">
                        <h6>By Role</h6>
                        <?php if (!empty($stats['by_role'])): ?>
                            <?php foreach ($stats['by_role'] as $role => $count): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><?php echo ucfirst($role); ?>:</span>
                                    <strong><?php echo $count; ?></strong>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <h6>By Hierarchy Level</h6>
                        <?php if (!empty($stats['by_level'])): ?>
                            <?php foreach ($stats['by_level'] as $level => $count): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><?php echo ucfirst($level); ?>:</span>
                                    <strong><?php echo $count; ?></strong>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Hierarchy data for JavaScript
        const hierarchyData = <?php echo json_encode($hierarchy_data); ?>;
        
        // Level scope change handler
        document.getElementById('level_scope').addEventListener('change', function() {
            const level = this.value;
            showHierarchySelectors(level);
        });
        
        // Godina change handler
        document.getElementById('godina_id').addEventListener('change', function() {
            const godinaId = this.value;
            loadGamtas(godinaId);
        });
        
        // Gamta change handler
        document.getElementById('gamta_id').addEventListener('change', function() {
            const gamtaId = this.value;
            loadGurmus(gamtaId);
        });
        
        function showHierarchySelectors(level) {
            // Hide all selectors
            document.getElementById('global-selector').style.display = 'none';
            document.getElementById('godina-selector').style.display = 'none';
            document.getElementById('gamta-selector').style.display = 'none';
            document.getElementById('gurmu-selector').style.display = 'none';
            
            // Clear all selections
            document.getElementById('global_id').value = '';
            document.getElementById('godina_id').value = '';
            document.getElementById('gamta_id').value = '';
            document.getElementById('gurmu_id').value = '';
            
            // Show relevant selectors based on level
            switch(level) {
                case 'global':
                    document.getElementById('global-selector').style.display = 'block';
                    // Auto-select global if only one exists
                    if (hierarchyData.global) {
                        document.getElementById('global_id').value = hierarchyData.global.id;
                    }
                    break;
                case 'godina':
                    document.getElementById('global-selector').style.display = 'block';
                    document.getElementById('godina-selector').style.display = 'block';
                    if (hierarchyData.global) {
                        document.getElementById('global_id').value = hierarchyData.global.id;
                    }
                    break;
                case 'gamta':
                    document.getElementById('global-selector').style.display = 'block';
                    document.getElementById('godina-selector').style.display = 'block';
                    document.getElementById('gamta-selector').style.display = 'block';
                    if (hierarchyData.global) {
                        document.getElementById('global_id').value = hierarchyData.global.id;
                    }
                    break;
                case 'gurmu':
                    document.getElementById('global-selector').style.display = 'block';
                    document.getElementById('godina-selector').style.display = 'block';
                    document.getElementById('gamta-selector').style.display = 'block';
                    document.getElementById('gurmu-selector').style.display = 'block';
                    if (hierarchyData.global) {
                        document.getElementById('global_id').value = hierarchyData.global.id;
                    }
                    break;
            }
        }
        
        function loadGamtas(godinaId) {
            const gamtaSelect = document.getElementById('gamta_id');
            const gurmuSelect = document.getElementById('gurmu_id');
            
            // Clear existing options
            gamtaSelect.innerHTML = '<option value="">Select Gamta</option>';
            gurmuSelect.innerHTML = '<option value="">Select Gurmu</option>';
            
            if (!godinaId) return;
            
            // Filter Gamtas by Godina
            const gamtas = hierarchyData.gamtas.filter(g => g.godina_id == godinaId);
            gamtas.forEach(gamta => {
                const option = document.createElement('option');
                option.value = gamta.id;
                option.textContent = gamta.name + ' (' + gamta.code + ')';
                gamtaSelect.appendChild(option);
            });
        }
        
        function loadGurmus(gamtaId) {
            const gurmuSelect = document.getElementById('gurmu_id');
            
            // Clear existing options
            gurmuSelect.innerHTML = '<option value="">Select Gurmu</option>';
            
            if (!gamtaId) return;
            
            // Filter Gurmus by Gamta
            const gurmus = hierarchyData.gurmus.filter(g => g.gamta_id == gamtaId);
            gurmus.forEach(gurmu => {
                const option = document.createElement('option');
                option.value = gurmu.id;
                option.textContent = gurmu.name + ' (' + gurmu.code + ')';
                gurmuSelect.appendChild(option);
            });
        }
        
        // Form submission
        document.getElementById('hierarchicalRegistrationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const formData = new FormData(this);
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating User...';
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User created successfully!\n\nTemporary Password: ' + data.temp_password + '\n\nPlease save this password and share it securely with the user.');
                    this.reset();
                    showHierarchySelectors(''); // Hide selectors
                    
                    // Reload page to refresh recent registrations
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the user.');
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-person-plus"></i> Create User';
            });
        });
        
        function resetForm() {
            document.getElementById('hierarchicalRegistrationForm').reset();
            showHierarchySelectors(''); // Hide all selectors
        }
        
        // Initialize the form
        document.addEventListener('DOMContentLoaded', function() {
            // Hide all hierarchy selectors initially
            showHierarchySelectors('');
        });
    </script>
</body>
</html>