# Teacher API Documentation (Mobile App)

هذا الملف يحتوي على توثيق شامل لجميع المسارات (Endpoints) المطلوبة لبناء تطبيق الموبايل الخاص بالأستاذ.

## معلومات عامة
- **Base URL:** `http://your-domain.com/api`
- **Content-Type:** `application/json`
- **Accept:** `application/json`
- **Authentication:** يتم استخدام Laravel Sanctum. يجب إرسال الـ Token في الهيدر كـ `Authorization: Bearer {TOKEN}` لجميع المسارات المحمية.

---

## 1. المصادقة (Authentication)

### تسجيل الدخول
- **Endpoint:** `POST /login`
- **Parameters:**
    - `email` (string, required)
    - `password` (string, required)
    - `device_name` (string, required): اسم الجهاز (مثلاً: iPhone 13).
- **Response (200):**
```json
{
    "token": "1|xxxxxxxxxxxxxxx",
    "user": {
        "id": 1,
        "name": "الاسم",
        "email": "teacher@example.com",
        "roles": ["teacher"]
    }
}
```

### تسجيل الخروج
- **Endpoint:** `POST /logout` (محمي)
- **Response (200):** `{"message": "Logged out successfully."}`

---

## 2. لوحة التحكم (Dashboard)

- **Endpoint:** `GET /teacher/dashboard` (محمي)
- **الوصف:** استرجاع الإحصائيات، المحاضرات النشطة لليوم، وأحدث تنبيهات الطلاب.
- **Response (200):**
```json
{
    "stats": {
        "my_subjects_count": 5,
        "todays_lectures_count": 2,
        "total_lectures_given": 45,
        "total_students_count": 150
    },
    "active_lectures": [...],
    "recent_warnings": [...]
}
```

---

## 3. المحاضرات (Lectures)

### عرض قائمة المحاضرات
- **Endpoint:** `GET /teacher/lectures` (محمي)
- **Filters (Optional):** `search`, `subject_id`, `stage_id`, `status` (active/closed).
- **Response (200):** استجابة Pagination تحتوي على قائمة المحاضرات مع أعداد الحضور والغياب.

### إنشاء محاضرة جديدة
- **Endpoint:** `POST /teacher/lectures` (محمي)
- **Parameters:**
    - `title` (string, required)
    - `subject_id` (int, required)
    - `stage_id` (int, required)
    - `group_id` (int, required)
    - `study_type` (morning/evening, required)
    - `date` (YYYY-MM-DD, optional - default: today)
    - `time` (HH:mm, optional - default: now)

### تفاصيل المحاضرة وكشف الطلاب
- **Endpoint:** `GET /teacher/lectures/{id}` (محمي)
- **Response (200):** يحتوي على بيانات المحاضرة وقائمة بالطلاب (`students`) مع حالة حضور كل واحد (`present`/`absent`) وبروفايل الصورة.

### تسجيل حضور يدوي (Toggle)
- **Endpoint:** `POST /teacher/lectures/{id}/toggle-attendance` (محمي)
- **Parameters:** `student_id` (int, required).
- **الوصف:** إذا كان الطالب غائباً سيتم تسجيله حاضراً، والعكس صحيح.

### إنهاء المحاضرة
- **Endpoint:** `PATCH /teacher/lectures/{id}` (محمي)
- **Parameters:** `status` = `closed`.

---

## 4. ماسح الـ QR (QR Scanner)

- **Endpoint:** `POST /scan` (محمي)
- **Parameters:**
    - `lecture_id` (int, required)
    - `qr_payload` (string, required): البيانات المشفرة المستخرجة من رمز الـ QR.
- **Response (200):**
```json
{
    "message": "Attendance recorded successfully.",
    "student": {
        "name": "اسم الطالب",
        "time": "14:20"
    }
}
```

---

## 5. التنبيهات (Warnings)

- **Endpoint:** `GET /teacher/warnings` (محمي)
- **الوصف:** عرض جميع الطلاب الذين لديهم تنبيهات غياب حالية.

