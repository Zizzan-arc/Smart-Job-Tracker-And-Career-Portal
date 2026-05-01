# Job Portal - End-to-End System Check Guide

## Prerequisites
- XAMPP is running (Apache + MySQL)
- Database `jobportal_db` is created with all tables
- All necessary tables exist: User, Company, Admin, Applicant, Job, Requires_Skill, Has_Skill, Application, Wishlist, leave_review

---

## Phase 1: Setup & Database Verification

### Step 1: Verify Database Tables
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select database `jobportal_db`
3. Verify these tables exist:
   - ✅ User
   - ✅ Company
   - ✅ Admin
   - ✅ Applicant
   - ✅ Job
   - ✅ Requires_Skill
   - ✅ Has_Skill
   - ✅ Application
   - ✅ Wishlist
   - ✅ leave_review

### Step 2: Add leave_review Date Column (if missing)
Run this SQL in phpMyAdmin if `Date_Submitted` column doesn't exist:
```sql
ALTER TABLE leave_review ADD COLUMN Date_Submitted DATETIME DEFAULT CURRENT_TIMESTAMP;
```

---

## Phase 2: Create Test Accounts

### Create Admin Account
1. Go to: `http://localhost/Jobportal/registration/register.html`
2. Fill in:
   - First Name: `Admin`
   - Last Name: `User`
   - Email: `admin@jobportal.com`
   - Password: `admin123`
   - User Role: `Admin`
   - GitHub URL: `https://github.com/admin`
   - Experience: `5`
3. Click Register
4. Should redirect back to login page with success message

### Create Applicant Account
1. Go to: `http://localhost/Jobportal/registration/register.html`
2. Fill in:
   - First Name: `Applicant`
   - Last Name: `TestUser`
   - Email: `applicant@jobportal.com`
   - Password: `user123`
   - User Role: `Applicant`
   - GitHub URL: `https://github.com/applicant`
   - Experience: `2`
3. Click Register
4. Should redirect to onboarding skills page
5. Select at least 3 skills and click Save
6. Redirect to dashboard

---

## Phase 3: Admin Features Testing

### Test 1: Admin Login
1. Go to: `http://localhost/Jobportal/index.html`
2. Login with:
   - Email: `admin@jobportal.com`
   - Password: `admin123`
3. ✅ Should see Admin Dashboard with navigation menu

### Test 2: Create Company (if needed)
1. If no companies exist, create one in phpMyAdmin manually:
   ```sql
   INSERT INTO Company (Company_Name, Industry, Website, Location) 
   VALUES ('TechCorp', 'Technology', 'https://techcorp.com', 'San Francisco');
   ```

### Test 3: Create Job Posting
1. On Admin Dashboard, click "Create Job"
2. Fill in:
   - Job Title: `Senior Developer`
   - Base Salary: `100000`
   - Deadline: (future date)
   - Work Model: `Remote`
   - Employment Type: `Full-time`
   - Company: Select from dropdown
3. Select at least 2 Required Skills
4. Optionally select Nice-to-Have Skills
5. Click "Post Job"
6. ✅ Should see success message
7. Go to "View Jobs" - new job should appear

### Test 4: View Applicants
1. On Admin Dashboard, click "View Applicants"
2. Select a job (if available after Test 3)
3. ✅ Should show list of applicants (empty initially)

---

## Phase 4: Applicant Features Testing

### Test 5: Applicant Login
1. Go to: `http://localhost/Jobportal/index.html`
2. Login with:
   - Email: `applicant@jobportal.com`
   - Password: `user123`
3. ✅ Should see Applicant Dashboard

### Test 6: View Recommended Jobs
1. On Applicant Dashboard, view recommended jobs section
2. ✅ Should show jobs that match user skills
3. Jobs should show:
   - Job title
   - Company name
   - Salary range
   - Match percentage
   - Apply/Save buttons

### Test 7: Browse All Jobs
1. Click "Browse Jobs" in navigation
2. ✅ Should display all available jobs
3. Can filter by various criteria

### Test 8: Apply for Job
1. Click on a job with skill match > 0%
2. Click "Apply Now" button
3. ✅ Should show success message
4. Button should change to "Already Applied"

### Test 9: Save Job
1. On a different job, click "Save Job" button
2. ✅ Should show success message
3. Button should change to "Remove from Saved"

### Test 10: View Saved Jobs
1. Click "Saved Jobs" in navigation
2. ✅ Should show list of saved jobs
3. Can apply from here

### Test 11: View Applied Jobs
1. Click "Applied Jobs" in navigation
2. ✅ Should show list of applied jobs
3. Shows application status

