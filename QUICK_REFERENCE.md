# Job Portal - Quick Reference Guide

## Workspace Cleanup ✅

### Deleted Files
```
/scratch/          (debug folder - DELETED)
debug_db.php       (DELETED)
query              (DELETED)
```

### Active Folders
```
/admin/           - Admin features and management
/applicant/       - Applicant features and reviews
/registration/    - User registration pages
/onboarding/      - Skills selection after registration
```

---

## Entry Points

### User Access
```
Landing Page:     http://localhost/Jobportal/index.html
Registration:     http://localhost/Jobportal/registration/register.html
Database Admin:   http://localhost/phpmyadmin
```

### Test Accounts (Create These)
```
Admin:
  Email: admin@jobportal.com
  Password: admin123
  
Applicant:
  Email: applicant@jobportal.com
  Password: user123
```

---

## File Structure & Paths

### Root Level Files
- `Database.php` - Central DB connection (✅ Verified)
- `index.html` - Landing/Login page
- `login_process.php` - Authentication
- `logout.php` - Session destroy
- `script.js` - Global JS
- `style.css` - Global CSS

### Admin Files (`/admin/`)
- `index.php` - Dashboard
- `create_job.php` - Job posting form
- `jobs.php` - Manage jobs
- `view_applicants.php` - View applications
- `profile.php` - Admin profile

### Applicant Files (`/applicant/`)
- `applicant_dashboard.php` - Main dashboard
- `browse_jobs.php` - Browse all jobs
- `job_details.php` - Job details + REVIEWS
- `job_listing.php` - Alternative listing
- `apply_job.php` - Submit application
- `applied_jobs.php` - View applications
- `saved_jobs.php` - View saved jobs
- `save_job.php` - Save/unsave job
- `skill_gap.php` - Skill analysis
- `submit_review.php` - Review submission handler
- `review.js` - Review form handler

### Registration Files (`/registration/`)
- `register.html` - Registration form
- `register_process.php` - Registration handler
- `register_script.js` - Form validation

### Onboarding Files (`/onboarding/`)
- `skills.php` - Skills selection page
- `save_skills.php` - Save user skills

---

## Session Variables (Standardized ✅)

### Primary Session Variables
```php
$_SESSION['user_id']      // User ID (PRIMARY)
$_SESSION['role']         // 'Admin' or 'Applicant'
$_SESSION['current_user_id']  // (fallback - no longer set, kept for compatibility)
```

### Usage Pattern
```php
$userId = $_SESSION['user_id'] ?? $_SESSION['current_user_id'] ?? null;
```

---

## Key Features Status

### Core Features ✅
- [x] Registration (both roles)
- [x] Login with role-based redirect
- [x] Session management
- [x] Job posting (Admin)
- [x] Job browsing (Applicant)
- [x] Apply functionality
- [x] Save jobs
- [x] Skill matching
- [x] Skill gap analysis

### Company Reviews Feature ✅
- [x] Submit reviews with rating (1-5)
- [x] Anonymous review option
- [x] Update own review
- [x] View all reviews
- [x] Average rating display
- [x] Reviewer attribution (unless anonymous)
- [x] Database integration (uses `leave_review` table)

---

## Database Tables

### Main Tables
```
User            - All users (Admin + Applicant)
Company         - Company profiles
Admin           - Admin-specific data
Applicant       - Applicant profiles
Job             - Job postings
Requires_Skill  - Job skill requirements (Is_Mandatory flag)
Has_Skill       - User skills
Application     - Job applications
Wishlist        - Saved jobs
leave_review    - Company reviews (COMPOSITE KEY: UserID, Company_ID)
```

### leave_review Table Structure
```
UserID           INT          (FK to User) [PRIMARY KEY]
Company_ID       INT          (FK to Company) [PRIMARY KEY]
Rating           INT          (1-5)
Feedback         TEXT
Is_Anonymous     BOOLEAN
Date_Submitted   DATETIME     (DEFAULT CURRENT_TIMESTAMP)
```

---

## AJAX Endpoints

### Applicant Features
```
/Jobportal/applicant/apply_job.php          - POST job application
/Jobportal/applicant/save_job.php           - POST save/unsave job
/Jobportal/applicant/submit_review.php      - POST company review
```

### Admin Features
```
/Jobportal/admin/save_job.php               - POST create job
/Jobportal/admin/update_application_status.php - POST status update
```

---

## Code Validation Results ✅

### PHP Syntax Check
```
✅ applicant/job_details.php
✅ applicant/applicant_dashboard.php
✅ applicant/submit_review.php
✅ registration/register_process.php
✅ All other PHP files verified
```

### Path Verification
```
✅ All include '../Database.php' paths correct
✅ All AJAX endpoints use /Jobportal/ prefix
✅ All HTML form actions point to correct files
✅ All redirects use correct paths
```

---

## Testing Checklist

### Before Testing
- [ ] Start XAMPP (Apache + MySQL)
- [ ] Verify database exists: `jobportal_db`
- [ ] Check leave_review has Date_Submitted column
- [ ] Open http://localhost/Jobportal/

### Create Test Data
- [ ] Register Admin account
- [ ] Register Applicant account
- [ ] Complete skills onboarding

### Admin Testing
- [ ] Login as Admin
- [ ] Create job posting
- [ ] View jobs
- [ ] View applicants
- [ ] Logout

### Applicant Testing
- [ ] Login as Applicant
- [ ] View recommended jobs
- [ ] Browse all jobs
- [ ] View job details
- [ ] Apply for job
- [ ] Save job
- [ ] View saved jobs
- [ ] View applied jobs
- [ ] Check skill gap
- [ ] Submit company review
- [ ] Update review
- [ ] Post anonymously
- [ ] Logout

### Feature Verification
- [ ] Session persists across pages
- [ ] Role-based access works
- [ ] Skills affect job recommendations
- [ ] Reviews display correctly
- [ ] Anonymous reviews work
- [ ] Database updates correctly

---

## Troubleshooting

### MySQL Not Running
```
1. Open XAMPP Control Panel
2. Click "Start" next to MySQL
3. Wait for it to show "Running"
4. Refresh phpmyadmin
```

### Page Not Found (404)
```
Check paths:
✅ Root access: http://localhost/Jobportal/
✅ Registration: /Jobportal/registration/register.html
✅ Admin: /Jobportal/admin/index.php
✅ Applicant: /Jobportal/applicant/applicant_dashboard.php
```

### Session Lost
```
Verify:
✅ session_start() at top of each file
✅ No headers sent before session_start()
✅ $_SESSION['user_id'] is set after login
✅ Cookies enabled in browser
```

### Review Not Saving
```
Check:
✅ leave_review table exists
✅ Date_Submitted column exists in leave_review
✅ UserID and Company_ID are valid
✅ Browser console for JavaScript errors
```

---

## File Cleanup Summary

### Removed
- `scratch/check_table.php`
- `scratch/list_tables.php`
- `debug_db.php`
- `query` file

### Retained
All active application files are retained and verified

### Created (New Documentation)
- `SYSTEM_CHECK.md` - End-to-end testing guide
- `STATUS_REPORT.md` - Current system status
- `QUICK_REFERENCE.md` - This file

---

## System Ready Status: ✅ 

**All cleanup complete**
**All paths verified**
**All code validated**
**Ready for testing**

---

## Next Action

👉 **Start with:** `SYSTEM_CHECK.md` for comprehensive end-to-end testing
