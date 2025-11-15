# ABO-WBO Organizational Hierarchy & System Architecture Guide

## 🏢 **Organizational Structure Overview**

### **Hierarchy Levels (4-Tier Structure)**
```
Global (Waliigalaa Global)
   ↓
Godina (Continental/Regional Level)
   ↓
Gamta (Country Group Level)
   ↓  
Gurmu (City/Local Group Level)
```

---

## 🎯 **Executive Positions (7 Standard Positions)**

**Available at ALL levels** (Global, Godina, Gamta, Gurmu):

1. **Dura Ta'aa** (Chairperson/President)
2. **Barreessaa** (Secretary)
3. **Ijaarsaa fi Siyaasa** (Organization & Political Affairs)
4. **Dinagdee** (Finance & Economic Affairs)
5. **Mediyaa fi Sab-Quunnamtii** (Media & Communications)
6. **Diploomaasii Hawaasummaa** (Public Diplomacy & Community Relations)
7. **Tohannoo Keessaa** (Internal Audit & Oversight)

---

## 📋 **Shared Responsibilities & Tasks (5 Core Areas)**

**Applied to ALL positions and levels:**

1. **Qaboo Ya'ii** (Meetings Management)
2. **Karoora** (Planning & Strategic Development)
3. **Gabaasa** (Reporting & Documentation)
4. **Projectoota** (Projects & Initiatives)
5. **Gamaggama** (Evaluation & Assessment)

---

## 🗺️ **Current Organizational Geography**

### **Global Level**
- **Waliigalaa Global** (Global Executive Board)

### **Godina (Continental) Levels**
1. **Afrikaa** (Africa)
2. **Asiyaa fi Gidduu Galeessa Bahaa** (Asia & Middle East)
3. **Auwustraliyaa** (Australia)
4. **Awuroopaa** (Europe)
5. **Kaanadaa** (Canada)
6. **USA** (United States)

### **Sample Gamta (Country Groups) under Afrikaa**
- Afrikaa Kibbaa (South Africa)
- Ejiipt (Egypt) 
- Jibuutii (Djibouti)
- Keenyaa (Kenya)
- Somaliyaa (Somalia)
- Ugaandaa (Uganda)

---

## 🏗️ **System Architecture Requirements**

### **Technology Stack**
- **Backend:** PHP 8+ with Custom MVC Framework
- **Database:** MySQL 5.7+ with UTF-8 support
- **Frontend:** Bootstrap 5, Vanilla JavaScript, HTML5, CSS3
- **Security:** bcrypt, CSRF protection, prepared statements

### **Core System Modules Required**

#### **1. User Management & Hierarchy**
- **Registration Logic:** 
  - Members register at Gurmu level
  - Executives can be registered at Global/System Admin level
  - Hierarchy selection (Godina → Gamta → Gurmu)
  - Position and responsibility assignment

#### **2. Meeting Management System**
- **Features:**
  - Internal meeting system (Jitsi integration recommended)
  - Calendar integration
  - Attendees management
  - Meeting minutes recording
  - Links to tasks, reports, projects
  - Automatic follow-up task creation

#### **3. Task Management System**
- **Cross-functional capabilities:**
  - Individual and multi-person assignments
  - Cross-team collaboration
  - Hierarchy-based task delegation
  - Task dependencies and subtasks
  - Progress tracking and reporting

#### **4. Event Management Module**
- **Features:**
  - Global events with cascading participation
  - Godina/Gamta/Gurmu-level sub-events
  - Task assignment per participation level
  - Preparation tracking
  - Cross-functional collaboration

#### **5. Education & Training Module**
- **Learning Management System:**
  - Lesson plans and categories
  - Required lessons per role/level
  - Progress tracking
  - Certificates and compliance
  - Multi-level course delivery

#### **6. Finance Management (Dinagdee)**
- **Core Features:**
  - Donation tracking and receipt generation
  - Budget management per level
  - Expense approval workflows
  - Financial reporting
  - Payment integration

---

## 🔐 **Permissions & Access Control**

### **Role-Based Access by Level**

#### **Global Level**
- **Access:** All Godinas, Gamtas, Gurmus
- **Permissions:** System-wide administration, global reporting
- **Registration:** System Admin only

#### **Godina Level**
- **Access:** All Gamtas and Gurmus within their Godina
- **Permissions:** Regional management, cross-Gamta coordination
- **Registration:** Global Admin approval required

