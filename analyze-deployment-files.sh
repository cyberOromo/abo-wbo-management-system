#!/bin/bash
# 📦 DEPLOYMENT FILE ANALYZER
# This script identifies which files should be deployed to HostGator

echo "🔍 ANALYZING FILES FOR HOSTGATOR DEPLOYMENT..."
echo "=================================================="

echo ""
echo "✅ FILES THAT WILL BE DEPLOYED:"
echo "--------------------------------"

# Core application files
echo "📁 Core Application:"
find app/ -type f -name "*.php" | head -10
echo "   ... (all PHP files in app/)"

find config/ -type f -name "*.php" | head -5
echo "   ... (all config files)"

find resources/ -type f -name "*.php" | head -10
echo "   ... (all view files)"

echo ""
echo "📁 Public Assets:"
find public/ -type f \( -name "*.php" -o -name "*.css" -o -name "*.js" -o -name "*.png" -o -name "*.jpg" -o -name "*.ico" \) | head -10
echo "   ... (all public assets)"

echo ""
echo "📁 Database & Routes:"
ls -la routes/ database/ lang/ 2>/dev/null | grep -v "^total"

echo ""
echo "📁 Documentation:"
ls -la *.md 2>/dev/null | grep -E "(README|API|DEPLOYMENT)"

echo ""
echo "=================================================="
echo "❌ FILES THAT WILL BE EXCLUDED (DO NOT DEPLOY):"
echo "--------------------------------"

echo ""
echo "🔒 Sensitive Files:"
find . -maxdepth 2 -name ".env*" -type f 2>/dev/null
find . -name "*.log" -type f 2>/dev/null | head -5

echo ""
echo "🔧 Development Files:"
ls -la | grep -E "(\.git|node_modules|\.vscode|\.idea)"

echo ""
echo "📝 Local Files:"
find . -name "*local*" -o -name "*XAMPP*" -o -name "*test*" | grep -v "vendor" | head -5

echo ""
echo "=================================================="
echo "📊 DEPLOYMENT SUMMARY:"
echo "--------------------------------"

# Count files to be deployed
APP_FILES=$(find app/ -type f -name "*.php" 2>/dev/null | wc -l)
CONFIG_FILES=$(find config/ -type f -name "*.php" 2>/dev/null | wc -l)
VIEW_FILES=$(find resources/ -type f -name "*.php" 2>/dev/null | wc -l)
PUBLIC_FILES=$(find public/ -type f 2>/dev/null | wc -l)

echo "📁 App Controllers/Models: $APP_FILES files"
echo "⚙️  Configuration Files: $CONFIG_FILES files"
echo "🎨 View Templates: $VIEW_FILES files"
echo "🌐 Public Assets: $PUBLIC_FILES files"

# Calculate total size
TOTAL_SIZE=$(du -sh . 2>/dev/null | cut -f1)
echo "💾 Total Project Size: $TOTAL_SIZE"

echo ""
echo "✅ Ready for HostGator deployment!"
echo ""
echo "📋 NEXT STEPS:"
echo "1. Follow HOSTGATOR-DEPLOYMENT-STEPS.md"
echo "2. Create deployment archive with ONLY the ✅ files above"
echo "3. Upload to HostGator cPanel"
echo "4. Configure .env on server (DO NOT upload local .env)"
echo ""