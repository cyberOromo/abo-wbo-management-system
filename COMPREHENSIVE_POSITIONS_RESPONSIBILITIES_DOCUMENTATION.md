# Comprehensive Positions and Responsibilities System Documentation
## Software Development Logic for ABO-WBO Management System

---

## 1. EXECUTIVE POSITIONS STRUCTURE

### 1.1 The 7 Core Executive Positions (Consistent Across All Levels)

Every organizational level (Global, Godina, Gamta, Gurmu) maintains exactly **7 executive positions**:

```sql
INSERT INTO positions (key_name, name_en, name_om, description_en, description_om, level_scope, sort_order, status) VALUES
('barreessaa', 'Secretary', 'Barreessaa', 'Administrative oversight and documentation management', 'Bulchiinsa hojii fi galmeessuu', 'all', 1, 'active'),
('dinagdee', 'Finance Manager', 'Dinagdee', 'Financial management and resource allocation', 'Bulchiinsa maallaqaa fi qabeenya', 'all', 2, 'active'),
('diplomaasii_hawaasummaa', 'Public Diplomacy', 'Diploomaasii Hawaasummaa', 'External relations and community engagement', 'Hariiroo alaa fi hirmaannaa hawaasaa', 'all', 3, 'active'),
('dura_taa', 'Leadership', 'Dura Ta_aa', 'Strategic direction and organizational guidance', 'Kallattii tarsiimoo fi qajeelfama dhaabbataa', 'all', 4, 'active'),
('ijaarsaa_siyaasa', 'Development & Politics', 'Ijaarsaa fi Siyaasa', 'Policy development and political strategy', 'Misooma imaammata fi tarsiimoo siyaasaa', 'all', 5, 'active'),
('mediyaa_sab_quunnamtii', 'Media & Public Relations', 'Mediyaa fi Sab-Quunnamtii', 'Media management and public communications', 'Bulchiinsa miidiyaa fi qunnamtii ummataa', 'all', 6, 'active'),
('tohannoo_keessaa', 'Internal Affairs', 'Tohannoo Keessaa', 'Internal coordination and membership management', 'Qindoomina keessaa fi bulchiinsa miseensotaa', 'all', 7, 'active');
```

---

## 2. INDIVIDUAL RESPONSIBILITIES STRUCTURE

### 2.1 The 5 Individual Responsibilities Per Position

Each of the 7 positions has exactly **5 individual responsibility categories**:

#### A. Responsibility Categories (Common to All Positions)
1. **Gabaasa** (Reporting & Documentation) - Regular reporting and comprehensive documentation
2. **Gamaaggama** (Evaluation & Assessment) - Evaluation and assessment activities
3. **Karoora** (Planning & Strategic Development) - Strategic and operational planning development
4. **Projektoota** (Projects & Initiatives) - Project management and initiative implementation
5. **Qaboo Ya_ii** (Meetings Management) - Meeting coordination and management

#### B. Position-Specific Individual Responsibilities

