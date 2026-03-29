#!/bin/bash
# ABO-WBO Deployment Preparation Script
# This script validates and prepares files for safe deployment to HostGator

echo "🚀 ABO-WBO Deployment Preparation"
echo "=================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to check if file should be deployed
check_file_safety() {
    local file="$1"
    
    # Files/folders that should NEVER be deployed
    local dangerous_patterns=(
        "\.env$"
        "\.env\."
        "node_modules/"
        "\.git/"
        "\.vscode/"
        "\.idea/"
        "storage/logs/"
        "storage/cache/"
        "storage/sessions/"
        "storage/temp/"
        "\.log$"
        "Thumbs\.db"
        "\.DS_Store"
        "github-setup-commands\.txt"
        "PROJECT-COMPLETION\.md"
        "\.swp$"
        "\.swo$"
    )
    
    for pattern in "${dangerous_patterns[@]}"; do
        if [[ $file =~ $pattern ]]; then
            return 1  # Unsafe
        fi
    done
    
    return 0  # Safe
}

echo -e "${BLUE}Step 1: Analyzing current directory structure...${NC}"
echo ""

# Count files
total_files=$(find . -type f | wc -l)
echo -e "📊 Total files found: ${BLUE}$total_files${NC}"

echo ""
echo -e "${GREEN}✅ SAFE TO DEPLOY:${NC}"
echo "=================="

safe_count=0
while IFS= read -r -d '' file; do
    if check_file_safety "$file"; then
        echo -e "${GREEN}✓${NC} $file"
        ((safe_count++))
    fi
done < <(find . -type f -print0 | head -20)

echo ""
echo -e "${RED}❌ NEVER DEPLOY (Security Risk):${NC}"
echo "================================="

danger_count=0
while IFS= read -r -d '' file; do
    if ! check_file_safety "$file"; then
        echo -e "${RED}✗${NC} $file"
        ((danger_count++))
    fi
done < <(find . -type f -print0)

echo ""
echo "📈 DEPLOYMENT SUMMARY:"
echo "======================"
echo -e "Safe files: ${GREEN}$safe_count${NC}"
echo -e "Excluded files: ${RED}$danger_count${NC}"
echo -e "Deployment safety: ${GREEN}$(( safe_count * 100 / total_files ))%${NC}"

echo ""
echo -e "${YELLOW}🔍 CRITICAL FILES TO VERIFY:${NC}"
echo "============================="

# Check for critical files
if [ -f ".env" ]; then
    echo -e "${RED}⚠️  .env file detected - MUST NOT be deployed!${NC}"
else
    echo -e "${GREEN}✅ .env file not found (good)${NC}"
fi

if [ -d "node_modules" ]; then
    echo -e "${RED}⚠️  node_modules/ directory detected - MUST NOT be deployed!${NC}"
else
    echo -e "${GREEN}✅ node_modules/ not found (good)${NC}"
fi

if [ -d ".git" ]; then
    echo -e "${YELLOW}⚠️  .git/ directory detected - Use Git deployment instead${NC}"
else
    echo -e "${GREEN}✅ .git/ not found (good for file upload)${NC}"
fi

echo ""
echo -e "${BLUE}📋 DEPLOYMENT CHECKLIST:${NC}"
echo "========================"
echo "[ ] Verify .env is NOT in deployment files"
echo "[ ] Confirm database passwords are secure"
echo "[ ] Check file permissions will be set correctly"
echo "[ ] Ensure staging environment is configured"
echo "[ ] Backup current production if exists"
echo "[ ] Test staging before production deployment"

echo ""
echo -e "${GREEN}🎯 READY FOR HOSTGATOR DEPLOYMENT!${NC}"
echo "================================="
echo "Follow the HOSTGATOR-DEPLOYMENT-GUIDE.md for step-by-step instructions."

echo ""
echo -e "${YELLOW}⚠️  IMPORTANT REMINDERS:${NC}"
echo "- Create .env files directly on HostGator (never upload)"
echo "- Use Git deployment method in cPanel for best security"
echo "- Set proper file permissions (644 files, 755 directories)"
echo "- Test staging.j-abo-wbo.org before deploying to production"
echo "- Monitor error logs after deployment"