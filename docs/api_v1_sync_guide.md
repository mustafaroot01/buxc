# دليل تكامل API المزامنة (v1) - نظام الحضور والغياب

هذا المستند مخصص لمطور تطبيق الموبايل لربط النظام الجديد للمزامنة الأوفلاين (Offline Sync) وإدارة الجلسات.

---

## 1. تسجيل الدخول وإدارة الجلسة
يسمح النظام الآن بجلسة واحدة فقط لكل حساب لضمان دقة مزامنة البيانات.

- **Endpoint:** `POST /api/v1/login`
- **Payload:**
```json
{
  "email": "teacher@example.com",
  "password": "password123",
  "device_name": "iPhone 15 Pro",
  "force": false 
}
```
- **الملاحظات:**
    - إذا كان الحساب مسجلاً في جهاز آخر، سيعود الرد بـ `403 Forbidden`.
    - عند إرسال `"force": true` سيتم تسجيل الخروج من كافة الأجهزة الأخرى ومنحك توكّن جديد.

---

## 2. المزامنة التدريجية للطلاب (Incremental Students Sync)
جلب الطلاب المضافين أو المعدلين أو المحذوفين فقط. يبدأ التطبيق بـ `since_version = 0` لأول مرة.

- **Endpoint:** `GET /api/v1/teacher/students?since_version=50`
- **Headers:** `Authorization: Bearer {token}`
- **Response:**
```json
{
  "success": true,
  "data": {
    "students": [
      {
        "id": "uuid-1",
        "full_name": "احمد علي",
        "qr_hash": "sha256-hash-of-payload",
        "group_name": "المجموعة A",
        "version": 55
      }
    ],
    "deleted_ids": ["uuid-old-99"],
    "sync_version": 55,
    "server_time": "2024-03-15T01:40:00Z"
  }
}
```
- **المطلوب:** يجب على التطبيق تخزين `sync_version` الأخير واستخدامه في الطلب القادم كـ `since_version`.

---

## 3. المزامنة التدريجية للمحاضرات (Incremental Lectures Sync)
تحديث قائمة المحاضرات الخاصة بالمدرس.

- **Endpoint:** `GET /api/v1/teacher/lectures/sync?since_version=10`
- **Response:**
```json
{
  "success": true,
  "data": {
    "lectures": [
      {
        "id": "uuid-lecture-1",
        "title": "محاضرة الرياضيات",
        "status": "active",
        "version": 12
      }
    ],
    "deleted_ids": [],
    "sync_version": 12
  }
}
```

---

## 4. إرسال الحضور دفعة واحدة (Batch Attendance Sync)
إرسال كافة السجلات المخزنة أوفلاين إلى السيرفر.

- **Endpoint:** `POST /api/v1/attendance/sync`
- **Payload:**
```json
{
  "sync_id": "unique-uuid-per-batch",
  "lecture_id": "lecture-uuid",
  "device_info": {
    "id": "device-unique-hardware-id",
    "model": "Samsung S24 Ultra",
    "os_version": "Android 14",
    "app_version": "1.1.0"
  },
  "sent_at": "2024-03-15T10:45:00Z",
  "scans": [
    {
      "student_id": "student-uuid-1",
      "scanned_at": "2024-03-15T09:30:15Z",
      "request_id": "uuid-unique-per-single-scan"
    },
    {
      "student_id": "student-uuid-2",
      "scanned_at": "2024-03-15T09:35:10Z",
      "request_id": "uuid-unique-per-single-scan-2"
    }
  ]
}
```
- **القواعد السلوكية:**
    - يجب توليد `request_id` فريد لكل عملية مسح (Scan) لحماية البيانات من التكرار في حال إعادة الإرسال.
    - السيرفر يعالج البيانات في الخلفية باستخدام Redis لتصفير الغيابات فوراً.

---

## 5. ملاحظات تقنية عامة
- تفعيل الـ `X-Device-ID` في الـ Headers يساعد النظام على تتبع الأخطاء بدقة أكبر.
- جميع الاستجابات تعود بصيغة JSON موحدة.
- أي فشل في الـ API سيتم تسجيله تلقائياً في السيرفر للمراجعة في "مركز أخطاء API".