```sql
-- BARREESSAA (Secretary) Individual Responsibilities
INSERT INTO individual_responsibilities (position_key, category, name_en, name_om, description_en, description_om, sort_order) VALUES
('barreessaa', 'gabaasa', 'Administrative Reporting & Documentation', 'Gabaasa fi Galmee Bulchiinsaa', 'Prepare and manage all administrative reports and comprehensive documentation', 'Gabaasa bulchiinsaa fi galmee bal_aa hunda qopheessuu fi bulchuu', 1),
('barreessaa', 'gamaaggama', 'Administrative Evaluation & Assessment', 'Gamaaggama fi Madaallii Bulchiinsaa', 'Conduct evaluations and assessments of administrative processes and performance', 'Gamaaggama fi madaallii adeemsa bulchiinsaa fi raawwii gaggeessuu', 2),
('barreessaa', 'karoora', 'Administrative Planning & Strategic Development', 'Karoora fi Misooma Tarsiimoo Bulchiinsaa', 'Develop administrative procedures and strategic workflow planning', 'Adeemsa bulchiinsaa fi misooma karoora tarsiimoo hojii misoomuu', 3),
('barreessaa', 'projektoota', 'Documentation Projects & Initiatives', 'Pirojektii fi Jalqabni Galmee', 'Manage documentation projects and record-keeping initiatives', 'Pirojektii galmee fi jalqabni kuusaa ragaa bulchuu', 4),
('barreessaa', 'qaboo_yaii', 'Administrative Meetings Management', 'Bulchiinsa Qaboo Ya_ii Bulchiinsaa', 'Organize and manage administrative meetings and coordinate meeting activities', 'Qaboo ya_ii bulchiinsaa qindeessuu fi bulchuu fi qindoomina sochiiwwan qaboo ya_ii', 5);

-- DINAGDEE (Finance) Individual Responsibilities  
INSERT INTO individual_responsibilities (position_key, category, name_en, name_om, description_en, description_om, sort_order) VALUES
('dinagdee', 'gabaasa', 'Financial Reporting & Documentation', 'Gabaasa fi Galmee Maallaqaa', 'Prepare financial statements, budget reports and comprehensive financial documentation', 'Ibsa maallaqaa, gabaasa baajata fi galmee bal_aa maallaqaa qopheessuu', 1),
('dinagdee', 'gamaaggama', 'Financial Evaluation & Assessment', 'Gamaaggama fi Madaallii Maallaqaa', 'Conduct financial evaluations and assess budget performance', 'Gamaaggama maallaqaa fi madaallii raawwii baajata gaggeessuu', 2),
('dinagdee', 'karoora', 'Financial Planning & Strategic Development', 'Karoora fi Misooma Tarsiimoo Maallaqaa', 'Develop budgets and financial strategic development plans', 'Baajata fi karoora misooma tarsiimoo maallaqaa misoomuu', 3),
('dinagdee', 'projektoota', 'Financial Projects & Initiatives', 'Pirojektii fi Jalqabni Maallaqaa', 'Manage fundraising projects and financial improvement initiatives', 'Pirojektii maallaqa walitti qabuu fi jalqabni fooyya_uu maallaqaa bulchuu', 4),
('dinagdee', 'qaboo_yaii', 'Financial Meetings Management', 'Bulchiinsa Qaboo Ya_ii Maallaqaa', 'Organize and manage financial meetings and coordinate financial discussions', 'Qaboo ya_ii maallaqaa qindeessuu fi bulchuu fi qindoomina mari_atanii maallaqaa', 5);

-- DIPLOOMAASII HAWAASUMMAA (Public Diplomacy) Individual Responsibilities
INSERT INTO individual_responsibilities (position_key, category, name_en, name_om, description_en, description_om, sort_order) VALUES
('diplomaasii_hawaasummaa', 'gabaasa', 'Diplomatic Reporting & Documentation', 'Gabaasa fi Galmee Diplomaasii', 'Prepare comprehensive reports and documentation on external relations and community engagement', 'Gabaasa fi galmee bal_aa hariiroo alaa fi hirmaannaa hawaasaa qopheessuu', 1),
('diplomaasii_hawaasummaa', 'gamaaggama', 'Diplomatic Evaluation & Assessment', 'Gamaaggama fi Madaallii Diplomaasii', 'Evaluate diplomatic relations and assess community engagement effectiveness', 'Gamaaggama hariiroo diplomaasii fi madaallii bu_uura hirmaannaa hawaasaa', 2),
('diplomaasii_hawaasummaa', 'karoora', 'Diplomatic Planning & Strategic Development', 'Karoora fi Misooma Tarsiimoo Diplomaasii', 'Plan and develop diplomatic initiatives and community outreach strategic programs', 'Jalqabni diplomaasii fi misooma sagantaa tarsiimoo dhimmii hawaasaa karoorfachuu', 3),
('diplomaasii_hawaasummaa', 'projektoota', 'Diplomatic Projects & Initiatives', 'Pirojektii fi Jalqabni Diplomaasii', 'Manage international relations projects and partnership initiatives', 'Pirojektii hariiroo idil-addunyaa fi jalqabni tumsa bulchuu', 4),
('diplomaasii_hawaasummaa', 'qaboo_yaii', 'Diplomatic Meetings Management', 'Bulchiinsa Qaboo Ya_ii Diplomaasii', 'Organize and manage diplomatic meetings and coordinate diplomatic discussions', 'Qaboo ya_ii diplomaasii qindeessuu fi bulchuu fi qindoomina mari_atanii diplomaasii', 5);

-- DURA TA_AA (Leadership) Individual Responsibilities
INSERT INTO individual_responsibilities (position_key, category, name_en, name_om, description_en, description_om, sort_order) VALUES
('dura_taa', 'gabaasa', 'Leadership Reporting & Documentation', 'Gabaasa fi Galmee Hogganaa', 'Prepare strategic reports and comprehensive organizational documentation', 'Gabaasa tarsiimoo fi galmee bal_aa dhaabbataa qopheessuu', 1),
('dura_taa', 'gamaaggama', 'Leadership Evaluation & Assessment', 'Gamaaggama fi Madaallii Hogganaa', 'Evaluate organizational performance and assess strategic effectiveness', 'Gamaaggama raawwii dhaabbataa fi madaallii bu_uura tarsiimoo', 2),
('dura_taa', 'karoora', 'Strategic Planning & Development', 'Karoora fi Misooma Tarsiimoo', 'Develop long-term strategic plans and organizational strategic development', 'Karoora tarsiimoo yeroo dheeraa fi misooma tarsiimoo dhaabbataa misoomuu', 3),
('dura_taa', 'projektoota', 'Strategic Projects & Initiatives', 'Pirojektii fi Jalqabni Tarsiimoo', 'Oversee major strategic projects and transformation initiatives', 'Jalqabni tarsiimoo gurguddaa fi pirojektii jijjiirama hordofuu', 4),
('dura_taa', 'qaboo_yaii', 'Leadership Meetings Management', 'Bulchiinsa Qaboo Ya_ii Hogganaa', 'Organize and manage leadership meetings and coordinate executive discussions', 'Qaboo ya_ii hogganaa qindeessuu fi bulchuu fi qindoomina mari_atanii hojii gaggeessaa', 5);

-- IJAARSAA FI SIYAASA (Development & Politics) Individual Responsibilities
INSERT INTO individual_responsibilities (position_key, category, name_en, name_om, description_en, description_om, sort_order) VALUES
('ijaarsaa_siyaasa', 'gabaasa', 'Development Reporting & Documentation', 'Gabaasa fi Galmee Misoomaa', 'Prepare development progress reports and comprehensive policy implementation documentation', 'Gabaasa guddina misoomaa fi galmee bal_aa hojiirra oolmaa imaammata qopheessuu', 1),
('ijaarsaa_siyaasa', 'gamaaggama', 'Development Evaluation & Assessment', 'Gamaaggama fi Madaallii Misoomaa', 'Evaluate development progress and assess policy implementation effectiveness', 'Gamaaggama guddina misoomaa fi madaallii bu_uura hojiirra oolmaa imaammata', 2),
('ijaarsaa_siyaasa', 'karoora', 'Development Planning & Strategic Development', 'Karoora fi Misooma Tarsiimoo Misoomaa', 'Plan organizational development and develop policy strategic frameworks', 'Misooma dhaabbataa fi misooma caasaa tarsiimoo imaammata karoorfachuu', 3),
('ijaarsaa_siyaasa', 'projektoota', 'Development Projects & Initiatives', 'Pirojektii fi Jalqabni Misoomaa', 'Manage capacity building projects and policy implementation initiatives', 'Pirojektii ijaarsa dandeettii fi jalqabni hojiirra oolmaa imaammata bulchuu', 4),
('ijaarsaa_siyaasa', 'qaboo_yaii', 'Development Meetings Management', 'Bulchiinsa Qaboo Ya_ii Misoomaa', 'Organize and manage development meetings and coordinate policy discussions', 'Qaboo ya_ii misoomaa qindeessuu fi bulchuu fi qindoomina mari_atanii imaammata', 5);

-- MEDIYAA FI SAB-QUUNNAMTII (Media & Public Relations) Individual Responsibilities
INSERT INTO individual_responsibilities (position_key, category, name_en, name_om, description_en, description_om, sort_order) VALUES
('mediyaa_sab_quunnamtii', 'gabaasa', 'Media Reporting & Documentation', 'Gabaasa fi Galmee Miidiyaa', 'Prepare media coverage reports and comprehensive communication documentation', 'Gabaasa coverage miidiyaa fi galmee bal_aa qunnamtii qopheessuu', 1),
('mediyaa_sab_quunnamtii', 'gamaaggama', 'Media Evaluation & Assessment', 'Gamaaggama fi Madaallii Miidiyaa', 'Evaluate media effectiveness and assess public communication impact', 'Gamaaggama bu_uura miidiyaa fi madaallii dhiibbaa qunnamtii ummataa', 2),
('mediyaa_sab_quunnamtii', 'karoora', 'Communication Planning & Strategic Development', 'Karoora fi Misooma Tarsiimoo Qunnamtii', 'Develop communication strategies and strategic media campaign development', 'Tarsiimoo qunnamtii fi misooma duulee tarsiimoo miidiyaa misoomuu', 3),
('mediyaa_sab_quunnamtii', 'projektoota', 'Media Projects & Initiatives', 'Pirojektii fi Jalqabni Miidiyaa', 'Manage media production projects and public awareness initiatives', 'Pirojektii oomisha miidiyaa fi jalqabni hubannoo ummataa bulchuu', 4),
('mediyaa_sab_quunnamtii', 'qaboo_yaii', 'Media Meetings Management', 'Bulchiinsa Qaboo Ya_ii Miidiyaa', 'Organize and manage media meetings and coordinate communication activities', 'Qaboo ya_ii miidiyaa qindeessuu fi bulchuu fi qindoomina sochiiwwan qunnamtii', 5);

-- TOHANNOO KEESSAA (Internal Affairs) Individual Responsibilities
INSERT INTO individual_responsibilities (position_key, category, name_en, name_om, description_en, description_om, sort_order) VALUES
('tohannoo_keessaa', 'gabaasa', 'Internal Reporting & Documentation', 'Gabaasa fi Galmee Keessaa', 'Prepare internal affairs reports and comprehensive membership documentation', 'Gabaasa dhimmii keessaa fi galmee bal_aa miseensummaa qopheessuu', 1),
('tohannoo_keessaa', 'gamaaggama', 'Internal Evaluation & Assessment', 'Gamaaggama fi Madaallii Keessaa', 'Evaluate internal operations and assess member engagement effectiveness', 'Gamaaggama hojii keessaa fi madaallii bu_uura hirmaannaa miseensotaa', 2),
('tohannoo_keessaa', 'karoora', 'Internal Planning & Strategic Development', 'Karoora fi Misooma Tarsiimoo Keessaa', 'Plan internal coordination and develop membership strategic development programs', 'Qindoomina keessaa fi misooma sagantaa tarsiimoo miseensummaa karoorfachuu', 3),
('tohannoo_keessaa', 'projektoota', 'Internal Projects & Initiatives', 'Pirojektii fi Jalqabni Keessaa', 'Manage member engagement projects and internal development initiatives', 'Pirojektii hirmaannaa miseensotaa fi jalqabni misooma keessaa bulchuu', 4),
('tohannoo_keessaa', 'qaboo_yaii', 'Internal Meetings Management', 'Bulchiinsa Qaboo Ya_ii Keessaa', 'Organize and manage internal meetings and coordinate internal discussions', 'Qaboo ya_ii keessaa qindeessuu fi bulchuu fi qindoomina mari_atanii keessaa', 5);
```

