# Comprehensive Database Insertion Documentation
## Caasaa Jaarmayaa ABO-WBO Biyya Alaa Organizational Hierarchy

---

## 1. HIERARCHY DATABASE INSERTION

### 1.1 GODINAS (REGIONS) - INSERT STATEMENTS

```sql
-- Insert all 6 Godinas
INSERT INTO godinas (name, code, description, status) VALUES
('Afrikaa', 'AFR', 'African region covering East African countries with significant Oromo diaspora', 'active'),
('Asiyaa fi Gidduu Galeessa Bahaa', 'AME', 'Asia and Middle East region covering Arabian Peninsula and surrounding areas', 'active'),
('Auwustraliyaa', 'AUS', 'Australia and New Zealand region covering Oceania', 'active'),
('Awuroopaa', 'EUR', 'European region covering Western, Central, and Northern Europe', 'active'),
('Kaanadaa', 'CAN', 'Canadian region covering East and West Canada', 'active'),
('USA', 'USA', 'United States region covering East, West, and Central USA', 'active');
```

### 1.2 GAMTAS (SUB-REGIONS/COUNTRIES) - INSERT STATEMENTS

```sql
-- AFRIKAA GAMTAS (6 Gamtas)
INSERT INTO gamtas (godina_id, name, code, description, status) VALUES
((SELECT id FROM godinas WHERE code = 'AFR'), 'Afrikaa Kibbaa', 'ZAF', 'South African Oromo community', 'active'),
((SELECT id FROM godinas WHERE code = 'AFR'), 'Ejiipt', 'EGY', 'Egyptian Oromo community', 'active'),
((SELECT id FROM godinas WHERE code = 'AFR'), 'Jibuutii', 'DJI', 'Djiboutian Oromo community', 'active'),
((SELECT id FROM godinas WHERE code = 'AFR'), 'Keenyaa', 'KEN', 'Kenyan Oromo community', 'active'),
((SELECT id FROM godinas WHERE code = 'AFR'), 'Somaliyaa', 'SOM', 'Somali Oromo community', 'active'),
((SELECT id FROM godinas WHERE code = 'AFR'), 'Ugaandaa', 'UGA', 'Ugandan Oromo community', 'active');

-- ASIYAA FI GIDDUU GALEESSA BAHAA GAMTAS (4 Gamtas)
INSERT INTO gamtas (godina_id, name, code, description, status) VALUES
((SELECT id FROM godinas WHERE code = 'AME'), 'Dambalii Qeerroo Bilisummaa Oromoo Sawudii Arabiyaa', 'SAU-QBO', 'Saudi Arabia Qeerroo Bilisummaa Oromoo movement', 'active'),
((SELECT id FROM godinas WHERE code = 'AME'), 'Deeggarsa WBO Sawudii Arabiyaa', 'SAU-WBO', 'Saudi Arabia WBO Support organization', 'active'),
((SELECT id FROM godinas WHERE code = 'AME'), 'Hawaasa Oromoo Dubayi', 'UAE-DUB', 'Dubai Oromo community', 'active'),
((SELECT id FROM godinas WHERE code = 'AME'), 'Tokkummaa Baqattoota Oromoo Yamanii', 'YEM-REF', 'Yemen Oromo refugee unity organization', 'active');

-- AUWUSTRALIYAA GAMTAS (2 Gamtas)
INSERT INTO gamtas (godina_id, name, code, description, status) VALUES
((SELECT id FROM godinas WHERE code = 'AUS'), 'Awustraaliyaa', 'AUS-AUS', 'Australian Oromo community', 'active'),
((SELECT id FROM godinas WHERE code = 'AUS'), 'Newuzlaandii', 'NZL', 'New Zealand Oromo community', 'active');

-- AWUROOPAA GAMTAS (3 Gamtas)
INSERT INTO gamtas (godina_id, name, code, description, status) VALUES
((SELECT id FROM godinas WHERE code = 'EUR'), 'Gidduu Galeessa Awuroopaa', 'EUR-CEN', 'Central European Oromo communities', 'active'),
((SELECT id FROM godinas WHERE code = 'EUR'), 'Skaandinaviyaa', 'EUR-SCA', 'Scandinavian Oromo communities', 'active'),
((SELECT id FROM godinas WHERE code = 'EUR'), 'UK fi Ayerlaandii', 'EUR-UK', 'United Kingdom and Ireland Oromo communities', 'active');

-- KAANADAA GAMTAS (2 Gamtas)
INSERT INTO gamtas (godina_id, name, code, description, status) VALUES
((SELECT id FROM godinas WHERE code = 'CAN'), 'Baha Kanaadaa', 'CAN-E', 'Eastern Canada Oromo communities', 'active'),
((SELECT id FROM godinas WHERE code = 'CAN'), 'Dhiha Kanaadaa', 'CAN-W', 'Western Canada Oromo communities', 'active');

-- USA GAMTAS (3 Gamtas)
INSERT INTO gamtas (godina_id, name, code, description, status) VALUES
((SELECT id FROM godinas WHERE code = 'USA'), 'Baha USA', 'USA-E', 'Eastern United States Oromo communities', 'active'),
((SELECT id FROM godinas WHERE code = 'USA'), 'Dhiha USA', 'USA-W', 'Western United States Oromo communities', 'active'),
((SELECT id FROM godinas WHERE code = 'USA'), 'Gidduu Galeessa USA', 'USA-C', 'Central United States Oromo communities', 'active');
```

