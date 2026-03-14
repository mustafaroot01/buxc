<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', '=', $request->email, 'and')->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('بيانات الدخول غير صحيحة. يرجى التأكد من البريد الإلكتروني وكلمة المرور.', 401);
        }

        // Check for existing active tokens (to prevent multi-device sync conflicts)
        $activeTokensCount = $user->tokens()->count();
        if ($activeTokensCount > 0 && ! $request->boolean('force')) {
            return $this->error(
                'الحساب نشط حالياً على جهاز آخر. يرجى تسجيل الخروج من الجهاز السابق أولاً، أو استخدام خيار "فرض تسجيل الخروج" لتجنب تداخل البيانات.',
                403 // Forbidden
            );
        }

        // If force is requested, clear old tokens
        if ($request->boolean('force')) {
            $user->tokens()->delete();
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'roles' => $user->getRoleNames()
            ]
        ], 'تم تسجيل الدخول بنجاح.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'تم تسجيل الخروج بنجاح.');
    }
}

