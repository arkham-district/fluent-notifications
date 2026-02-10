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
     * @param  string|Notification  $notification  A title string or Notification instance
     * @param  string|null  $message  Optional message body (only used with string notifications)
     * @param  array<string, mixed>  $context  Context data for translation interpolation
     */
    public function notify(string|Notification $notification, ?string $message = null, array $context = []): ?FluentNotification
    {
        if ($notification instanceof Notification) {
            $this->baseNotify($notification);

            return null;
        }

        return new FluentNotification(
            notifiable: $this,
            title: $notification,
            message: $message,
            context: $context,
        );
    }
}
