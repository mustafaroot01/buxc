# دليل تكامل API المزامنة (v1) - تحديث مارس 2026

هذا الدليل مخصص لمطوري التطبيقات لربط نظام الحضور والغياب مع السيرفر، مع دعم كامل للمزامنة الأوفلاين (Offline Sync).

---

## 1. المدخل الرئيسي للبيانات (Aggregate Init)
بدلاً من طلب كل قائمة على حدة عند فتح التطبيق، استخدم هذا المسار لجلب (البروفايل + المواد + المحاضرات النشطة اليوم) في طلب واحد.

- **Endpoint:** `GET /api/v1/teacher/init`
- **Response Example:**
```json
{
  "success": true,
  "data": {
    "profile": { "id": "uuid", "full_name": "اسم المدرس", "email": "email@test.com" },
    "subjects": [ { "id": 1, "name": "رياضيات", "groups": [...] } ],
    "active_lectures": [ { "id": "uuid", "title": "محاضرة 1", "status": "active" } ]
  }
}
```

---

## 2. مزامنة الطلاب (Incremental Students Sync)
لجلب الطلاب وتخزينهم في قاعدة بيانات التطبيق المحلية.

- **Endpoint:** `GET /api/v1/teacher/students?since_version=0`
- **حقول الطالب الهامة:**
    - `id`: المعرف الفريد (UUID).
    - `first_name`: الاسم الأول.
    - `second_name`: اسم الأب.
    - `last_name`: اللقب/العشيرة.
    - `full_name`: الاسم الكامل المدمج (للعرض فقط).
    - `student_external_id`: الرقم التعريفي للطالب.
    - `qr_hash`: الـ Hash الذي يتم مقارنته مع الكود الممسوح.
    - `version`: رقم الإصدار للمزامنة التدريجية.

---

## 3. إرسال سجلات الحضور (Batch Attendance Sync)
يتم إرسال كافة الطلاب الممسوحين "أوفلاين" إلى هذا المسار.

- **Endpoint:** `POST /api/v1/attendance/sync`
- **Payload Structure:**
```json
{
  "sync_id": "uuid-per-batch",
  "lecture_id": "lecture-uuid",
  "device_info": {
    "id": "hardware-id",
    "model": "iPhone 14",
    "os_version": "17.1",
    "app_version": "1.2.0"
  },
  "sent_at": "2026-03-15T10:00:00Z",
  "scans": [
    {
      "student_id": "student-uuid",
      "scanned_at": "2026-03-15T09:30:00Z",
      "request_id": "unique-scan-uuid"
    }
  ]
}
```

---

## 4. ملاحظات هامة للمبرمج
1. **Idempotency:** السيرفر يستخدم `request_id` لمنع تكرار تسجيل الطالب إذا أرسلت نفس البيانات مرتين.
2. **Column Names:** تأكد من استخدام `first_name` و `last_name` عند عرض البيانات بدلاً من عمود `name` القديم.
3. **Roles:** النظام يعتند على `Spatie Permission`. عند تسجيل الدخول، تأكد من تخزين التوكّن واستخدامه في جميع الطلبات اللاحقة.
4. **403 Errors:** إذا واجهت خطأ 403 فور تسجيل الدخول، جرب تحديث الصفحة (Refresh) لضمان اكتمال تحميل الجلسة.
