<?php

declare(strict_types=1);

namespace ArkhamDistrict\FluentNotifications;

use ArkhamDistrict\FluentNotifications\Channels\AlertChannel;
use ArkhamDistrict\FluentNotifications\Channels\ToastChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class GenericNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private const CHANNEL_MAP = [
        'toast' => ToastChannel::class,
        'alert' => AlertChannel::class,
    ];

    /**
     * Create a new generic notification instance.
     *
     * @param  string  $type  The notification type (success, error, warning, info)
     * @param  string  $key  The notification key or message
     * @param  array<string, mixed>  $context  Context data for translation
     * @param  array<int, string>  $channels  The channels to send through
     */
    public function __construct(
        public readonly string $type,
        public readonly string $key,
        public readonly array $context = [],
        public readonly array $channels = ['toast'],
    ) {
        if (config('fluent-notifications.queue.enabled', false)) {
            $this->onConnection(config('fluent-notifications.queue.connection'));
            $this->onQueue(config('fluent-notifications.queue.queue'));
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return array_map(
            fn (string $channel) => self::CHANNEL_MAP[$channel] ?? $channel,
            $this->channels
        );
    }

    /**
     * Get the resolved notification message.
     */
    public function message(): string
    {
        if (config('fluent-notifications.translate', true)) {
            return __($this->key, $this->context);
        }

        return $this->key;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'key' => $this->key,
            'message' => $this->message(),
            'context' => $this->context,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->message())
            ->line($this->message());
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