---

---

## 🚫 تنبيه هام: مشكلة التحويل (Redirect) لصفحة الأدمن
إذا كنت تختبر الـ API ووجدته يقوم بتحويلك لصفحة تسجيل الدخول (`/login` أو `/admin/teachers/login`) بدلاً من إرجاع خطأ `401 Unauthorized` أو بيانات، فهذا يعني أن تطبيق الموبايل **لا يرسل الهيدر المطلوب**.

**يجب إضافة هذا الهيدر في كل طلب:**
```http
Accept: application/json
```
بدون هذا الهيدر، يعتبر Laravel أن الطلب مرسل من "متصفح ويب" ويقوم بتحويله لصفحة تسجيل الدخول العادية.

---
# توثيق الـ API المطور (V1 - Standard) 🚀

تم تحديث الـ API ليكون أكثر استقراراً وسهولة في الربط مع تطبيق الموبايل (Flutter/Mobile).

## 1. المعلومات الأساسية
- **Base URL:** `https://bucx.diyala.net/api/v1`
- **Response Format:** جميع الردود تأتي بتنسيق JSON موحد:
  ```json
  {
    "success": true,
    "message": "رسالة توضيحية بالعربية",
    "data": { ... }
  }
  ```

## 2. مسار البداية الشامل (Init) - [NEW]
هذا المسار يعطيك كل شي يحتاجه التطبيق عند الفتح بطلب واحد فقط!
- **URL:** `GET /teacher/init`
- **Headers:** `Authorization: Bearer {token}`
- **البيانات التي يرجعها:** (الملف الشخصي، المواد المسؤولة، المراحل، المجموعات، والمحاضرات النشطة حالياً).

## 3. تسجيل الدخول
- **URL:** `POST /login`
- **Body:** `{"email": "...", "password": "...", "device_name": "..."}`

## 4. مسح الـ QR
- **URL:** `POST /scan`
- **Body:** `{"lecture_id": "...", "qr_payload": "..."}`
- **الرد:** يرجع تفاصيل الطالب (الاسم والصورة ووقت الحضور) مع رسالة نجاح.
## 🚀 أمثلة برمجية للربط (Flutter/Dart)

### 1. تسجيل الدخول والحصول على الـ Token
```dart
Future<String?> loginTeacher(String email, String password) async {
  final url = Uri.parse('https://bucx.diyala.net/api/login');
  
  final response = await http.post(
    url,
    headers: {
      'Accept': 'application/json', // ضروري جداً لمنع الـ Redirect
      'Content-Type': 'application/json',
    },
    body: jsonEncode({
      'email': email,
      'password': password,
      'device_name': 'Mobile_App',
    }),
  );

  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    // تأكد أن المستخدم "أستاذ"
    if (data['user']['roles'].contains('teacher')) {
      return data['token']; // احفظ هذا الـ Token لاستخدامه لاحقاً
    } else {
      throw Exception("هذا الحساب ليس حساب أستاذ");
    }
  } else {
    throw Exception("خطأ في تسجيل الدخول: ${response.body}");
  }
}
```

### 2. جلب إحصائيات لوحة التحكم
```dart
Future<void> getDashboard(String token) async {
  final url = Uri.parse('https://bucx.diyala.net/api/teacher/dashboard');
  
  final response = await http.get(
    url,
    headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token', // إرسال التوكن هنا
    },
  );
  
  if (response.statusCode == 200) {
    print(jsonDecode(response.body));
  }
}
```

---

## 6. الملف الشخصي (Profile)

### عرض البيانات
- **Endpoint:** `GET /teacher/profile` (محمي)

### تحديث البيانات
- **Endpoint:** `POST /teacher/profile` (محمي)
- **Parameters:**
    - `full_name`, `email`, `phone_number`, `department`, `academic_title`
    - `password`, `password_confirmation` (اختياري)
    - `photo` (file, image, optional)
