<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        $settings = Setting::all()->keyBy('name');

        return response()->view('settings.index', [
            'header' => 'Settings',
            'settings' => $settings,
        ]);
    }

    public function store(Request $request)
    {
        $settings = Setting::all()->keyBy('name');

        foreach ($settings as $setting) {
            if ($request->get($setting->name) !== null) {
                $setting->update([
                    'value' => $request->get($setting->name)
                ]);
            }
        }

        return redirect()->to('/settings');
    }
}
