# Student Attendance Management System Requirements

## 1. Project Overview
A web-based system for managing student attendance in colleges or institutes featuring:
- Student registration
- Lecture management
- QR Code attendance tracking
- Automated absence and warning management
- Exporting attendance reports
- Role-based Access Control (Super Admin / Admin / Teacher)

### Technical Stack:
- Backend: Laravel (Global Standard)
- Frontend: Vue.js 3 + Tailwind CSS + Vite
- Database: MySQL (Standardized Schema)
- QR Scanner: High-performance mobile/laptop camera library.
- Real-time Sync: Laravel Reverb (WebSockets) for live attendance updates.
- Background Processing: Laravel Queue & Horizon (for heavy tasks: Reports, QR generation, Printing).
- Scalability: SaaS-ready architecture with Redis caching and CI/CD (GitHub Actions).



---

## 2. User Roles and Permissions

### 1. Admin
Multi-admin support with granular permissions:
- Manage academic stages and groups (Group A, B, C)
- Assign subjects to teachers
- Student management (CRUD operations)
- Comprehensive analytics and system configuration
- Sub-admin account management (Super Admin only)

### 2. Teacher
- Secure login and personalized profile page.
- Comprehensive Profile: Full Name, Professional Photo, Unique Teacher ID, and Department Info.
- View assigned subjects and schedules
- Initiate attendance sessions and scan student QRs
- Manual attendance overrides
- Export attendance reports to Excel
- Monitor student absence logs and warning statuses

---

## 3. Academic Structure and Student Management

### Academic Hierarchy:
- Stages: (First, Second, Third, Fourth)
- Groups: (Group A, Group B, Group C)
- Study Type: (Morning / Evening)

### Student Profiles:
- Full Name (Four-part name)
- Unique Student ID
- Gender and Profile Image
- Academic Stage, Group, and Study Type
- Unique QR Code: Automatically generated upon registration.

---

## 4. Lecture Management and Attendance Flow

### Session Workflow:
1. Lecture Creation:
   - Teacher enters a **Lecture Title** (e.g., "Introduction to OOP").
   - System **automatically** logs the current **Date** and **Time** upon creation.
   - **Device Session Lock:** A teacher can only have one active `lecture_session` at a time. Opening a new session will prompt to close the previous one or prevent multiple simultaneous scanners to ensure data integrity and prevent manipulation.
   - A unique scanning session/page is generated for each specific lecture.

2. Attendance Mode (Scanner Interface):

   - Professional UI with centered camera viewport.
   - Real-time statistics: Live counter of present students.
   - Full responsiveness (Mobile-First).
   - Success Feedback: Pop-up card with student photo, name, and exact timestamp.
   - Error Handling:
     - Duplicate Scan: Yellow alert for previously registered students in the same session.
     - Invalid Code: Red alert for unrecognized QR codes.
   - **Privacy and Security:**
     - **Data Encryption:** QR payloads are encrypted/signed to prevent forgery.
     - **Rate Limiting:** Protects the Scanner API from brute-force attempts.

3. Manual Entry:
   - Capability to override status for specific students.
   - **Editing Restriction:** Teachers can only modify attendance records within the same day of the lecture. After 24 hours, the record is locked for teachers and can only be edited by an Admin.
4. Session Closure: Finalizes attendance data and generates the session report.
5. **Activity Logs (Audit Trail):** Every action performed by a teacher (Lecture creation, QR scan, manual override, status change) is logged with a timestamp and IP address for Admin review.


---

## 5. Automated Warning System
- Level 1 Warning: 5 consecutive absences.
- Streak Reset: Attendance in the 6th lecture resets the consecutive absence counter.
- Recurring Warnings: Subsequent streaks of 5 absences trigger additional warning levels.

---

## 6. Dashboard and Reporting
- Admin Analytics: Total students, teachers, subjects, lectures, and attendance/absence ratios.
- Activity Logs: Dedicated view for Admins to monitor teacher actions and system changes.
- Professional Reporting: Excel exports with standardized columns (Name, ID, Status, Date, Stage, Group) and official formatting.


---

## 7. QR Print Center
Dedicated administrative interface for QR management:
- Advanced Filtering: Filter by stage, group, and study type.
- Grid View: Organized layout showing QR codes with student metadata.
- Printing Options:
   - Bulk Print: Print-friendly layout for all filtered students.
   - Individual Print: Single QR extraction for specific users.

---

## 8. UI/UX and Performance Standards
- Large Dataset Handling: Server-side pagination and virtual scrolling for 1000+ records.
- Navigation:
   - Responsive Sidebar: Converts to a drawer menu on mobile devices.
   - Tabbed Browsing: Student profiles open in new browser tabs for multi-tasking.
- Responsive Design: 100% compatibility across mobile, tablet, and desktop viewports.
- Search Functionality: Global search across all data tables.

---

## 9. Global System Settings
A centralized module for Admins to configure the environment:
- **Institutional Identity:** Upload College Logo, Name, and Header for reports.
- **Attendance Policies:**
   - Configure the 24-hour edit lock period (if needed to change).
   - Define custom warning thresholds (e.g., set warning at 3 or 7 absences instead of 5).
- **QR Customization:** Toggle logo overlay and color themes for generated QR codes.
- **Academic Year Management:**
   - Define and activate the current academic year (e.g., 2025-2026).
   - Semester control (First Semester / Second Semester).
   - Data Segregation: Attendance and records are tagged by the active year, allowing for historical data archiving.
- **Interface Language:** Selection between Arabic and English for the dashboard.

---

## 10. Archive Management Center
A dedicated, organized interface to access historical records:
- **Yearly Archives:** Select any previous academic year to view its snapshots.
- **Historical Data View:** View students, lectures, and attendance records as they were in that specific year.
- **Reports Retrieval:** Ability to re-export Excel reports from historical sessions.
- **Security:** Read-only access for teachers (if permitted) and full access for admins.

---

## 11. Dashboard and Homepage Layout
A dynamic, role-based landing page for quick oversight:

### Admin Dashboard:
- **Statistical Overview:** Quick-access cards showing (Total Students, Faculty Count, Active Subjects, Today's Lectures).
- **Attendance Insights:** A professional Bar Chart showing attendance vs. absence trends for the current week.
- **Critical Alerts:** List of students who just reached a "Warning 1" or "Warning 2" threshold today.
- **Recent System Activity:** A live feed of the last 5 actions performed in the system (from the Activity Log).

### Teacher Dashboard:
- **Daily Schedule:** List of the teacher's lectures scheduled for today with a "Start Session" button.
- **Quick Stats:** Individual performance metrics (How many students attended my last lecture?).
- **My Subjects:** Grid view of all assigned subjects for easy navigation.
- **Notifications:** Alerts for students in their groups who are approaching a warning level.

---

## 12. Future SaaS Integration



Designed with multi-tenancy in mind, allowing separate databases and administrative domains for different educational institutions under a centralized Super Admin control.
