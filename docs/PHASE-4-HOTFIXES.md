# Phase 4 Hotfixes - v4.0.0

## 🚨 Critical Hotfixes Applied

After Phase 4 (alias removal), production site broke with Fatal Errors. This document tracks all hotfixes applied.

---

## Timeline

| Time | Issue | Commit | Status |
|------|-------|--------|--------|
| 2026-01-26 | Initial Phase 4 deployment | 9c0509b | ❌ BROKEN |
| 2026-01-26 | Fatal: Free_Form_Certificate_Loader not found | abc7de8 | ⚠️ PARTIAL |
| 2026-01-26 | Fatal: FFC_Submission_Handler not found | 5fcb77e | ✅ FIXED |

---

## Hotfix 1: Loader Instantiation (abc7de8)

### Error
```
Fatal error: Class 'Free_Form_Certificate_Loader' not found
in wp-ffcertificate.php:76
```

### Root Cause
- In Phase 2, class was renamed:
  - `Free_Form_Certificate_Loader` → `FreeFormCertificate\Loader`
- In Phase 4, aliases were removed
- Main plugin file still used old name

### Fix
**File:** `wp-ffcertificate.php:74`

```diff
- $plugin = new \Free_Form_Certificate_Loader();
+ $plugin = new \FreeFormCertificate\Loader();
```

### Commit
```
abc7de8 - fix: Corrigir instanciação do Loader para usar namespace correto
```

---

## Hotfix 2: Loader Dependencies (5fcb77e)

### Error
```
Fatal error: Class 'FFC_Submission_Handler' not found
in includes/class-ffc-loader.php:37
```

### Root Cause
- `class-ffc-loader.php` still used BC aliases:
  - `\FFC_Submission_Handler`
  - `\FFC_Email_Handler`
  - `\FFC_CSV_Exporter`
  - `\FFC_CPT`
  - `\FFC_Admin`
  - `\FFC_Frontend`
  - `\FFC_Admin_Ajax`
  - `\FFC_REST_Controller`
- These aliases were removed in Phase 4

### Fix
**File:** `includes/class-ffc-loader.php`

#### 1. Added use statements
```php
use FreeFormCertificate\Submissions\SubmissionHandler;
use FreeFormCertificate\Integrations\EmailHandler;
use FreeFormCertificate\Admin\CSVExporter;
use FreeFormCertificate\Admin\CPT;
use FreeFormCertificate\Admin\Admin;
use FreeFormCertificate\Frontend\Frontend;
use FreeFormCertificate\Admin\AdminAjax;
use FreeFormCertificate\API\RestController;
```

#### 2. Updated instantiations
```diff
- $this->submission_handler = new \FFC_Submission_Handler();
+ $this->submission_handler = new SubmissionHandler();

- $this->email_handler = new \FFC_Email_Handler();
+ $this->email_handler = new EmailHandler();

- $this->csv_exporter = new \FFC_CSV_Exporter();
+ $this->csv_exporter = new CSVExporter();

- $this->cpt = new \FFC_CPT();
+ $this->cpt = new CPT();

- $this->admin = new \FFC_Admin(...);
+ $this->admin = new Admin(...);

- $this->frontend = new \FFC_Frontend(...);
+ $this->frontend = new Frontend(...);

- $this->admin_ajax = new \FFC_Admin_Ajax();
+ $this->admin_ajax = new AdminAjax();
```

#### 3. Updated REST API check
```diff
- if (class_exists('\FFC_REST_Controller')) {
-     new \FFC_REST_Controller();
+ if (class_exists(RestController::class)) {
+     new RestController();
```

#### 4. Fixed activation hooks
```diff
- register_activation_hook(..., ['FFC_Activator', 'activate']);
- register_deactivation_hook(..., ['FFC_Deactivator', 'deactivate']);
+ register_activation_hook(..., ['\\FFC_Activator', 'activate']);
+ register_deactivation_hook(..., ['\\FFC_Deactivator', 'deactivate']);
```

### Commit
```
5fcb77e - fix: Atualizar Loader para usar namespaces completos (v4.0.0 - HOTFIX 2)
```

---

## Production Deployment Instructions

### ⚠️ CRITICAL: Deploy Both Hotfixes

You **MUST** deploy **both files** to fix production:

1. **wp-ffcertificate.php** (Hotfix 1)
2. **includes/class-ffc-loader.php** (Hotfix 2)

### Option A: Git Pull (Recommended)

```bash
# 1. SSH to production server
ssh user@yourserver.com

# 2. Navigate to plugin directory
cd /home/u690874273/domains/.../wp-content/plugins/wp-ffcertificate

# 3. Pull latest hotfixes
git pull origin claude/fix-migration-cleanup-xlJ4P

# 4. Clear PHP OPcache (CRITICAL!)
# Option 1: Restart PHP-FPM
sudo systemctl restart php-fpm

# Option 2: Via WordPress
wp cache flush

# Option 3: Via temporary script (if no root access)
echo "<?php opcache_reset(); echo 'Cache cleared!';" > clear-cache.php
# Visit: https://yoursite.com/wp-content/plugins/wp-ffcertificate/clear-cache.php
# Then DELETE the file immediately!
rm clear-cache.php
```

