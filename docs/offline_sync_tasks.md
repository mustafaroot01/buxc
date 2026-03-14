# قائمة مهام تنفيذ نظام المزامنة (Offline Sync Tasks)

تم استخراج هذه المهام بناءً على المخطط التقني الرصين لضمان تنفيذ احترافي ومتكامل.

## المرحلة الأولى: بنية قاعدة البيانات (Backend Migrations)
- [ ] إضافة حقول `version` (BIGINT) و `deleted_at` لجدول `students`.
- [ ] تحديث جدول `attendances`:
    - [ ] إضافة `request_id` بصيغة `BINARY(16)`.
    - [ ] إضافة `device_id` (string).
    - [ ] إضافة `scanned_at` (التوقيت المحلي للموبايل).
    - [ ] إضافة **Unique Constraint** على (`lecture_id`, `student_id`).
    - [ ] إضافة **Index** لتحسين سرعة البحث.
- [ ] إنشاء جدول `attendance_sync_logs`:
    - [ ] الحقول: `id, device_id, lecture_id, sync_id, scans_received, scans_processed, failed_scans, sent_at, synced_at, duration_ms, status`.

## المرحلة الثانية: تطوير الـ API (Laravel Endpoints)
- [ ] تنفيذ الـ Endpoint الخاص بجلب الطلاب `GET /api/teacher/students`:
    - [ ] دعم معامل `since_version`.
    - [ ] **منطق التصفية:** جلب الطلاب المرتبطين بالمجموعات التي يُدرّسها الأستاذ فقط بناءً على المواد الدراسية المسندة له.
    - [ ] **البيانات المرفقة:** التأكد من إرسال (اسم المرحلة، اسم الكروب، نوع الدراسة) لكل طالب.
    - [ ] تنفيذ منطق الـ `Limit` (1000 سجل) والـ `Order By`.
    - [ ] إرسال `server_time` و `sync_version` الجديدة.
- [ ] تنفيذ الـ Endpoint الخاص بالمزامنة `POST /api/attendance/sync`:
    - [ ] التحقق من الـ Payload (Validation).
    - [ ] تنفيذ الـ `DB::transaction`.
    - [ ] تطبيق قاعدة **"الأقدم يفوز" (Earliest Scan Wins)** عند معالجة التكرار.
    - [ ] تسجيل العملية في `attendance_sync_logs`.

## المرحلة الثالثة: المنطق البرمجي والأمان (Logic & Security)
- [ ] تنفيذ نظام الـ `Versioning` التلقائي (زيادة الرقم عند أي تعديل على بيانات الطالب).
- [ ] إعداد الـ **Redis Queue** للمهام الخلفية:
    - [ ] حساب الـ `Streak` والغيابات المتسلسلة.
    - [ ] إرسال التنبيهات (Push Notifications).
- [ ] تفعيل الـ **Rate Limiting (Throttle)** على برمجيات المزامنة.
- [ ] حماية الـ Endpoints باستخدام **JWT**.

## المرحلة الرابعة: تطبيق الموبايل - البنى التحتية (Mobile Infrastructure)
- [ ] إعداد قاعدة بيانات **SQLCipher** المشفرة محلياً.
- [ ] تنفيذ نظام الـ **Two-Stage Lookup** للـ QR (Prefix + Full Hash).
- [ ] إنشاء جدول `pending_scans` للـ Queue المحلي.
- [ ] تنفيذ منطق المزامنة اليدوية وإدارة الـ Batches.

## المرحلة الخامسة: تطبيق الموبايل - التجربة والواجهة (Mobile UI/UX)
- [ ] تصميم بطاقة هوية الطالب (Identity Card) فور المسح.
- [ ] إضافة مؤشر حالة المزامنة والعداد (Pending Badge).
- [ ] بناء واجهة لوحة المزامنة (Sync Dashboard).
- [ ] تنفيذ التنبيه المستمر (Persistent Notification) للسجلات غير المرفوعة.

## المرحلة السادسة: لوحة السوبر أدمن (Monitoring Tools)
- [ ] بناء واجهة مراقبة المزامنة اللحظية (Sync Monitoring Wall).
- [ ] إضافة قسم إدارة الأجهزة والنسخ (Device Control).
- [ ] إنشاء واجهة لمراقبة حالة الـ Redis Queue.
- [ ] بناء أداة مراجعة التعارضات (Conflict Resolution Review).

---
> [!IMPORTANT]
> يرجى التأشير على المهام المنجزة تباعاً لضمان سير العمل وفق المخطط الرصين.
أداء الفائق: تفعيل المعالجة الخلفية (Redis Queues) لتحديث الغيابات والتنبيهات دون التأثير على سرعة تطبيق الموبايل.