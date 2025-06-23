<?php

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

if (!function_exists('logActivity')) {
    function logActivity($event, $model = null, $data = [], $userId = null)
    {
        try {
            $activity = ActivityLog::create([
                'user_id'    => $userId ?? optional(auth()->user())->id,
                'event'      => $event,
                'model'      => $model ? class_basename($model) : null,
                'model_id'   => $model?->id,
                'data'       => $data,
                'ip'         => request()->ip(),
                'user_agent' => json_encode(parseUserAgent(request()->userAgent()))
            ]);

            Log::info($activity);
        } catch (\Throwable $e) {
            Log::error('Activity log failed', ['error' => $e->getMessage()]);
        }
    }

    function parseUserAgent($userAgent)
    {
        $platform = 'Unknown';
        $browser = 'Unknown';
        $browserVersion = '';
        $deviceType = 'Desktop';
        $architecture = 'Unknown';
        $engine = 'Unknown';
        $isBot = false;
        $deviceBrand = 'Unknown';


        if (stripos($userAgent, 'windows nt 10.0') !== false) $platform = 'Windows 10';
        elseif (stripos($userAgent, 'windows nt 6.1') !== false) $platform = 'Windows 7';
        elseif (stripos($userAgent, 'macintosh') !== false || stripos($userAgent, 'mac os x') !== false) $platform = 'Mac OS';
        elseif (stripos($userAgent, 'android') !== false) $platform = 'Android';
        elseif (stripos($userAgent, 'iphone') !== false) $platform = 'iPhone';
        elseif (stripos($userAgent, 'ipad') !== false) $platform = 'iPad';
        elseif (stripos($userAgent, 'linux') !== false) $platform = 'Linux';


        if (stripos($userAgent, 'x86_64') !== false || stripos($userAgent, 'Win64') !== false || stripos($userAgent, 'x64') !== false) {
            $architecture = '64-bit';
        } elseif (stripos($userAgent, 'i686') !== false || stripos($userAgent, 'i386') !== false) {
            $architecture = '32-bit';
        } elseif (stripos($userAgent, 'arm') !== false) {
            $architecture = 'ARM';
        }


        if (stripos($userAgent, 'AppleWebKit') !== false) $engine = 'WebKit';
        elseif (stripos($userAgent, 'Gecko') !== false && stripos($userAgent, 'like Gecko') === false) $engine = 'Gecko';
        elseif (stripos($userAgent, 'Trident') !== false) $engine = 'Trident';
        elseif (stripos($userAgent, 'Blink') !== false) $engine = 'Blink';


        if (preg_match('/Edg\/([0-9\.]+)/', $userAgent, $match)) {
            $browser = 'Edge';
            $browserVersion = $match[1];
        } elseif (preg_match('/Chrome\/([0-9\.]+)/', $userAgent, $match) && stripos($userAgent, 'Chromium') === false) {
            $browser = 'Chrome';
            $browserVersion = $match[1];
        } elseif (preg_match('/Firefox\/([0-9\.]+)/', $userAgent, $match)) {
            $browser = 'Firefox';
            $browserVersion = $match[1];
        } elseif (preg_match('/Version\/([0-9\.]+).*Safari/', $userAgent, $match)) {
            $browser = 'Safari';
            $browserVersion = $match[1];
        } elseif (preg_match('/MSIE ([0-9\.]+)/', $userAgent, $match)) {
            $browser = 'Internet Explorer';
            $browserVersion = $match[1];
        } elseif (preg_match('/OPR\/([0-9\.]+)/', $userAgent, $match)) {
            $browser = 'Opera';
            $browserVersion = $match[1];
        }


        if (stripos($userAgent, 'mobile') !== false || stripos($userAgent, 'iphone') !== false || stripos($userAgent, 'android') !== false) {
            $deviceType = 'Mobile';
        } elseif (stripos($userAgent, 'tablet') !== false || stripos($userAgent, 'ipad') !== false) {
            $deviceType = 'Tablet';
        }


        $botPatterns = ['bot', 'crawl', 'spider', 'slurp', 'mediapartners'];
        foreach ($botPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                $isBot = true;
                break;
            }
        }


        if (preg_match('/; ([^;]*?) Build\//', $userAgent, $match)) {
            $deviceBrand = trim($match[1]);
        }

        $data = [
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'platform' => $platform,
            'device_type' => $deviceType,
            'architecture' => $architecture,
            'engine' => $engine,
            'is_bot' => $isBot,
            'device_brand' => $deviceBrand,
            'user_agent' => $userAgent
        ];

        return $data;
    }
}
