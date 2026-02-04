<?php

declare(strict_types=1);

namespace ArkhamDistrict\FluentNotifications\Channels;

use ArkhamDistrict\FluentNotifications\GenericNotification;
use Illuminate\Notifications\Notification;

final class AlertChannel
{
    /**
     * Send the given notification as an alert via the session.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! $notification instanceof GenericNotification) {
            return;
        }

        $key = config('fluent-notifications.session.alerts', 'alerts');
        $alerts = session()->get($key, []);
        $alerts[] = $notification->toArray($notifiable);
        session()->put($key, $alerts);
    }
}
