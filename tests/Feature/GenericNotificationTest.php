<?php

declare(strict_types=1);

use ArkhamDistrict\FluentNotifications\Channels\AlertChannel;
use ArkhamDistrict\FluentNotifications\Channels\ToastChannel;
use ArkhamDistrict\FluentNotifications\GenericNotification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

beforeEach(function () {
    $this->notifiable = new stdClass;
});

it('returns correct channels via method', function () {
    $notification = new GenericNotification(
        type: 'info',
        key: 'test.key',
        channels: ['mail', 'database'],
    );

    expect($notification->via($this->notifiable))->toBe(['mail', 'database']);
});

it('maps custom channels to classes', function () {
    $notification = new GenericNotification(
        type: 'info',
        key: 'test.key',
        channels: ['toast', 'alert', 'mail'],
    );

    expect($notification->via($this->notifiable))->toBe([
        ToastChannel::class,
        AlertChannel::class,
        'mail',
    ]);
});

it('translates key when config enabled', function () {
    config()->set('fluent-notifications.translate', true);

    $notification = new GenericNotification(
        type: 'success',
        key: 'notifications.order_created',
        context: ['id' => 42],
    );

    // When no translation file exists, __() returns the key itself
    expect($notification->message())->toBe('notifications.order_created');
});

it('uses literal key when config disabled', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'info',
        key: 'Your order was shipped!',
    );

    expect($notification->message())->toBe('Your order was shipped!');
});

it('formats toArray correctly', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'success',
        key: 'Order created',
        context: ['id' => 1],
    );

    expect($notification->toArray($this->notifiable))->toBe([
        'type' => 'success',
        'key' => 'Order created',
        'message' => 'Order created',
        'context' => ['id' => 1],
    ]);
});

it('formats toMail correctly', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'info',
        key: 'Welcome aboard!',
    );

    $mail = $notification->toMail($this->notifiable);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe('Welcome aboard!');
});

it('formats toBroadcast correctly', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'warning',
        key: 'Low stock alert',
        context: ['product' => 'Widget'],
    );

    $broadcast = $notification->toBroadcast($this->notifiable);

    expect($broadcast)->toBeInstanceOf(BroadcastMessage::class)
        ->and($broadcast->data)->toBe([
            'type' => 'warning',
            'key' => 'Low stock alert',
            'message' => 'Low stock alert',
            'context' => ['product' => 'Widget'],
        ]);
});

it('formats toDatabase correctly', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'error',
        key: 'Payment failed',
        context: ['reason' => 'Insufficient funds'],
    );

    expect($notification->toDatabase($this->notifiable))->toBe([
        'type' => 'error',
        'key' => 'Payment failed',
        'message' => 'Payment failed',
        'context' => ['reason' => 'Insufficient funds'],
    ]);
});