#### **Gamta Level**
- **Access:** All Gurmus within their Gamta
- **Permissions:** Local area management, Gurmu coordination
- **Registration:** Godina Admin approval required

#### **Gurmu Level**
- **Access:** Own Gurmu and members only
- **Permissions:** Local group management, member services
- **Registration:** Gamta Admin approval required

---

## 📊 **Data Flow & Integration**

### **Membership Model**
- **Base Rule:** ALL users belong to a specific Gurmu
- **Executive Roles:** Higher-level executives maintain Gurmu membership but have expanded access scope
- **Reporting Chain:** Gurmu → Gamta → Godina → Global

### **Task Flow Example**
```
Global creates strategic initiative
   ↓
Assigned to relevant Godinas
   ↓  
Godina delegates to Gamtas
   ↓
Gamta assigns to Gurmus
   ↓
Gurmu executes with members
   ↓
Progress reports flow upward
```

---

## 🎓 **Module Integration Matrix**

| Module | Global | Godina | Gamta | Gurmu | Integration Points |
|--------|--------|---------|-------|-------|-------------------|
| **Meetings** | ✅ | ✅ | ✅ | ✅ | Tasks, Reports, Calendar |
| **Tasks** | ✅ | ✅ | ✅ | ✅ | Events, Projects, Reports |
| **Events** | ✅ | ✅ | ✅ | ✅ | Tasks, Finance, Reports |
| **Education** | ✅ | ✅ | ✅ | ✅ | User Roles, Compliance |
| **Finance** | ✅ | ✅ | ✅ | ✅ | Events, Reports, Audit |
| **Reports** | ✅ | ✅ | ✅ | ✅ | All Modules |

---

## 🚀 **Implementation Priorities**

### **Phase 1 (MVP - 6-8 weeks)**
1. ✅ User registration with hierarchy selection
2. ✅ Basic authentication and RBAC
3. ✅ Task management with multi-assignment
4. ✅ Meeting scheduling with basic integration
5. ✅ Finance module with donation receipts

### **Phase 2 (Enhanced Features)**
1. 📚 Education module with LMS capabilities
2. 🎯 Advanced event management
3. 📊 Comprehensive reporting system
4. 🔄 Workflow automation

### **Phase 3 (Advanced Features)**
1. 📱 Mobile PWA optimization
2. 🌐 Multi-language support (English/Oromo)
3. 📈 Advanced analytics and dashboards
4. 🔐 Enhanced security and audit features

---

## 📝 **Development Guidelines**

### **Database Design Principles**
- **Hierarchy Tables:** `godinas`, `gamtas`, `gurmus` with proper foreign keys
- **User Association:** Every user linked to a specific `gurmu_id`
- **Position Assignment:** Flexible position-responsibility mapping
- **Access Control:** Level-based permissions with inherited scope

### **Security Implementation**
- **Registration Approval:** Admin approval required for new users
- **Hierarchical Visibility:** Users see only their scope + subordinates
- **Action Auditing:** All critical actions logged
- **Data Protection:** Prepared statements, CSRF protection

### **Integration Points**
- **Meeting → Tasks:** Auto-generate follow-up tasks from minutes
- **Events → Finance:** Budget tracking and expense approvals
- **Education → Compliance:** Required training tracking
- **Reports → All Modules:** Comprehensive analytics across all functions

---

## 🎯 **Success Metrics**

### **System Health Indicators**
- ✅ User registration and hierarchy assignment accuracy
- ✅ Task completion rates across levels
- ✅ Meeting attendance and follow-up completion
- ✅ Financial transaction accuracy and receipt generation
- ✅ Education module completion rates

### **Organizational Effectiveness**
- 📈 Cross-level collaboration metrics
- 📊 Project completion rates by level
- 💰 Financial transparency and reporting
- 🎓 Training compliance rates
- 📅 Meeting effectiveness and outcomes

---

## 📞 **Technical Support Structure**

### **For Developers**
- **Architecture:** Custom PHP MVC with clean URL routing
- **Database:** MySQL with comprehensive relational structure
- **Frontend:** Bootstrap 5 with progressive enhancement
- **Hosting:** Shared hosting compatible (HostGator/BlueHost)

### **For System Administrators**
- **User Management:** Hierarchical approval workflows
- **Data Management:** Comprehensive backup and audit systems
- **System Configuration:** Environment-based settings management
- **Monitoring:** Built-in audit logs and system health checks

---

*Last Updated: October 26, 2025*  
*Status: Ready for Development Implementation*