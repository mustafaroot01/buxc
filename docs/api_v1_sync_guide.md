# دليل التكامل التقني لـ API (v1) - نظام الحضور والغياب 📱🌐

هذا الدليل موجه لمطور تطبيقات الموبايل. يغطي جميع المسارات (Endpoints) اللازمة لبناء نظام يعمل بكفاءة عالية مع دعم المزامنة "أوفلاين".

---

## 1. المعلومات الأساسية (Base Info)
- **Base URL:** `https://bucx.diyala.net/api/v1`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer {token}` (للمسارات المحمية)
  - `X-Device-ID: {unique_id}` (يفضل إرساله لتتبع الأخطاء)

---

## 2. المصادقة (Authentication)

### 🔑 تسجيل الدخول (Login)
يدعم النظام ميزة "الجلسة الواحدة" (Single Session).

- **Endpoint:** `POST /login`
- **Payload:**
```json
{
  "email": "teacher@example.com",
  "password": "password",
  "device_name": "iPhone 15 Pro",
  "force": false 
}
```
- **الملاحظات:**
  - إذا كان المستخدم مسجلاً في جهاز آخر، سيعود الرد بـ `403`.
  - اطلب من المستخدم "تبديل الجهاز" وأرسل `"force": true` لتسجيل خروجه من الأجهزة القديمة.

---

## 3. جلب البيانات الأولية (Initial Data Load)

### 📦 حزمة البيانات الأساسية (Init)
بدلاً من نداء مسارات متعددة، استخدم هذا المسار عند فتح التطبيق لجلب (البروفايل، المواد، والمحاضرات النشطة حالياً).

- **Endpoint:** `GET /teacher/init`
- **Response Example:**
```json
{
  "success": true,
  "data": {
    "profile": { "id": "uuid", "full_name": "أحمد علي", "email": "a@test.com" },
    "subjects": [
      {
        "id": "subject-uuid",
        "name": "البرمجة",
        "groups": [
          { "id": "group-uuid", "name": "المجموعة الأولى", "stage_name": "المرحلة الثالثة" }
        ]
      }
    ],
    "active_lectures": [
      { "id": "lecture-uuid", "title": "محاضرة 1", "status": "active" }
    ]
  }
}
```

---

## 4. المزامنة والبيانات الضخمة (Data Sync)

### 👥 مزامنة الطلاب (Incremental Students)
لجلب الطلاب وتحديث قاعدة بيانات التطبيق المحلية.

- **Endpoint:** `GET /teacher/students?since_version=0`
- **Response:**
```json
{
  "success": true,
  "data": {
    "students": [
      {
        "id": "uuid",
        "first_name": "محمد",
        "second_name": "جاسم",
        "last_name": "الساعدي",
        "full_name": "محمد جاسم الساعدي",
        "student_external_id": "ST-12345",
        "qr_hash": "sha256_hash_here",
        "group_name": "م1",
        "version": 1710500000 
      }
    ],
    "deleted_ids": [],
    "sync_version": 1710500000
  }
}
```
> **مهم:** احفظ رقم الـ `sync_version` وأرسله في الطلب القادم كـ `since_version` لجلب التحديثات الجديدة فقط.

---

## 5. عمليات الحضور (Attendance)

### 📡 إرسال الحضور (Batch Sync)
يُستخدم لإرسال الطلاب الممسوحين "أوفلاين" كدفعة واحدة.

- **Endpoint:** `POST /attendance/sync`
- **Payload:**
```json
{
  "sync_id": "unique-uuid",
  "lecture_id": "lecture-uuid",
  "device_info": {
    "id": "device_unique_id",
    "model": "iPhone 13",
    "os_version": "iOS 17.1",
    "app_version": "1.0.4"
  },
  "action_type": "scan", // "scan" (QR) or "manual" (Teacher manual entry)
  "sent_at": "2024-03-15T10:00:00Z",
  "scans": [
    {
      "student_id": "student-uuid-OR-offline_id",
      "scanned_at": "2026-03-15T09:30:00Z",
      "request_id": "unique-scan-uuid"
    }
  ]
}
```
> **ملاحظة للمطور:** الـ `lecture_id` والـ `student_id` في هذا المسار يقبلان الآن المعرف الحقيقي (UUID) **أو** المعرف المؤقت الذي يبدأ بـ `offline_`. السيرفر سيقوم بمطابقتهم تلقائياً.

---

## 6. إدارة المحاضرات (Lecture Management)

### ➕ إنشاء محاضرة (Create Lecture)
عند إنشاء محاضرة وأنت "أوفلاين"، قم بإرسال المعرف المؤقت في حقل `offline_id`.

- **Endpoint:** `POST /teacher/lectures`
- **Payload:**
```json
{
  "title": "اسم المحاضرة",
  "subject_id": "uuid",
  "group_id": "uuid",
  "offline_id": "offline_unique_temp_id"
}
```

### 👆 تسجيل يدوي (Toggle Manual)
- **Endpoint:** `POST /teacher/lectures/{lecture_id}/toggle-attendance`
- **Payload:** `{ "student_id": "uuid" }`

---

## 6. ملاحظات تقنية للمبرمج (Critical Notes)
1. **الأسماء:** قاعدة البيانات تفصل الاسم (`first_name`, `second_name`, `last_name`). لا تبحث عن حقل `name`.
2. **Idempotency:** السيرفر يرفض تكرار نفس الـ `request_id` لنفس الطالب في نفس المحاضرة.
3. **QR Matching:** السيرفر يرسل `qr_hash` (sha256). التطبيق يجب أن يقوم بعمل hash للكود الممسوح ومقارنته محلياً.
4. **التوقيت:** استخدم دائماً صيغة ISO 8601 للتواريخ (`2026-03-15T14:00:00Z`).