### Option B: Manual FTP Upload

1. **Download** corrected files from local repo:
   - `/home/user/wp-ffcertificate/wp-ffcertificate.php`
   - `/home/user/wp-ffcertificate/includes/class-ffc-loader.php`

2. **Upload** via FTP/cPanel to:
   - `/home/u690874273/.../wp-content/plugins/wp-ffcertificate/wp-ffcertificate.php`
   - `/home/u690874273/.../wp-content/plugins/wp-ffcertificate/includes/class-ffc-loader.php`

3. **CRITICAL:** Clear PHP OPcache:
   - Via cPanel: PHP Selector → OPcache → Reset
   - Via plugin: Disable and re-enable plugin
   - Via file manager: Delete temp files if available

### Option C: Create Emergency Rollback

If hotfixes don't work, create temporary aliases:

**File:** `wp-content/mu-plugins/ffc-emergency-aliases.php`

```php
<?php
/**
 * Emergency BC aliases for Free Form Certificate v4.0.0
 * DELETE THIS FILE after proper hotfix is deployed!
 */

// Loader alias
class_alias('FreeFormCertificate\\Loader', 'Free_Form_Certificate_Loader');

// Core classes
class_alias('FreeFormCertificate\\Submissions\\SubmissionHandler', 'FFC_Submission_Handler');
class_alias('FreeFormCertificate\\Integrations\\EmailHandler', 'FFC_Email_Handler');
class_alias('FreeFormCertificate\\Admin\\CSVExporter', 'FFC_CSV_Exporter');
class_alias('FreeFormCertificate\\Admin\\CPT', 'FFC_CPT');
class_alias('FreeFormCertificate\\Admin\\Admin', 'FFC_Admin');
class_alias('FreeFormCertificate\\Frontend\\Frontend', 'FFC_Frontend');
class_alias('FreeFormCertificate\\Admin\\AdminAjax', 'FFC_Admin_Ajax');
class_alias('FreeFormCertificate\\API\\RestController', 'FFC_REST_Controller');

// Add more as needed...
```

---

## Verification Steps

After deployment, verify these work:

### 1. Site Loads
- [ ] Visit homepage (no errors)
- [ ] Visit wp-admin (no errors)
- [ ] Check PHP error log (no new errors)

### 2. Admin Panel
- [ ] Dashboard loads
- [ ] FFC menu accessible
- [ ] Forms list displays
- [ ] Settings page opens

### 3. Frontend
- [ ] Forms render on pages
- [ ] Shortcodes work
- [ ] Submit test form (if safe)

### 4. Check PHP Version
```bash
php -v
# Ensure PHP 7.4+ for namespace support
```

---

## Why This Happened

### Phase 2 vs Phase 4 Mismatch

**Phase 2 (Completed):** Classes migrated to namespaces
- ✅ All 60 classes got namespace declarations
- ✅ Internal references updated
- ✅ **BUT:** Some files still used BC aliases (`\FFC_*`)

**Phase 4 (Initial):** Removed BC aliases
- ❌ Removed `includes/class-ffc-aliases.php`
- ❌ Removed `ffc_register_class_aliases()` call
- ❌ **BUT:** Didn't catch ALL files still using aliases

**Root Cause:**
- Automated correction script (ca99ba5) missed some files
- Manual review didn't catch `class-ffc-loader.php` internal usage
- Should have tested plugin activation before Phase 4 commit

---

## Lessons Learned

### For Future Breaking Changes

1. **Test activation before deploy:**
   ```bash
   # Should have run this BEFORE Phase 4 commit:
   wp plugin activate wp-ffcertificate --path=/path/to/wordpress
   ```

2. **Check ALL instantiations:**
   ```bash
   # Should have searched for ALL patterns:
   grep -r "new FFC_" includes/
   grep -r "new \\\\FFC_" includes/
   grep -r "class_exists('FFC_" includes/
   ```

3. **Use staging environment first:**
   - Test Phase 4 on staging BEFORE production
   - Verify all critical paths work
   - Check error logs

4. **Have rollback plan ready:**
   - Keep emergency alias file prepared
   - Know how to restore previous version
   - Document OPcache clearing steps

---

## Current Status

✅ **All hotfixes applied and pushed**

**Commits:**
- `abc7de8` - Hotfix 1: Loader instantiation
- `5fcb77e` - Hotfix 2: Loader dependencies

**Branch:** `claude/fix-migration-cleanup-xlJ4P`

**Awaiting:** Production deployment + verification

---

## Next Steps After Verification

Once production is confirmed working:

1. **Create Pull Request** for main branch
2. **Update CHANGELOG.md** with v4.0.0 details
3. **Create git tag** `v4.0.0`
4. **Test all critical features** comprehensively
5. **Update documentation** with any additional findings

---

**Document Version:** 1.0
**Last Updated:** 2026-01-26
**Status:** Hotfixes applied, awaiting production verification
