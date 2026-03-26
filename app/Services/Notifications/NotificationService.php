<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function send(User $user, string $type, string $title, string $message, string $channel = 'in_app', array $payload = []): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'channel' => $channel,
            'title' => $title,
            'message' => $message,
            'payload' => $payload,
        ]);
    }
}
