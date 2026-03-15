<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // تصفير الريديس من جهة الويب (القادرة على الاتصال)
    Illuminate\Support\Facades\Redis::flushall();
    echo "<h1>✅ تم تصفير العمال الأشباح بنجاح!</h1>";
    echo "<p>اذهب للداشبورد الآن واعمل Refresh، ستجد الرقم أصبح 5 أو 0 (وسيعود لـ 5 فوراً عند بدء العمال الجدد).</p>";
} catch (\Exception $e) {
    echo "<h1>❌ خطأ في الاتصال:</h1>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
