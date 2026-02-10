<?php

declare(strict_types=1);

namespace ArkhamDistrict\FluentNotifications;

use BadMethodCallException;

/**
 * Fluent notification builder.
 *
 * @method self success() Set a notification type to success
 * @method self error() Set a notification type to error
 * @method self warning() Set a notification type to warning
 * @method self info() Set a notification type to info
 */
final class FluentNotification
{
    private const TYPES = ['success', 'error', 'warning', 'info'];

    private string $type;

    private array $channels;

    private bool $sent = false;

    /**
     * Create a new fluent notification builder.
     *
     * @param  mixed  $notifiable  The notifiable entity
     * @param  string  $title  The notification title
     * @param  string|null  $message  Optional message body
     * @param  array<string, mixed>  $context  Context data for translation interpolation
     */
    public function __construct(
        private readonly mixed $notifiable,
        private string $title,
        private ?string $message = null,
        private array $context = [],
    ) {
        $this->type = config('fluent-notifications.default_type', 'info');
        $this->channels = config('fluent-notifications.default_channels', ['toast']);
    }

    /**
     * Handle dynamic method calls for notification types.
     *
     * @param  string  $method  The method name
     * @param  array<int, mixed>  $arguments  The method arguments
     *
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $arguments): self
    {
        if (in_array($method, self::TYPES, true)) {
            $this->type = $method;

            return $this;
        }

        throw new BadMethodCallException("Method {$method} does not exist.");
    }

    /**
     * Set the notification channels.
     *
     * @param  array<int, string>  $channels  The channels to send through
     */
    public function via(array $channels): self
    {
        $this->channels = $channels;

        return $this;
    }

    /**
     * Set the context data for translation interpolation.
     *
     * @param  array<string, mixed>  $context  Context data
     */
    public function context(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Send the notification immediately.
     */
    public function send(): void
    {
        if ($this->sent) {
            return;
        }

        $this->sent = true;

        $this->notifiable->baseNotify(
            new GenericNotification(
                type: $this->type,
                title: $this->title,
                message: $this->message,
                context: $this->context,
                channels: $this->channels,
            )
        );
    }

    /**
     * Ensure the notification is sent when the builder is destroyed.
     */
    public function __destruct()
    {
        try {
            $this->send();
        } catch (\Throwable) {
            // Silently ignore exceptions during destruction to prevent
            // fatal errors when the builder is garbage collected.
        }
    }
}