---

## 3. SHARED EXECUTIVE RESPONSIBILITIES STRUCTURE

### 3.1 The 5 Shared Team Responsibilities

Every executive team (at all levels) collaborates on exactly **5 shared responsibility categories**:

```sql
-- SHARED EXECUTIVE TEAM RESPONSIBILITIES (Same for All Levels)
INSERT INTO shared_responsibilities (level_scope, category, name_en, name_om, description_en, description_om, sort_order) VALUES
('all', 'gabaasa', 'Collective Reporting & Documentation', 'Gabaasa fi Galmee Waliigalaa', 'Joint preparation of comprehensive organizational reports and documentation', 'Qopheessuu waliigalaa gabaasa fi galmee bal_aa dhaabbataa', 1),
('all', 'gamaaggama', 'Team Evaluation & Assessment', 'Gamaaggama fi Madaallii Garee', 'Collaborative evaluation and assessment of organizational performance', 'Gamaaggama fi madaallii tumsaa raawwii dhaabbataa', 2),
('all', 'karoora', 'Collaborative Planning & Strategic Development', 'Karoora Tumsaa fi Misooma Tarsiimoo', 'Joint strategic planning development and coordinated decision making', 'Misooma karoora tarsiimoo waliigalaa fi murtii qindoomina', 3),
('all', 'projektoota', 'Joint Projects & Initiatives', 'Pirojektii fi Jalqabni Waliigalaa', 'Cross-functional project and initiative management and implementation', 'Bulchiinsa pirojektii fi jalqabni hojii-garagar-qaban fi hojiirra oolmaa', 4),
('all', 'qaboo_yaii', 'Shared Meetings Management', 'Bulchiinsa Qaboo Ya_ii Qoodamaa', 'Collective meeting management and coordination', 'Bulchiinsa fi qindoomina qaboo ya_ii waliigalaa', 5);
```

