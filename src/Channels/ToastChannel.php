<?php

declare(strict_types=1);

namespace ArkhamDistrict\FluentNotifications\Channels;

use ArkhamDistrict\FluentNotifications\GenericNotification;
use Illuminate\Notifications\Notification;
use Inertia\Inertia;

final class ToastChannel
{
    /**
     * Send the given notification as a toast via the session.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (!$notification instanceof GenericNotification) {
            return;
        }

        $key = config('fluent-notifications.session.toasts', 'toasts');

        $toasts = session()->get($key, []);
        $toasts[] = $notification->toArray($notifiable);

        Inertia::flash($key, $toasts);
    }
}
