#!/bin/bash
# Route Testing Script for ABO-WBO Management System
# Tests all fixed hierarchy and email routes

echo "=================================================="
echo "ABO-WBO ROUTE TESTING - Hierarchy & Email Module"
echo "=================================================="
echo ""

BASE_URL="http://localhost"

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

test_route() {
    local url=$1
    local name=$2
    
    response=$(curl -s -o /dev/null -w "%{http_code}" "$url")
    
    if [ "$response" -eq 200 ] || [ "$response" -eq 302 ]; then
        echo -e "${GREEN}✓${NC} $name - HTTP $response"
    else
        echo -e "${RED}✗${NC} $name - HTTP $response (FAILED)"
    fi
}

echo "Testing Hierarchy Routes:"
echo "-------------------------"
test_route "$BASE_URL/hierarchy" "Hierarchy Index"
test_route "$BASE_URL/hierarchy/8?type=godina" "Godina Details (ID 8)"
test_route "$BASE_URL/hierarchy/9?type=godina" "Godina Details (ID 9)"
test_route "$BASE_URL/hierarchy/10?type=gamta" "Gamta Details (ID 10)"
test_route "$BASE_URL/hierarchy/25?type=gamta" "Gamta Details (ID 25)"
test_route "$BASE_URL/hierarchy/tree/view" "Hierarchy Tree View"

echo ""
echo "Testing Email Management Routes:"
echo "--------------------------------"
test_route "$BASE_URL/user-emails" "Email List"
test_route "$BASE_URL/user-emails?status=active" "Active Emails"
test_route "$BASE_URL/user-emails?status=inactive" "Inactive Emails"
test_route "$BASE_URL/user-emails/create" "Create Email Form"

echo ""
echo "Testing Dashboard:"
echo "------------------"
test_route "$BASE_URL/dashboard" "Main Dashboard"

echo ""
echo "=================================================="
echo "Testing Complete!"
echo "=================================================="
echo ""
echo "Note: Routes requiring authentication will return 302 (redirect to login)"
echo "This is expected behavior. 404 errors indicate broken routes."
echo ""
echo "Expected Results:"
echo "  - 200: Success (page loaded)"
echo "  - 302: Redirect (usually to login - this is GOOD)"
echo "  - 404: Not Found (this is BAD - route broken)"
echo ""