---

## 4. SOFTWARE DEVELOPMENT LOGIC IMPLEMENTATION

### 4.1 Database Schema for Responsibilities

```sql
-- Individual Responsibilities Table
CREATE TABLE individual_responsibilities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    position_key VARCHAR(50) NOT NULL,
    category ENUM('gabaasa', 'gamaaggama', 'karoora', 'projektoota', 'qaboo_yaii') NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    name_om VARCHAR(255) NOT NULL,
    description_en TEXT,
    description_om TEXT,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (position_key) REFERENCES positions(key_name) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_position_category (position_key, category),
    INDEX idx_position_key (position_key),
    INDEX idx_category (category),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shared Responsibilities Table
CREATE TABLE shared_responsibilities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    level_scope ENUM('global', 'godina', 'gamta', 'gurmu', 'all') NOT NULL,
    category ENUM('gabaasa', 'gamaaggama', 'karoora', 'projektoota', 'qaboo_yaii') NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    name_om VARCHAR(255) NOT NULL,
    description_en TEXT,
    description_om TEXT,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_level_category (level_scope, category),
    INDEX idx_level_scope (level_scope),
    INDEX idx_category (category),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Responsibility Assignments Table
CREATE TABLE user_responsibility_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    responsibility_type ENUM('individual', 'shared') NOT NULL,
    responsibility_id INT NOT NULL,
    assignment_level ENUM('global', 'godina', 'gamta', 'gurmu') NOT NULL,
    scope_id INT NOT NULL, -- ID of the specific organizational unit
    assigned_by INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'completed') DEFAULT 'active',
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_responsibility_type (responsibility_type),
    INDEX idx_responsibility_id (responsibility_id),
    INDEX idx_assignment_level (assignment_level),
    INDEX idx_scope_id (scope_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4.2 Application Logic for Position Management

```php
class PositionResponsibilityManager 
{
    // Get all individual responsibilities for a specific position
    public function getIndividualResponsibilities($positionKey) 
    {
        return DB::table('individual_responsibilities')
            ->where('position_key', $positionKey)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();
    }
    