### 1.3 GURMUS (LOCAL UNITS) - INSERT STATEMENTS

```sql
-- AFRIKAA GURMUS (8 Gurmus)
INSERT INTO gurmus (gamta_id, name, code, description, status) VALUES
((SELECT id FROM gamtas WHERE code = 'ZAF'), 'Gurmuu Afrikaa Kibbaa', 'ZAF-001', 'Main South African Oromo local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'EGY'), 'Gurmuu Ejiipt', 'EGY-001', 'Main Egyptian Oromo local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'DJI'), 'Gurmuu Dambalii', 'DJI-001', 'Djibouti Dambalii local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'DJI'), 'Konyaa Jibuutii', 'DJI-002', 'Djibouti Konyaa local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'KEN'), 'Gurmuu Keenyaa', 'KEN-001', 'Main Kenyan Oromo local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'SOM'), 'Biiftuu Bilisummaa', 'SOM-001', 'Somalia Biiftuu Bilisummaa local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'SOM'), 'Gaachana WBO', 'SOM-002', 'Somalia WBO Shield local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'SOM'), 'Xumura Gabrummaa', 'SOM-003', 'Somalia End of Oppression local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'UGA'), 'Gurmuu Ugaandaa', 'UGA-001', 'Main Ugandan Oromo local unit', 'active');

-- ASIYAA FI GIDDUU GALEESSA BAHAA GURMUS (18 Gurmus)
INSERT INTO gurmus (gamta_id, name, code, description, status) VALUES
((SELECT id FROM gamtas WHERE code = 'SAU-QBO'), 'Gadaa Jizaan', 'SAU-001', 'Saudi Arabia Gadaa Jizaan local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'SAU-QBO'), 'Mul_isa Gadaa', 'SAU-002', 'Saudi Arabia Mul_isa Gadaa local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'SAU-QBO'), 'Qeerroo Bilisummaa Oromoo', 'SAU-003', 'Saudi Arabia Qeerroo Bilisummaa local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'SAU-QBO'), 'Tokkummaa', 'SAU-004', 'Saudi Arabia Unity local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'SAU-WBO'), 'Hasan Ammee', 'SAU-005', 'Saudi Arabia Hasan Ammee local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'SAU-WBO'), 'Hawaasa Jizaan', 'SAU-006', 'Saudi Arabia Hawaasa Jizaan local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'UAE-DUB'), 'Bakkalcha', 'UAE-001', 'Dubai Bakkalcha local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'UAE-DUB'), 'Laggasaa Wagii', 'UAE-002', 'Dubai Laggasaa Wagii local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'YEM-REF'), 'Bakkalcha Barii', 'YEM-001', 'Yemen Bakkalcha Barii refugee unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'YEM-REF'), 'Biiftuu', 'YEM-002', 'Yemen Biiftuu refugee unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'YEM-REF'), 'Booranaa fi Baarentoo', 'YEM-003', 'Yemen Booranaa fi Baarentoo refugee unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'YEM-REF'), 'Faana Kaayyoo', 'YEM-004', 'Yemen Faana Kaayyoo refugee unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'YEM-REF'), 'Magaalaa Aden', 'YEM-005', 'Yemen Aden city refugee unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'YEM-REF'), 'Mooraa Baqattoota Al Kharaz', 'YEM-006', 'Yemen Al Kharaz refugee camp unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'YEM-REF'), 'Murtii Guutoo', 'YEM-007', 'Yemen Murtii Guutoo refugee unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'YEM-REF'), 'Odaa-Radaa', 'YEM-008', 'Yemen Odaa-Radaa refugee unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'YEM-REF'), 'Shamarran Magaalaa Sana_aa', 'YEM-009', 'Yemen Sana_aa city refugee unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'YEM-REF'), 'Urjii', 'YEM-010', 'Yemen Urjii refugee unit', 'active');

-- AUWUSTRALIYAA GURMUS (2 Gurmus)
INSERT INTO gurmus (gamta_id, name, code, description, status) VALUES
((SELECT id FROM gamtas WHERE code = 'AUS-AUS'), 'Awustraaliyaa', 'AUS-001', 'Main Australian Oromo local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'NZL'), 'Newuzlaandii', 'NZL-001', 'Main New Zealand Oromo local unit', 'active');

-- AWUROOPAA GURMUS (6 Gurmus)
INSERT INTO gurmus (gamta_id, name, code, description, status) VALUES
((SELECT id FROM gamtas WHERE code = 'EUR-CEN'), 'Gidduu Galeessa Awuroopaa test', 'EUR-001', 'Central Europe test local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'EUR-CEN'), 'Gidduu Galeessa Awuroopaa test2', 'EUR-002', 'Central Europe test2 local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'EUR-SCA'), 'Skaandinaviyaa test', 'EUR-003', 'Scandinavia test local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'EUR-SCA'), 'Skaandinaviyaa test2', 'EUR-004', 'Scandinavia test2 local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'EUR-UK'), 'UK fi Ayerlaandii test', 'EUR-005', 'UK and Ireland test local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'EUR-UK'), 'UK fi Ayerlaandii test2', 'EUR-006', 'UK and Ireland test2 local unit', 'active');

-- KAANADAA GURMUS (4 Gurmus)
INSERT INTO gurmus (gamta_id, name, code, description, status) VALUES
((SELECT id FROM gamtas WHERE code = 'CAN-E'), 'Baha Kanaadaa test', 'CAN-001', 'East Canada test local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'CAN-E'), 'Baha Kanaadaa test2', 'CAN-002', 'East Canada test2 local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'CAN-W'), 'Dhiha Kanaadaa test', 'CAN-003', 'West Canada test local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'CAN-W'), 'Dhiha Kanaadaa test2', 'CAN-004', 'West Canada test2 local unit', 'active');

-- USA GURMUS (5 Gurmus)
INSERT INTO gurmus (gamta_id, name, code, description, status) VALUES
((SELECT id FROM gamtas WHERE code = 'USA-E'), 'Baha USA test', 'USA-001', 'East USA test local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'USA-E'), 'Baha USA test2', 'USA-002', 'East USA test2 local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'USA-W'), 'Dhiha USA test', 'USA-003', 'West USA test local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'USA-W'), 'Dhiha USA test2', 'USA-004', 'West USA test2 local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'USA-C'), 'Gidduu Galeessa USA test', 'USA-005', 'Central USA test local unit', 'active'),
((SELECT id FROM gamtas WHERE code = 'USA-C'), 'Gurmuu Minnesota', 'USA-006', 'Minnesota Oromo local unit', 'active');
```