### Test 12: Skill Gap Analysis
1. On a saved job, click "Skill Gap Analysis" button
2. ✅ Should show:
   - Missing skills
   - Matched skills
   - Recommendations

### Test 13: Job Details Page
1. Click on any job title to view details
2. ✅ Page should show:
   - Full job description
   - Required skills (marked as Required/Preferred)
   - Company information
   - Apply/Save buttons

---

## Phase 5: Company Reviews Feature Testing

### Test 14: Submit Company Review
1. On Job Details page, scroll to "Company Reviews" section
2. See existing reviews (if any)
3. Scroll to "Write a Review" form
4. Fill in:
   - Rating: Select 5 stars
   - Review: Type feedback (min 10 characters)
   - Optional: Check "Post anonymously"
5. Click "Submit Review"
6. ✅ Should show success message and reload
7. New review should appear in the reviews list

### Test 15: Update Review
1. Submit another review for the same company
2. ✅ Should update existing review (not create duplicate)
3. New date should be shown

### Test 16: Anonymous Reviews
1. Submit a review with "Anonymous" checked
2. ✅ Should show "Anonymous" instead of reviewer name

### Test 17: View Reviews
1. Check that reviews show:
   - Star rating
   - Reviewer name (or "Anonymous")
   - Review text
   - Submit date

---

## Phase 6: Session & Navigation Testing

### Test 18: Logout
1. Click Logout button
2. ✅ Should return to login page
3. Session should be destroyed

### Test 19: Session Persistence
1. Login as applicant
2. Navigate to different pages (Browse Jobs, Saved Jobs, etc.)
3. ✅ Should stay logged in
4. Header should show user info/logout option

### Test 20: Role-Based Access
1. Try accessing `/Jobportal/admin/index.php` as applicant
2. ✅ Should be redirected (or show access error)
3. Try accessing `/Jobportal/applicant/applicant_dashboard.php` as admin
4. ✅ Should be redirected (or show access error)

---

## Phase 7: Data Validation Testing

### Test 21: Invalid Inputs
1. Try registration with blank fields
2. ✅ Should prevent submission with validation errors
3. Try login with wrong email/password
4. ✅ Should show "Login Failed" message
5. Try applying without matching skills
6. ✅ Should show warning and prevent apply

### Test 22: Duplicate Prevention
1. Try registering with same email twice
2. ✅ Should show "Email already registered" error

---

## Phase 8: File & Path Verification

### Verification Checklist
- ✅ All include/require paths use correct relative paths
- ✅ AJAX endpoints point to correct PHP files
- ✅ Session variables are consistent (using `user_id` primarily)
- ✅ Database connection works from all files
- ✅ No unnecessary debug files in workspace

### Cleaned Up Files
- ✅ Deleted `/scratch/` folder
- ✅ Deleted `debug_db.php`
- ✅ Deleted `query` file

---

## Phase 9: Bug Verification

### Common Issues to Check
- [ ] Session not persisting across pages
- [ ] Skills not showing in job posting
- [ ] Apply button disabled incorrectly
- [ ] Reviews not saving
- [ ] Database connection errors

---

## Success Criteria

✅ All tests pass when you can:
1. Register new users (both roles)
2. Login with correct credentials
3. Post jobs (Admin)
4. View jobs (Applicant)
5. Apply/Save jobs (Applicant)
6. View skill gaps (Applicant)
7. Submit company reviews (Applicant)
8. Update reviews (Applicant)
9. Logout properly
10. Session persists correctly

---

## Troubleshooting

### If MySQL Connection Fails
1. Start XAMPP Control Panel
2. Click "Start" next to MySQL
3. Verify `http://localhost/phpmyadmin` loads

### If Registration Page Doesn't Load
1. Check path: `http://localhost/Jobportal/registration/register.html`
2. Verify `registration/` folder exists

### If Features Don't Work
1. Check browser console for JavaScript errors (F12)
2. Check PHP error log in XAMPP
3. Verify database tables have correct structure

---

## Testing Completed ✅

Document when you finish each phase:
- Phase 1 (Setup): ____/____ at ____:____
- Phase 2 (Accounts): ____/____ at ____:____
- Phase 3 (Admin): ____/____ at ____:____
- Phase 4 (Applicant): ____/____ at ____:____
- Phase 5 (Reviews): ____/____ at ____:____
- Phase 6 (Session): ____/____ at ____:____
- Phase 7 (Validation): ____/____ at ____:____
- Phase 8 (Files): ____/____ at ____:____
- Phase 9 (Bugs): ____/____ at ____:____
