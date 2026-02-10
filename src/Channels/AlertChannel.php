<?php

declare(strict_types=1);

namespace ArkhamDistrict\FluentNotifications\Channels;

use ArkhamDistrict\FluentNotifications\GenericNotification;
use Illuminate\Notifications\Notification;
use Inertia\Inertia;

final class AlertChannel
{
    private static array $pending = [];

    /**
     * Send the given notification as an alert via Inertia flash.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! $notification instanceof GenericNotification) {
            return;
        }

        $key = config('fluent-notifications.flash.alerts', 'alerts');

        self::$pending[$key][] = $notification->toArray($notifiable);

        Inertia::flash($key, self::$pending[$key]);
    }

    /**
     * Reset pending notifications (useful for testing).
     */
    public static function flush(): void
    {
        self::$pending = [];
    }
}
