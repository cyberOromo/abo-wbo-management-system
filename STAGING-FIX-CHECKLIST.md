# 🚨 STAGING SERVER FIX - COMPREHENSIVE CHECKLIST

## 🔍 ROOT CAUSE IDENTIFIED:
**Sessions cannot be created because `storage/sessions/` directory doesn't exist!**
- This causes `csrf_token()` to fail silently
- Form submission blocked without CSRF token
- No POST request sent to server

---

## ✅ COMPLETE FIX STEPS (Do ALL of these):

### **Step 1: Create ALL Missing Storage Directories**

In cPanel File Manager, navigate to:
```
/home2/jabowbo/staging.j-abo-wbo.org/storage/
```

Click **"+ Folder"** and create these directories:
1. `sessions`
2. `cache`  
3. `temp`
4. `uploads`

**Set permissions to 777 for EACH:**
- Right-click folder → Change Permissions
- Check ALL 9 boxes (Read, Write, Execute for User, Group, World)
- Click "Change Permissions"

---

### **Step 2: Upload Fixed AuthController.php**

Navigate to:
```
/home2/jabowbo/staging.j-abo-wbo.org/app/Controllers/
```

1. **Delete** existing `AuthController.php`
2. **Upload** new version from: `c:\xampp\htdocs\abo-wbo\app\Controllers\AuthController.php`
3. Set permissions to **644**

---

### **Step 3: Verify PHP Session Configuration**

In cPanel File Manager, check if `php.ini` or `.user.ini` exists in:
```
/home2/jabowbo/staging.j-abo-wbo.org/
```

If it doesn't exist, create `.user.ini` with this content:
```ini
session.save_path = "/home2/jabowbo/staging.j-abo-wbo.org/storage/sessions"
session.gc_probability = 1
session.gc_divisor = 100
session.gc_maxlifetime = 7200
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
max_execution_time = 300
```

Set `.user.ini` permissions to **644**

---

### **Step 4: Verify .htaccess in Public Directory**

Navigate to:
```
/home2/jabowbo/staging.j-abo-wbo.org/public/
```

**Enable "Show Hidden Files"** (Settings gear icon)

Check if `.htaccess` exists. If not, create it with:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Disable directory browsing
Options -Indexes

# Set default charset
AddDefaultCharset UTF-8

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "DENY"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Deny access to sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files ".htaccess">
    Order allow,deny
    Deny from all
</Files>
```

Set `.htaccess` permissions to **644**

---

### **Step 5: Test Session Creation**

Create a test file to verify sessions work:

In `/home2/jabowbo/staging.j-abo-wbo.org/public/`, create `test-session.php`:

```php
<?php
// Start session
session_start();

// Set a test value
$_SESSION['test'] = 'Sessions are working!';

// Display result
echo '<h1>Session Test</h1>';
echo '<p>Session ID: ' . session_id() . '</p>';
echo '<p>Session Data: ' . print_r($_SESSION, true) . '</p>';
echo '<p>Session Save Path: ' . session_save_path() . '</p>';

// Check if sessions directory is writable
$sessionPath = '/home2/jabowbo/staging.j-abo-wbo.org/storage/sessions';
echo '<p>Sessions directory writable: ' . (is_writable($sessionPath) ? 'YES ✅' : 'NO ❌') . '</p>';
```

Visit: `https://staging.j-abo-wbo.org/test-session.php`

**Expected output:**
```
Session ID: [some random string]
Session Data: Array ( [test] => Sessions are working! )
Sessions directory writable: YES ✅
```

**If you see errors**, sessions aren't working.

---

### **Step 6: Clear Browser Cache and Test Login**

1. **Clear browser cache**: Ctrl + Shift + Delete (clear everything)
2. **Close and reopen browser**
3. Navigate to: `https://staging.j-abo-wbo.org/`
4. Open Developer Tools (F12) → Network tab
5. Enter credentials:
   - Email: `admin@abo-wbo.org`
   - Password: `admin123`
6. Click "Sign In"
7. **Watch Network tab** - You should now see a **POST** request to `/auth/login`

---

### **Step 7: Check Application Logs**

After login attempt, check:
```
/home2/jabowbo/staging.j-abo-wbo.org/storage/logs/
```

Look for new log files with error messages.

---

## 🎯 QUICK VERIFICATION CHECKLIST:

```
☐ storage/sessions/ exists with 777 permissions
☐ storage/cache/ exists with 777 permissions  
☐ storage/temp/ exists with 777 permissions
☐ storage/uploads/ exists with 777 permissions
☐ storage/logs/ has 777 permissions
☐ AuthController.php uploaded with password fix
☐ .htaccess in public/ exists with 644 permissions
☐ .user.ini created with session.save_path set
☐ test-session.php shows "Sessions are working"
☐ Browser cache cleared
☐ POST request visible in Network tab when logging in
```

---

## 🔧 IF STILL NOT WORKING:

### Check PHP Session Files:
After login attempt, check if session files are being created:
```
/home2/jabowbo/staging.j-abo-wbo.org/storage/sessions/
```

You should see files named like: `sess_abc123...`

If no files, PHP can't write to sessions directory.

### View Page Source:
Right-click login page → "View Page Source"
Search for: `_csrf_token` or `_token`

**If you see:** `<input type="hidden" name="_token" value="">`  
→ CSRF token is empty = sessions not working

**If you see:** `<input type="hidden" name="_token" value="abc123...">` 
→ CSRF token is set = sessions working

---

## 📞 EMERGENCY FALLBACK:

If nothing works, we can temporarily disable CSRF protection:

In `app/Core/Controller.php`, find `requireCsrf()` method and comment it out temporarily for testing.

**But fix sessions properly - CSRF protection is critical for security!**

---

## ✅ SUCCESS INDICATORS:

1. test-session.php shows sessions working
2. Login form has CSRF token in page source
3. Network tab shows POST request to /auth/login
4. Session files created in storage/sessions/
5. Login redirects to dashboard

---

**Start with Step 1 (create storage directories) - this is THE critical missing piece!**
