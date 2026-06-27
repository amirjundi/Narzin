<?php

namespace App\Trait;

use Google\Auth\ApplicationDefaultCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait PushNotification
{
    public function sendNotification($title, $body, $data, $device_token)
    {
        $fcmUrl = 'https://fcm.googleapis.com/v1/projects/narzin-notification/messages:send';
    
        $notification = [
            "message" => [
                "token" => $device_token,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ],
                "data" => $data, // Optional custom data
            ]
        ];
    
        try {
            $response = Http::withHeaders([
                "Authorization" => "Bearer " . $this->getAccessToken(),
                'Content-Type' => 'application/json',
            ])->post($fcmUrl, $notification);
    
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Push notification failed', [
                'error' => $e->getMessage(),
                'device_token' => $device_token,
            ]);
            return false;
        }
    }


    private function getAccessToken(){
        $keyPath = config('services.firebase.key_path');
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $keyPath);
        $scopes = [
            'https://www.googleapis.com/auth/cloud-platform',
            'https://www.googleapis.com/auth/firebase.messaging',
        ];

        $credentials = ApplicationDefaultCredentials::getCredentials($scopes);
        $token = $credentials->fetchAuthToken();

        return $token['access_token'] ?? null;
    }
}