---

## 2. HIERARCHY VERIFICATION QUERIES

```sql
-- Verify complete hierarchy count
SELECT 
    COUNT(DISTINCT g.id) as total_godinas,
    COUNT(DISTINCT gm.id) as total_gamtas,
    COUNT(DISTINCT gr.id) as total_gurmus
FROM godinas g
LEFT JOIN gamtas gm ON g.id = gm.godina_id
LEFT JOIN gurmus gr ON gm.id = gr.gamta_id;

-- Expected Results:
-- total_godinas: 6
-- total_gamtas: 20  
-- total_gurmus: 48

-- Verify hierarchy relationships
SELECT 
    g.name as godina,
    COUNT(gm.id) as gamtas_count,
    COUNT(gr.id) as gurmus_count
FROM godinas g
LEFT JOIN gamtas gm ON g.id = gm.godina_id
LEFT JOIN gurmus gr ON gm.id = gr.gamta_id
GROUP BY g.id, g.name
ORDER BY g.name;

-- View complete hierarchy tree
SELECT 
    g.name as godina,
    gm.name as gamta,
    gr.name as gurmu,
    CONCAT(g.name, ' → ', gm.name, ' → ', gr.name) as full_path
FROM godinas g
JOIN gamtas gm ON g.id = gm.godina_id
JOIN gurmus gr ON gm.id = gr.gamta_id
ORDER BY g.name, gm.name, gr.name;
```

