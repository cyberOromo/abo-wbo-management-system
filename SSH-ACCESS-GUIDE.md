# 🔐 SSH Access Setup Guide for HostGator cPanel

## 📋 How to Check if SSH Access is Enabled

### Method 1: Check in cPanel
1. **Login to cPanel** (https://marbella.websitewelcome.com:2083)
2. Look for **"SSH Access"** or **"Terminal"** icon
   - Usually found under "Advanced" or "Security" section
3. If you see it → SSH is enabled ✅
4. If you don't see it → SSH might be disabled ❌

### Method 2: Try to Connect
1. Open **Command Prompt** (Windows) or **Terminal** (Mac/Linux)
2. Try connecting:
   ```bash
   ssh jabowbo@staging.j-abo-wbo.org
   # or
   ssh jabowbo@marbella.websitewelcome.com
   ```
3. If you get password prompt → SSH enabled ✅
4. If you get "Connection refused" → SSH disabled ❌

---

## 🔓 How to Enable SSH Access on HostGator

### Step 1: Contact HostGator Support (Required for Shared Hosting)
HostGator **disables SSH by default** on shared hosting for security. You must request activation:

1. **Login to HostGator Client Portal**
   - URL: https://portal.hostgator.com
   - Use your HostGator account credentials

2. **Open Support Ticket**
   - Click "Support" → "Support Portal"
   - Click "Open a Ticket"
   - Subject: "Enable SSH Access for my account"
   - Message:
     ```
     Hello,
     
     Please enable SSH/Shell access for my cPanel account:
     - cPanel Username: jabowbo
     - Domain: staging.j-abo-wbo.org
     
     I need SSH access to install Composer dependencies for my web application.
     
     Thank you!
     ```

3. **Wait for Activation** (usually 1-4 hours)

### Step 2: Generate SSH Key (Optional but Recommended)
Once SSH is enabled, you can use SSH keys instead of passwords:

1. **On Windows (using Git Bash or PowerShell):**
   ```bash
   ssh-keygen -t rsa -b 4096 -C "your_email@example.com"
   ```
   - Press Enter to save to default location
   - Enter a passphrase (optional)

2. **Copy Public Key:**
   ```bash
   cat ~/.ssh/id_rsa.pub
   ```
   - Copy the entire output

3. **Add to cPanel:**
   - cPanel → SSH Access → Manage SSH Keys
   - Click "Import Key"
   - Paste your public key
   - Click "Import"
   - Then click "Manage" → "Authorize"

---

## 🔌 Connecting via SSH

### Method 1: Using Command Prompt/Terminal
```bash
# Basic connection
ssh jabowbo@marbella.websitewelcome.com

# Or using domain
ssh jabowbo@staging.j-abo-wbo.org

# Specify port if needed (usually 22)
ssh -p 22 jabowbo@marbella.websitewelcome.com
```

### Method 2: Using PuTTY (Windows)
1. **Download PuTTY** (if not installed)
   - URL: https://www.putty.org/

2. **Configure Connection:**
   - Host Name: `marbella.websitewelcome.com`
   - Port: `22`
   - Connection Type: `SSH`
   - Click "Open"

3. **Login:**
   - Username: `jabowbo`
   - Password: Your cPanel password

---

## 🚀 Using SSH to Install Composer Dependencies

Once connected via SSH:

### Step 1: Navigate to Your Application
```bash
cd /home2/jabowbo/staging.j-abo-wbo.org
pwd  # Verify you're in the right directory
ls   # List files (should see app/, public/, config/, etc.)
```

### Step 2: Check if Composer is Installed
```bash
composer --version
```

**If Composer is installed:**
```bash
composer install --no-dev --optimize-autoloader
```

**If Composer is NOT installed:**
```bash
# Download Composer installer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

# Install Composer locally
php composer-setup.php

# Remove installer
php -r "unlink('composer-setup.php');"

# Run composer using php
php composer.phar install --no-dev --optimize-autoloader
```

### Step 3: Verify Installation
```bash
ls -la vendor/  # Should see autoload.php
```

### Step 4: Set Correct Permissions
```bash
chmod -R 755 vendor/
chmod 644 vendor/autoload.php
```

### Step 5: Test the Site
Open browser: `https://staging.j-abo-wbo.org/`

---

## ❌ If SSH is NOT Available (Alternative Methods)

### Alternative 1: Upload vendor/ Folder via cPanel

1. **On your local machine** (Windows Command Prompt):
   ```bash
   cd c:\xampp\htdocs\abo-wbo
   composer install --no-dev --optimize-autoloader
   ```

2. **Create ZIP file:**
   - Right-click on `vendor` folder
   - Send to → Compressed (zipped) folder
   - Name it `vendor.zip`

3. **Upload to cPanel:**
   - cPanel → File Manager
   - Navigate to `/home2/jabowbo/staging.j-abo-wbo.org/`
   - Click "Upload" button
   - Select `vendor.zip`
   - Wait for upload to complete

4. **Extract ZIP:**
   - Right-click on `vendor.zip`
   - Click "Extract"
   - Extract to current directory
   - Delete `vendor.zip` after extraction

5. **Set Permissions:**
   - Select `vendor` folder
   - Right-click → Change Permissions
   - Set to 755
   - Check "Recurse into subdirectories"
   - Click "Change Permissions"

### Alternative 2: Use FTP Client (FileZilla)

1. **Install FileZilla:**
   - Download: https://filezilla-project.org/

2. **Connect to HostGator:**
   - Host: `ftp.j-abo-wbo.org` or `marbella.websitewelcome.com`
   - Username: `jabowbo`
   - Password: Your cPanel password
   - Port: `21`
   - Click "Quickconnect"

3. **Upload vendor/ folder:**
   - Left panel: Navigate to `c:\xampp\htdocs\abo-wbo\vendor`
   - Right panel: Navigate to `/home2/jabowbo/staging.j-abo-wbo.org/`
   - Drag `vendor` folder from left to right
   - Wait for upload (may take 5-10 minutes)

---

## 🔍 Common SSH Issues & Solutions

### Issue 1: "Permission denied (publickey)"
**Solution:** SSH keys not set up properly. Use password authentication:
```bash
ssh -o PreferredAuthentications=password jabowbo@marbella.websitewelcome.com
```

### Issue 2: "Connection refused"
**Solution:** SSH is disabled. Contact HostGator support to enable it.

### Issue 3: "Host key verification failed"
**Solution:** Clear known_hosts file:
```bash
ssh-keygen -R marbella.websitewelcome.com
```

### Issue 4: "composer: command not found"
**Solution:** Install Composer locally (see Step 2 above) or use Alternative 1/2.

---

## ✅ Quick Decision Tree

```
Do you see "SSH Access" icon in cPanel?
├── YES → Try connecting via SSH
│   ├── Connection successful → Run composer install
│   └── Connection failed → Contact HostGator support
│
└── NO → Choose one:
    ├── Contact HostGator to enable SSH (fastest if approved)
    └── Use Alternative 1 or 2 (upload vendor/ folder manually)
```

---

## 🎯 Recommended Approach for You

Since we're not sure if SSH is enabled:

1. **First, try Alternative 1 (Upload vendor/ via cPanel)**
   - This is guaranteed to work
   - Takes 5-10 minutes
   - No need to wait for SSH activation

2. **Meanwhile, request SSH access for future deployments**
   - Open support ticket with HostGator
   - SSH makes future updates much easier

---

## 📞 HostGator Support Contact Info

- **Live Chat:** Available in Client Portal
- **Phone:** 1-866-96-GATOR (1-866-964-2867)
- **Support Ticket:** https://portal.hostgator.com/support
- **Response Time:** Usually 1-4 hours for SSH requests

---

**Ready to proceed? I recommend starting with Alternative 1 (upload vendor/ folder) since it's the fastest method that doesn't require SSH access.**
