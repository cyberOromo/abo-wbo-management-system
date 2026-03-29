#!/bin/bash

# ============================================================================
# STAGING DATABASE IMPORT SCRIPT
# Imports complete schema and data to staging.j-abo-wbo.org
# ============================================================================

# Database credentials
DB_HOST="localhost"
DB_NAME="jabowbo_abo_staging"
DB_USER="jabowbo_abo_user"
DB_PASS="your_password_here"

# Color codes for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}============================================================================${NC}"
echo -e "${GREEN}ABO-WBO STAGING DATABASE IMPORT${NC}"
echo -e "${GREEN}============================================================================${NC}"
echo ""

# Step 1: Drop all existing tables
echo -e "${YELLOW}Step 1: Dropping all existing tables...${NC}"
mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < drop_all_tables.sql
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ All tables dropped successfully${NC}"
else
    echo -e "${RED}✗ Failed to drop tables${NC}"
    exit 1
fi
echo ""

# Step 2: Import schema
echo -e "${YELLOW}Step 2: Importing schema (38 tables + 3 views)...${NC}"
mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < schema.sql
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Schema imported successfully${NC}"
    
    # Count tables
    TABLE_COUNT=$(mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "SHOW TABLES;" | wc -l)
    echo -e "${GREEN}  Tables created: $((TABLE_COUNT - 1))${NC}"
else
    echo -e "${RED}✗ Failed to import schema${NC}"
    exit 1
fi
echo ""

# Step 3: Import data
echo -e "${YELLOW}Step 3: Importing organizational data...${NC}"
mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < comprehensive_data_insertion.sql
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Data imported successfully${NC}"
else
    echo -e "${RED}✗ Failed to import data${NC}"
    exit 1
fi
echo ""

# Step 4: Verification
echo -e "${YELLOW}Step 4: Verifying import...${NC}"

# Check table counts
GODINAS=$(mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -se "SELECT COUNT(*) FROM godinas;")
GAMTAS=$(mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -se "SELECT COUNT(*) FROM gamtas;")
GURMUS=$(mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -se "SELECT COUNT(*) FROM gurmus;")
POSITIONS=$(mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -se "SELECT COUNT(*) FROM positions;")
IND_RESP=$(mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -se "SELECT COUNT(*) FROM individual_responsibilities;")
SHARED_RESP=$(mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -se "SELECT COUNT(*) FROM shared_responsibilities;")
USERS=$(mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -se "SELECT COUNT(*) FROM users;")

echo -e "${GREEN}Data counts:${NC}"
echo -e "  Godinas: ${GODINAS} (expected: 6)"
echo -e "  Gamtas: ${GAMTAS} (expected: 20)"
echo -e "  Gurmus: ${GURMUS} (expected: 48)"
echo -e "  Positions: ${POSITIONS} (expected: 7)"
echo -e "  Individual Responsibilities: ${IND_RESP} (expected: 35)"
echo -e "  Shared Responsibilities: ${SHARED_RESP} (expected: 5)"
echo -e "  Users: ${USERS} (expected: 1 system admin)"
echo ""

# Check system admin user
ADMIN_EXISTS=$(mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -se "SELECT COUNT(*) FROM users WHERE email='admin@abo-wbo.org' AND user_type='system_admin';")
if [ "$ADMIN_EXISTS" -eq 1 ]; then
    echo -e "${GREEN}✓ System admin user verified (admin@abo-wbo.org)${NC}"
else
    echo -e "${RED}✗ System admin user not found!${NC}"
fi
echo ""

# Final summary
echo -e "${GREEN}============================================================================${NC}"
echo -e "${GREEN}IMPORT COMPLETE!${NC}"
echo -e "${GREEN}============================================================================${NC}"
echo ""
echo -e "Staging Site: ${GREEN}https://staging.j-abo-wbo.org${NC}"
echo -e "Login: ${GREEN}admin@abo-wbo.org${NC}"
echo -e "Password: ${GREEN}admin123${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo -e "1. Test login functionality"
echo -e "2. Verify user_type based dashboard routing"
echo -e "3. Test member registration at Gurmu level"
echo -e "4. Test executive position assignment"
echo ""