    // Get shared responsibilities for a specific organizational level
    public function getSharedResponsibilities($levelScope) 
    {
        return DB::table('shared_responsibilities')
            ->where('level_scope', $levelScope)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();
    }
    
    // Assign user to position with all responsibilities
    public function assignUserToPosition($userId, $positionId, $levelScope, $scopeId) 
    {
        // Get position details
        $position = Position::find($positionId);
        
        // Assign individual responsibilities
        $individualResponsibilities = $this->getIndividualResponsibilities($position->key_name);
        foreach ($individualResponsibilities as $responsibility) {
            UserResponsibilityAssignment::create([
                'user_id' => $userId,
                'responsibility_type' => 'individual',
                'responsibility_id' => $responsibility->id,
                'assignment_level' => $levelScope,
                'scope_id' => $scopeId,
                'assigned_by' => auth()->id()
            ]);
        }
        
        // Assign shared responsibilities
        $sharedResponsibilities = $this->getSharedResponsibilities($levelScope);
        foreach ($sharedResponsibilities as $responsibility) {
            UserResponsibilityAssignment::create([
                'user_id' => $userId,
                'responsibility_type' => 'shared',
                'responsibility_id' => $responsibility->id,
                'assignment_level' => $levelScope,
                'scope_id' => $scopeId,
                'assigned_by' => auth()->id()
            ]);
        }
    }
    
