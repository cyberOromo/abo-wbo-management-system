-- =============================================================================
-- CRITICAL DATABASE MIGRATION FOR ABO-WBO FIXES
-- Date: November 29, 2025
-- Commit: 74c4a16
-- =============================================================================

-- This migration MUST be run before deploying the fixed code to staging/production
-- Without this column, the application will fail with database errors

-- =============================================================================
-- STEP 1: ADD MISSING COLUMN TO USERS TABLE
-- =============================================================================

-- Add gurmu_id column (users belong to a Gurmu in the hierarchy)
ALTER TABLE users 
ADD COLUMN gurmu_id INT NULL 
COMMENT 'Foreign key to gurmus table - users organizational assignment'
AFTER role;

-- =============================================================================
-- STEP 2: VERIFY THE COLUMN WAS ADDED
-- =============================================================================

-- Run this to confirm:
DESCRIBE users;
-- Expected output should show gurmu_id column

-- =============================================================================
-- STEP 3: (OPTIONAL) ADD INDEX FOR PERFORMANCE
-- =============================================================================

-- Recommended for better query performance:
ALTER TABLE users ADD INDEX idx_gurmu_id (gurmu_id);

-- =============================================================================
-- ROLLBACK (IF NEEDED)
-- =============================================================================

-- If you need to rollback this migration:
-- ALTER TABLE users DROP COLUMN gurmu_id;

-- =============================================================================
-- NOTES
-- =============================================================================

-- 1. This column was defined in schema.sql but was never added during initial migration
-- 2. The application code expects this column for hierarchy queries
-- 3. Column can be NULL - will be populated as users are assigned to Gurmus
-- 4. DO NOT add foreign key constraint yet (data needs to be populated first)

-- =============================================================================
-- VERIFICATION QUERIES
-- =============================================================================

-- Check if any users already have gurmu_id (should be NULL initially):
SELECT COUNT(*) as total_users, 
       COUNT(gurmu_id) as users_with_gurmu 
FROM users;

-- Verify hierarchy structure is intact:
SELECT 
    (SELECT COUNT(*) FROM godinas WHERE status != 'deleted') as godina_count,
    (SELECT COUNT(*) FROM gamtas WHERE status != 'deleted') as gamta_count,
    (SELECT COUNT(*) FROM gurmus WHERE status != 'deleted') as gurmu_count,
    (SELECT COUNT(*) FROM users WHERE status = 'active') as user_count;

-- =============================================================================
-- SUCCESS CONFIRMATION
-- =============================================================================

-- After running migration, you should see:
-- ✅ users table has gurmu_id column
-- ✅ No errors when running DESCRIBE users
-- ✅ Application can query users with JOIN on gurmu_id

-- =============================================================================
-- DEPLOYMENT SEQUENCE
-- =============================================================================

-- STAGING:
-- 1. Backup staging database
-- 2. Run this SQL script
-- 3. Verify column exists
-- 4. Deploy code
-- 5. Test all hierarchy pages

-- PRODUCTION:
-- 1. Backup production database (CRITICAL!)
-- 2. Test on staging first (DO NOT SKIP!)
-- 3. Run this SQL script on production
-- 4. Verify column exists
-- 5. Deploy code
-- 6. Monitor error logs
-- 7. Test critical paths immediately

-- =============================================================================
