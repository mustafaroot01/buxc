## 1. التجهيز قبل الرفع (SSH)
إذا كنت ترغب بسحب الكود عبر SSH بدلاً من الرفع اليدوي، اتبع ما يلي:

### أ. توليد مفتاح SSH على السيرفر (إذا لم يتوفر)
قم بالدخول عبر Terminal السيرفر ونفذ:
```bash
ssh-keygen -t ed25519 -C "your_email@example.com"
# اضغط Enter ثلاث مرات
```

### ب. إضافة المفتاح إلى GitHub
1. اعرض المفتاح العام: `cat ~/.ssh/id_ed25519.pub`
2. قم بنسخ النص الناتج.
3. اذهب إلى GitHub -> Settings -> SSH and GPG keys -> New SSH Key.
4. قم بلصق المفتاح وحفظه.

### ج. سحب الكود (Clone)
استخدم الرابط التالي للسحب:
```bash
git clone git@github.com:mustafaroot01/buxc.git .
```

## 2. التجهيز قبل الرفع (يدوي)
إذا كنت قد قمت بالبناء محلياً (`npm run build`) وتريد الرفع اليدوي، تأكد من استثناء `node_modules`.

## 2. ضغط الملفات
قم بضغط ملفات المشروع (Zip) **باستثناء** المجلدات التالية لتقليل الحجم وتسريع الرفع:
- `node_modules` (لا تحتاجه على السيرفر)
- `.git`
- `tests`

## 3. إعدادات الموقع في بلاسك
بمجرد دخولك إلى لوحة تحكم Plesk:
1. **Document Root:** تأكد من توجيه المسار إلى مجلد `public/` وليس المجلد الرئيسي للمشروع.
2. **PHP Version:** تأكد من استخدام نسخة PHP 8.2 أو أحدث.
3. **Database:** قم بإنشاء قاعدة بيانات جديدة ومستخدم لها، ثم احتفظ ببيانات الاتصال.

## 4. إعداد ملف الـ .env
بعد رفع الملفات وفك الضغط، قم بتعديل ملف `.env` على السيرفر بالبيانات التالية:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=اسم_قاعدة_البيانات
DB_USERNAME=اسم_المستخدم
DB_PASSWORD=كلمة_المرور
```

## 5. الأوامر الضرورية (عبر SSH أو Laravel Toolkit)
إذا كانت الاستضافة تدعم Laravel Toolkit في بلاسك، استخدمها لتنفيذ هذه الأوامر. إذا كنت تستخدم SSH:
```bash
# تنصيب المكتبات البرمجية (إذا لم ترفع مجلد vendor)
composer install --optimize-autoloader --no-dev

# تهجير قاعدة البيانات
php artisan migrate --force

# ربط مجلد التخزين بالعام
php artisan storage:link

# تنظيف وتحسين الكاش لسرعة الأداء
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 6. الصلاحيات (Permissions)
تأكد من أن المجلدات التالية قابلة للكتابة من قبل السيرفر:
- `storage/`
- `bootstrap/cache/`

## 7. حل مشكلة Node.js (npm command not found)
إذا ظهر لك خطأ متعلق بـ `nodenv` أو `npm` عند التحميل:
1. اختر نسخة Node.js المتوفرة في سيرفرك (مثلاً 22):
   ```bash
   nodenv local 22
   ```
2. تأكد من إعادة تشغيل الأمر:
   ```bash
   npm install && npm run build
   ```

## 8. مهام المجدول (Cron Job)
لضمان عمل تنبيهات الغياب والمهام التلقائية، أضف مهمة Cron في بلاسك:
`* * * * * php /path/to/your/project/artisan schedule:run >> /dev/null 2>&1`

---
**مبارك! النظام الآن جاهز للعمل على استضافتك.**
