<?php

declare(strict_types=1);

namespace ArkhamDistrict\FluentNotifications\Channels;

use ArkhamDistrict\FluentNotifications\GenericNotification;
use Illuminate\Notifications\Notification;
use Inertia\Inertia;

final class ToastChannel
{
    private static array $pending = [];

    /**
     * Send the given notification as a toast via Inertia flash.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! $notification instanceof GenericNotification) {
            return;
        }

        $key = config('fluent-notifications.flash.toasts', 'toasts');

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
