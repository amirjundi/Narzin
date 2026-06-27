<?php

namespace App\Http\Controllers;

use App\Trait\PushNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use PushNotification;


    public function sendPushNotification(Request $request)
    {
        $title = 'Test Notification';
        $body = 'This is a test notification';
        $data = [
            'key' => 'value',
            'key2' => 'value2',  
        ];
        $device_token = 'ipodfhjfpougwe7tdfw0egfdwei';

        $response = $this->sendNotification($title, $body, $data, $device_token);

        return response()->json($response);
    }
}
