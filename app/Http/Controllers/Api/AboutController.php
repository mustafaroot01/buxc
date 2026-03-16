<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class AboutController extends Controller
{
    use ApiResponse;

    public function __invoke()
    {
        $aboutData = [
            'department' => [
                'name' => 'قسم هندسة تقنيات الحاسوب',
                'college' => 'كلية التقنيات الهندسية',
                'university' => 'جامعة بلاد الرافدين',
                'logo_url' => url('/img/logo_dept.jpg'),
            ],
            'developers_heading' => 'الطلاب المطورون',
            'created_with' => 'تم صنع بـ <3 في جامعة بلاد الرافدين',
            'team' => [
                [
                    'name' => 'مصطفي سمير غني',
                    'role' => 'مطوّر برمجيات',
                    'image_url' => url('/img/mustafa_samir.jpg'),
                ],
                [
                    'name' => 'مصطفي قاسم نعمان',
                    'role' => 'مطوّر برمجيات',
                    'image_url' => url('/img/mustafa_qasim.jpg'),
                ],
                [
                    'name' => 'اثير حارث خالد',
                    'role' => 'مطوّر برمجيات',
                    'image_url' => url('/img/ather_harith.jpg'),
                ],
            ],
            'copyright' => '© 2026 جميع الحقوق محفوظة - قسم هندسة تقنيات الحاسوب'
        ];

        return $this->success($aboutData, 'تم جلب بيانات "عن التطبيق" بنجاح.');
    }
}
