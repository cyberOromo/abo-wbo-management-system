# 📋 HostGator cPanel Quick Setup Checklist

## 🎯 IMMEDIATE ACTIONS (Do These First)

### 1. Create Subdomain (5 minutes)
```
✓ cPanel → Subdomains
✓ Create: staging.j-abo-wbo.org → /public_html/staging
✓ Create: j-abo-wbo.org/app → /public_html/app (manually create folder)
```

### 2. Create Databases (5 minutes)
```
✓ cPanel → MySQL Databases
✓ Create Database: [your_cpanel_user]_abo_staging
✓ Create Database: [your_cpanel_user]_abo_production
✓ Create User: [your_cpanel_user]_abo_user
✓ Assign User to Both Databases (ALL PRIVILEGES)
✓ SAVE DATABASE PASSWORD SECURELY!
```

### 3. Set Up Git Deployment (10 minutes)
```
✓ cPanel → Git Version Control
✓ Clone to staging:
  - Repository: https://github.com/cyberOromo/abo-wbo-management-system.git
  - Branch: develop
  - Path: /public_html/staging

✓ Clone to production:
  - Repository: https://github.com/cyberOromo/abo-wbo-management-system.git
  - Branch: main
  - Path: /public_html/app
```

## 🔧 CONFIGURATION (Do These Next)

### 4. Create Environment Files (Critical - 10 minutes)
```
✓ File Manager → /public_html/staging → Create .env
✓ File Manager → /public_html/app → Create .env
✓ Use the exact .env templates from the deployment guide
✓ Update database credentials with YOUR actual passwords
```

### 5. Set File Permissions (5 minutes)
```
✓ Select all files in /public_html/staging
✓ Right-click → Permissions → 644 files, 755 directories
✓ Check "Recurse into subdirectories"
✓ Repeat for /public_html/app
```

### 6. Import Database Schema (10 minutes)
```
✓ cPanel → phpMyAdmin
✓ Select staging database
✓ Import → database/schema.sql
✓ Import → database/comprehensive_data_insertion.sql
✓ Repeat for production database
```

## 🧪 TESTING (Critical Step)

### 7. Test Staging Site (15 minutes)
```
✓ Visit: https://staging.j-abo-wbo.org
✓ Check: Homepage loads
✓ Check: Login page works
✓ Check: Database connection
✓ Test: Tasks module
✓ Test: Meetings module
✓ Test: Events module
✓ Test: Donations module
✓ Check: cPanel → Error Logs for any issues
```

### 8. Test Production Site (10 minutes)
```
✓ Visit: https://j-abo-wbo.org/app
✓ Verify all functionality works
✓ Check error logs
```

## ⚡ QUICK TROUBLESHOOTING

### If Homepage Shows 500 Error:
```
1. Check cPanel → Error Logs
2. Verify .env file exists and has correct database credentials
3. Check file permissions (644 for files, 755 for directories)
4. Verify .htaccess file is correct
```

### If Database Connection Fails:
```
1. Verify database name, username, password in .env
2. Check database user has privileges
3. Test connection in phpMyAdmin
```

### If Files Don't Load:
```
1. Check Git deployment pulled all files
2. Verify file permissions are set
3. Check if public folder is accessible
```

## 🎯 SUCCESS INDICATORS

### You'll Know It's Working When:
```
✅ staging.j-abo-wbo.org shows ABO-WBO homepage
✅ j-abo-wbo.org/app shows ABO-WBO homepage
✅ Login pages are accessible
✅ Modern UI modules load without errors
✅ No errors in cPanel error logs
✅ Database connections work
```

## 📞 NEED HELP?

### Before Asking for Help, Check:
```
1. Are database credentials correct in .env files?
2. Did Git deployment complete successfully?
3. Are file permissions set correctly?
4. What do the error logs show?
5. Can you access phpMyAdmin?
```

### Common Issues:
```
❌ "Database connection failed"
   → Check database credentials in .env

❌ "500 Internal Server Error"  
   → Check error logs and file permissions

❌ "Page not found"
   → Verify Git deployment pulled all files

❌ "Permission denied"
   → Set proper file permissions (644/755)
```

---

## ⏰ ESTIMATED TOTAL TIME: 45-60 MINUTES

### Time Breakdown:
- Setup (databases, subdomains): 15 minutes
- Git deployment: 10 minutes  
- Configuration: 15 minutes
- Testing: 15 minutes

**🚀 After following this checklist, your ABO-WBO system will be live and ready for testing!**