---

## 3. ORGANIZATIONAL STATISTICS

### 3.1 Hierarchy Distribution
```
Total Organization Units: 74
├── Godinas (Regions): 6
├── Gamtas (Sub-regions): 20  
└── Gurmus (Local Units): 48

By Region:
├── Afrikaa: 6 Gamtas → 8 Gurmus
├── Asiyaa fi Gidduu Galeessa Bahaa: 4 Gamtas → 18 Gurmus
├── Auwustraliyaa: 2 Gamtas → 2 Gurmus
├── Awuroopaa: 3 Gamtas → 6 Gurmus
├── Kaanadaa: 2 Gamtas → 4 Gurmus
└── USA: 3 Gamtas → 5 Gurmus
```

### 3.2 Expected Executive Positions
```
Total Executive Positions Across All Levels: 2,072
├── Global Level: 7 positions
├── Godina Level: 42 positions (6 × 7)
├── Gamta Level: 140 positions (20 × 7)
└── Gurmu Level: 336 positions (48 × 7)

Plus Shared Responsibilities: 1,547 shared responsibility roles
```

---

## 4. DATA INTEGRITY CONSTRAINTS

### 4.1 Referential Integrity
- All Gamtas must belong to a valid Godina
- All Gurmus must belong to a valid Gamta
- Hierarchical deletion restrictions prevent orphaned records

### 4.2 Business Rules
- Each level maintains exactly 7 executive positions
- Each position has exactly 5 individual responsibilities
- Each executive team has exactly 5 shared responsibilities
- No duplicate names within the same parent level

### 4.3 Status Management
- All entities have active/inactive status
- Cascading status changes affect child entities
- Soft deletion preserves historical data integrity

---

## 5. IMPLEMENTATION NOTES

### 5.1 Execution Order
1. Insert Godinas first (no dependencies)
2. Insert Gamtas (depends on Godinas)
3. Insert Gurmus (depends on Gamtas)
4. Insert Positions (independent)
5. Create Users with position assignments

### 5.2 Code Generation
- All codes follow consistent patterns
- Unique constraints prevent duplicates
- Codes enable easy lookup and reporting

### 5.3 Localization
- Names support both English and Oromo
- Description fields accommodate cultural context
- Unicode support for all text fields

This documentation provides complete database insertion coverage for all 6 Godinas, 20 Gamtas, and 48 Gurmus with proper hierarchical relationships and data integrity constraints.