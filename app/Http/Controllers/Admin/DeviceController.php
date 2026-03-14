<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Device;
use Inertia\Inertia;


class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::latest('last_seen_at')
            ->paginate(20);

        return Inertia::render('Admin/System/Devices', [
            'devices' => $devices,
        ]);
    }

    public function toggleStatus(Device $device)
    {
        $device->update([
            'status' => $device->status === 'active' ? 'blocked' : 'active',
        ]);

        return back()->with('success', 'تم تحديث حالة الجهاز بنجاح.');
    }
}
