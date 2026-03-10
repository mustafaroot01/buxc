<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all();
        // Pluck the settings into a key-value format for easy frontend use
        $formattedSettings = $settings->pluck('value', 'key');
        
        return Inertia::render('Admin/Settings/Index', [
            'settings' => $formattedSettings
        ]);
    }

    public function update(Request $request)
    {
        $settings = $request->input('settings', []);
        
        // Handle normal settings
        foreach ($settings as $key => $value) {
            if ($key !== 'login_logo') {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        }

        // Handle File upload for login_logo
        if ($request->hasFile('login_logo')) {
            $path = $request->file('login_logo')->store('settings', 'public');
            Setting::updateOrCreate(
                ['key' => 'login_logo'],
                ['value' => '/storage/' . $path]
            );
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}

