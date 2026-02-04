<?php

declare(strict_types=1);

namespace ArkhamDistrict\FluentNotifications\Concerns;

use ArkhamDistrict\FluentNotifications\FluentNotification;
use Illuminate\Notifications\Notifiable as BaseNotifiable;
use Illuminate\Notifications\Notification;

trait Notifiable
{
    use BaseNotifiable {
        notify as public baseNotify;
    }

    /**
     * Send a notification or create a fluent notification builder.
     *
     * @param  string|Notification  $notification  A translation key, literal message, or Notification instance
     * @param  array<string, mixed>  $context  Context data for translation (only used with string notifications)
     */
    public function notify(string|Notification $notification, array $context = []): ?FluentNotification
    {
        if ($notification instanceof Notification) {
            $this->baseNotify($notification);

            return null;
        }

        return new FluentNotification(
            notifiable: $this,
            key: $notification,
            context: $context,
        );
    }
}
