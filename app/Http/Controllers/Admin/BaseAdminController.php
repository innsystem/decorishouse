<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Integrations\ShopeeIntegration;
use App\Jobs\ProcessNotificationJob;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
// use App\Integrations\GoogleAnalyticsIntegration;

class BaseAdminController extends Controller
{
    public function index()
    {
        return view('admin.pages.home');
    }

    public function settings()
    {
        $getSetting = new Setting;

        $result = [
            'logo' => $getSetting->getValue(('logo')),
            'favicon' => $getSetting->getValue(('favicon')),
            'meta_title' => $getSetting->getValue(('meta_title')),
            'meta_keywords' => $getSetting->getValue(('meta_keywords')),
            'meta_description' => $getSetting->getValue(('meta_description')),
            'script_head' => $getSetting->getValue(('script_head')),
            'script_body' => $getSetting->getValue(('script_body')),
            'site_name' => $getSetting->getValue(('site_name')),
            'site_proprietary' => $getSetting->getValue(('site_proprietary')),
            'site_document' => $getSetting->getValue(('site_document')),
            'site_email' => $getSetting->getValue(('site_email')),
            'telephone' => $getSetting->getValue(('telephone')),
            'cellphone' => $getSetting->getValue(('cellphone')),
            'address' => $getSetting->getValue(('address')),
            'hour_open' => $getSetting->getValue(('hour_open')),
            'facebook' => $getSetting->getValue(('facebook')),
            'instagram' => $getSetting->getValue(('instagram')),
            'tiktok' => $getSetting->getValue(('tiktok')),
            'youtube' => $getSetting->getValue(('youtube')),
            'client_id' => $getSetting->getValue(('client_id')),
            'client_secret' => $getSetting->getValue(('client_secret')),
        ];

        return view('admin.pages.settings', compact('result'));
    }

    public function settingsUpdate(Request $request)
    {
        $settings = $request->all();

        foreach ($settings as $key => $value) {
            // Exception Logo AND Favicon
            if ($key != 'logo' && $key != 'favicon') {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        }

        try {
            Cache::forget('settings');
        } catch (\Exception $e) {
            \Log::error('BaseAdminController :: settingsUpdate' . $e->getMessage());
            return response()->json($e->getMessage(), 500);
        }

        return response()->json('Configurações atualizadas com sucesso', 200);
    }

    public function updateImages(Request $request)
    {
        $pathResponse = '';

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            Setting::updateOrCreate(
                ['key' => 'logo'],
                ['value' => $logoPath]
            );

            $pathResponse = $logoPath;
        }

        if ($request->hasFile('favicon')) {
            $faviconPath = $request->file('favicon')->store('favicons', 'public');
            Setting::updateOrCreate(
                ['key' => 'favicon'],
                ['value' => $faviconPath]
            );

            $pathResponse = $faviconPath;
        }

        try {
            Cache::forget('settings');
        } catch (\Exception $e) {
            \Log::error('BaseAdminController :: settingsUpdate' . $e->getMessage());
            return response()->json($e->getMessage(), 500);
        }

        return response()->json(asset('storage/' . $pathResponse), 200);
    }
}
