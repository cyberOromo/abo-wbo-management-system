#!/bin/bash

# Deploy Controller and Model Fixes to Staging
# This script uploads only the fixed files to HostGator staging

echo "================================================"
echo "Deploying Controller & Model Fixes to Staging"
echo "================================================"
echo ""

# Modified files to upload
FILES=(
    "app/helpers.php"
    "app/Controllers/HierarchyController.php"
    "app/Controllers/PositionController.php"
    "app/Controllers/MeetingController.php"
    "app/Controllers/EventController.php"
    "app/Controllers/TaskController.php"
    "app/Controllers/DonationController.php"
    "app/Controllers/FinanceController.php"
    "app/Models/Meeting.php"
    "app/Models/Event.php"
)

echo "Files to upload:"
for file in "${FILES[@]}"; do
    echo "  - $file"
done
echo ""

read -p "Upload these files to staging? (y/n): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo ""
    echo "✅ Files ready for upload!"
    echo ""
    echo "Upload via cPanel File Manager:"
    echo "1. Go to https://staging.j-abo-wbo.org:2083"
    echo "2. File Manager → public_html/"
    echo "3. Navigate to each directory and upload corresponding files"
    echo "4. Replace existing files when prompted"
    echo ""
    echo "Or use FTP/SFTP with these credentials:"
    echo "  Host: ftp.j-abo-wbo.org"
    echo "  User: jabowbo"
    echo "  Port: 21 (FTP) or 22 (SFTP)"
    echo ""
    echo "After upload, test:"
    echo "  - https://staging.j-abo-wbo.org/dashboard"
    echo "  - https://staging.j-abo-wbo.org/hierarchy"
    echo "  - https://staging.j-abo-wbo.org/positions"
    echo "  - https://staging.j-abo-wbo.org/meetings"
    echo "  - https://staging.j-abo-wbo.org/events"
    echo "  - https://staging.j-abo-wbo.org/tasks"
    echo "  - https://staging.j-abo-wbo.org/donations"
    echo ""
else
    echo "Deployment cancelled."
fi