    // Get complete responsibility structure for a user
    public function getUserResponsibilities($userId) 
    {
        $user = User::with(['position', 'gurmu.gamta.godina'])->find($userId);
        
        return [
            'individual_responsibilities' => $this->getIndividualResponsibilities($user->position->key_name),
            'shared_responsibilities' => $this->getSharedResponsibilities($user->level_scope),
            'position_info' => $user->position,
            'organizational_context' => [
                'gurmu' => $user->gurmu->name,
                'gamta' => $user->gurmu->gamta->name,
                'godina' => $user->gurmu->gamta->godina->name,
                'level' => $user->level_scope
            ]
        ];
    }
}
```

### 4.3 Frontend Component Structure

```javascript
// React Component for Position Responsibilities Display
const PositionResponsibilities = ({ userId }) => {
    const [responsibilities, setResponsibilities] = useState(null);
    
    useEffect(() => {
        fetchUserResponsibilities(userId).then(setResponsibilities);
    }, [userId]);
    
    if (!responsibilities) return <Loading />;
    
    return (
        <div className="position-responsibilities">
            <h2>Position: {responsibilities.position_info.name_en}</h2>
            
            {/* Individual Responsibilities */}
            <section className="individual-responsibilities">
                <h3>Individual Responsibilities</h3>
                <div className="responsibility-grid">
                    {responsibilities.individual_responsibilities.map(resp => (
                        <div key={resp.id} className="responsibility-card">
                            <h4>{resp.name_en}</h4>
                            <p>{resp.description_en}</p>
                            <div className="category-badge">{resp.category}</div>
                        </div>
                    ))}
                </div>
            </section>
            
            {/* Shared Responsibilities */}
            <section className="shared-responsibilities">
                <h3>Shared Team Responsibilities</h3>
                <div className="responsibility-grid">
                    {responsibilities.shared_responsibilities.map(resp => (
                        <div key={resp.id} className="responsibility-card shared">
                            <h4>{resp.name_en}</h4>
                            <p>{resp.description_en}</p>
                            <div className="category-badge shared">{resp.category}</div>
                        </div>
                    ))}
                </div>
            </section>
            
            {/* Organizational Context */}
            <section className="organizational-context">
                <h3>Organizational Context</h3>
                <div className="hierarchy-path">
                    {responsibilities.organizational_context.godina} →
                    {responsibilities.organizational_context.gamta} →
                    {responsibilities.organizational_context.gurmu}
                </div>
                <div className="level-indicator">
                    Level: {responsibilities.organizational_context.level}
                </div>
            </section>
        </div>
    );
};
```

---

## 5. MATHEMATICAL VALIDATION

### 5.1 Responsibility Distribution Calculation

```
Individual Responsibilities per Level:
- Global Level: 7 positions × 5 responsibilities = 35 individual responsibilities
- Godina Level: 42 positions × 5 responsibilities = 210 individual responsibilities  
- Gamta Level: 140 positions × 5 responsibilities = 700 individual responsibilities
- Gurmu Level: 336 positions × 5 responsibilities = 1,680 individual responsibilities
Total Individual Responsibilities: 2,625

Shared Responsibilities per Level:
- Global Level: 1 team × 5 shared = 5 shared responsibilities
- Godina Level: 6 teams × 5 shared = 30 shared responsibilities
- Gamta Level: 20 teams × 5 shared = 100 shared responsibilities  
- Gurmu Level: 48 teams × 5 shared = 240 shared responsibilities
Total Shared Responsibilities: 375

GRAND TOTAL RESPONSIBILITIES: 3,000
```

### 5.2 Validation Rules

```php
// Validation Rules for Position Assignment
class PositionValidationRules 
{
    public static function validatePositionStructure($organizationalUnit) 
    {
        // Rule 1: Each organizational unit must have exactly 7 positions
        $positionCount = Position::where('level_scope', $organizationalUnit->level)
                                ->where('status', 'active')
                                ->count();
        assert($positionCount === 7, "Each level must have exactly 7 positions");
        
        // Rule 2: Each position must have exactly 5 individual responsibilities
        $positions = Position::where('level_scope', $organizationalUnit->level)->get();
        foreach ($positions as $position) {
            $responsibilityCount = IndividualResponsibility::where('position_key', $position->key_name)
                                                          ->where('status', 'active')
                                                          ->count();
            assert($responsibilityCount === 5, "Each position must have exactly 5 responsibilities");
        }
        
        // Rule 3: Each level must have exactly 5 shared responsibilities
        $sharedCount = SharedResponsibility::where('level_scope', $organizationalUnit->level)
                                          ->where('status', 'active')
                                          ->count();
        assert($sharedCount === 5, "Each level must have exactly 5 shared responsibilities");
        
        return true;
    }
}
```

---

## 6. IMPLEMENTATION PHASES

### Phase 1: Core Structure Setup
1. Create positions table with 7 core positions
2. Create individual_responsibilities table with 35 base responsibilities (7×5)
3. Create shared_responsibilities table with 5 base shared responsibilities

### Phase 2: Level-Specific Implementation  
1. Replicate position structure across all 4 levels
2. Create level-specific responsibility variations
3. Implement assignment and validation logic

### Phase 3: User Integration
1. Connect users to positions with automatic responsibility assignment
2. Implement responsibility tracking and reporting
3. Create dashboards for responsibility management

### Phase 4: Advanced Features
1. Responsibility delegation and substitution
2. Performance tracking for responsibilities
3. Cross-level coordination tools

This comprehensive documentation provides the complete software development framework for implementing the ABO-WBO positions and responsibilities system with full mathematical validation and structural integrity.