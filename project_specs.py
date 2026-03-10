# Student Attendance Management System - Project Specifications

# 1. Project Overview & General Info
project_info = {
    "name": "Student Attendance Management System",
    "description": "A high-performance web-based system for tracking academic attendance using encrypted QR codes.",
    "version": "1.0.0",
}

# 2. Technical Stack
technical_stack = {
    "backend": "Laravel 11 (Global Standard)",
    "frontend": "Vue.js 3 + Tailwind CSS + Vite",
    "database": "SQLite (Standardized Schema with UUIDs)", # Currently SQLite locally
    "frontend_architecture": {
        "framework": "Vue.js 3 (Composition API)",
        "bundler": "Vite",
        "styling": "Tailwind CSS",
        "icons": "Lucide Vue",
        "state_management": "Pinia",
        "routing": "Inertia.js + Vue Router",
        "http_client": "Axios (via Inertia)",
        "form_validation": "VeeValidate / Inertia Forms",
        "realtime": "Laravel Echo + Reverb",
        "qr_scanner": "html5-qrcode (Mobile-First Focus)",
        "charts": "Chart.js / ApexCharts"
    },
    "caching": "Redis",
    "real_time": "Laravel Reverb (WebSockets)",
    "background_processing": "Laravel Queue & Horizon (Redis-backed)",
    "deployment": "CI/CD via GitHub Actions",
    "qr_system": {
        "generator": "SimpleSoftwareIO QR Code (Encrypted Payloads)",
        "scanner": "html5-qrcode (Mobile-First Focus)"
    },
    "reports": "Laravel Excel ( maatwebsite/excel )"
}

# 3. User Roles
user_roles = {
    "super_admin": "Full system control, multi-tenant/global management, audit logs, system settings.",
    "admin": "Branch/College management, manages (Students, Teachers, Stages, Groups, Subjects), reports, archive.",
    "teacher": "Dashboard access, lecture management, scanning attendance, 24-hour edit window, profile management."
}

# 4. Database Schema (Actual Implementation)
database_schema = {
    "standards": ["UUID Primary Keys", "Soft Deletes", "Database Indexing (External IDs, QR payloads)"],
    "tables": [
        "users (id, full_name, email, password, roles)",
        "academic_stages (id, name, description, status)",
        "academic_groups (id, name, stage_id, study_type: morning/evening)",
        "subjects (id, name, code, stage_id, teacher_id)",
        "students (id, full_name, student_id, gender, stage_id, group_id, study_type, qr_code_payload, status)",
        "lectures (id, title, subject_id, teacher_id, stage_id, group_id, study_type, date, time)",
        "attendances (id, lecture_id, student_id, status: present/absent/late, scanned_at, method)",
        "warnings (id, student_id, level, reason, generated_at)",
        "activity_log (Spatie tracking)",
        "settings (K-V pairs for global config)"
    ]
}

# 5. UI/UX Standards & Layout
ui_ux_standards = {
    "responsiveness": "100% Mobile-First (Tailwind CSS, RTL Setup)",
    "language": "Arabic Built-in (dir=rtl, Tajawal font)",
    "visuals": "Modern Glassmorphism, Gradients, Professional Iconography (Lucide Vue), No emojis for main actions.",
    "live_updates": "WebSockets for real-time scanner dashboard statistics",
    "main_layout": {
        "components": [
            "Sidebar Navigation",
            "Top Navigation Bar",
            "Main Content Area",
            "Global Notification System",
            "Modal System"
        ]
    },
    "navigation": {
        "sidebar": [
            "Dashboard",
            "Students (الطلاب)",
            "Teachers (الأساتذة)",
            "Stages (المراحل)",
            "Groups (المجموعات)",
            "Subjects (المواد)",
            "Lectures (المحاضرات)",
            "Warnings (الإنذارات)",
            "Reports (التقارير)",
            "Print QR (طباعة بطاقات QR)",
            "Archive (الأرشيف)",
            "Audit Logs (سجل النشاطات)",
            "Settings (الإعدادات)"
        ]
    }
}

# 6. Core Pages & Modules

dashboard_page = {
    "statistics_cards": [
        "Total Students",
        "Total Lectures Today",
        "Attendance Rate Today",
        "Active Teachers"
    ],
    "charts": [
        "Weekly Attendance Chart",
        "Absence Distribution",
        "Warnings Overview"
    ],
    "live_widgets": [
        "Real-time Scan Counter",
        "Recent Attendance Activity",
        "Latest Warnings"
    ]
}

students_management = {
    "hierarchy": ["Stages (1-4)", "Groups (A, B, C)", "Study Type (Morning/Evening)"],
    "students_table": {
        "columns": [
            "Photo", "Full Name", "Student ID", "Stage", "Group", "Study Type", "QR Code", "Actions"
        ],
        "features": ["Search", "Filters", "Pagination", "Bulk Actions", "Export"]
    },
    "student_profile": {
        "sections": ["Personal Information", "Attendance History", "Warnings History", "QR Code Preview"]
    }
}

lecture_management = {
    "creation_fields": ["Lecture Title", "Subject", "Stage", "Group", "Study Type", "Date/Time"],
    "lecture_list": {
        "columns": ["Title", "Subject", "Teacher", "Date", "Time", "Attendance Count", "Actions"]
    },
    "restrictions": "24-hour edit lock for teachers; only Admins can edit after one day."
}

qr_scanner_ui = {
    "design": "Mobile-first scanning interface directly linked from an active lecture.",
    "components": [
        "Camera Scanner Window",
        "Live Attendance Counter",
        "Last Scanned Student Profile/Info",
        "Scan Status Alerts"
    ],
    "alerts": {
        "success": "Green - Attendance Recorded",
        "duplicate": "Yellow - Already Scanned",
        "invalid": "Red - Invalid QR Code/Different Group"
    },
    "protection": {
        "device_session_lock": "Only one active scanning session per teacher to prevent manipulation",
        "encryption": "AES or signed tokens in QR",
        "rate_limiting": "API protection against brute-force"
    }
}

warnings_system = {
    "rules": {
        "level_1": "5 consecutive absences trigger level 1 warning",
        "reset": "Attendance on 6th lecture resets consecutive counter",
        "level_2": "Next 5 absences trigger level 2"
    },
    "warnings_table": {
        "columns": ["Student Name", "Student ID", "Warning Level", "Absence Count", "Date"],
        "filters": ["Stage", "Group", "Warning Level"]
    }
}

reports_and_exports = {
    "report_types": ["Attendance Report", "Absence Report", "Warnings Report"],
    "filters": ["Date Range", "Stage", "Group", "Subject", "Teacher"],
    "export_formats": ["Excel", "PDF"],
    "printing_center": "Filterable grid for bulk or individual QR code printing (Print-friendly CSS)",
    "heavy_tasks_management": {
        "engine": "Laravel Queue & Horizon",
        "tasks": ["PDF/Excel Report Generation", "Bulk Student QR Generation", "Bulk Printing Jobs", "Automated Warning Calculations"]
    }
}

if __name__ == "__main__":
    print(f"Project: {project_info['name']}")
    print(f"Stack: {technical_stack['backend']} & Vue.js 3 / Tailwind CSS (Arabic RTL)")
    print("Full specifications consolidated successfully according to actual DB mapping.")
