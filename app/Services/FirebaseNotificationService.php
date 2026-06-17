<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        // Khởi tạo Firebase Cloud Messaging (FCM)
        try {
            $factory = (new Factory)
                ->withServiceAccount(storage_path('app/firebase_credentials.json'));
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Firebase Initialization Error: ' . $e->getMessage());
        }
    }

    /**
     * Gửi thông báo đẩy đến một thiết bị cụ thể.
     *
     * @param string $deviceToken (FCM Token của thiết bị)
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array|bool
     */
    public function sendPushNotification($deviceToken, $title, $body, $data = [])
    {
        if (!$this->messaging) {
            return false;
        }

        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification($notification)
                ->withData($data);

            $response = $this->messaging->send($message);
            
            Log::info("Push Notification sent successfully to: " . $deviceToken);
            return $response;
            
        } catch (\Exception $e) {
            Log::error('Firebase Send Error: ' . $e->getMessage());
            return false;
        }
    }
}
