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

// --- Channel Mapping ---

it('returns correct channels via method', function () {
    $notification = new GenericNotification(
        type: 'info',
        title: 'Test',
        channels: ['mail', 'database'],
    );

    expect($notification->via($this->notifiable))->toBe(['mail', 'database']);
});

it('maps custom channels to classes', function () {
    $notification = new GenericNotification(
        type: 'info',
        title: 'Test',
        channels: ['toast', 'alert', 'mail'],
    );

    expect($notification->via($this->notifiable))->toBe([
        ToastChannel::class,
        AlertChannel::class,
        'mail',
    ]);
});

// --- Title + Message ---

it('accepts title only', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'success',
        title: 'Profile Updated',
    );

    expect($notification->toArray($this->notifiable))->toBe([
        'type' => 'success',
        'title' => 'Profile Updated',
        'message' => null,
        'context' => [],
    ]);
});

it('accepts title and message', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'success',
        title: 'Profile Updated',
        message: 'Your changes have been saved.',
    );

    expect($notification->toArray($this->notifiable))->toBe([
        'type' => 'success',
        'title' => 'Profile Updated',
        'message' => 'Your changes have been saved.',
        'context' => [],
    ]);
});

it('accepts title, message and context', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'info',
        title: 'Order Shipped',
        message: 'Tracking: :code',
        context: ['code' => 'ABC123'],
    );

    expect($notification->toArray($this->notifiable))->toBe([
        'type' => 'info',
        'title' => 'Order Shipped',
        'message' => 'Tracking: :code',
        'context' => ['code' => 'ABC123'],
    ]);
});

// --- Translation ---

it('translates title and message when config enabled', function () {
    config()->set('fluent-notifications.translate', true);

    $notification = new GenericNotification(
        type: 'success',
        title: 'notifications.title',
        message: 'notifications.body',
        context: ['id' => 42],
    );

    // When no translation file exists, __() returns the key itself
    $data = $notification->toArray($this->notifiable);

    expect($data['title'])->toBe('notifications.title')
        ->and($data['message'])->toBe('notifications.body');
});

it('uses literal strings when translation disabled', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'info',
        title: 'Your order was shipped!',
        message: 'Check your email for details.',
    );

    $data = $notification->toArray($this->notifiable);

    expect($data['title'])->toBe('Your order was shipped!')
        ->and($data['message'])->toBe('Check your email for details.');
});

it('handles null message with translation enabled', function () {
    config()->set('fluent-notifications.translate', true);

    $notification = new GenericNotification(
        type: 'info',
        title: 'notifications.saved',
    );

    $data = $notification->toArray($this->notifiable);

    expect($data['message'])->toBeNull();
});

// --- Mail ---

it('formats toMail with title as subject', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'info',
        title: 'Welcome aboard!',
        message: 'We are glad to have you.',
    );

    $mail = $notification->toMail($this->notifiable);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe('Welcome aboard!');
});

// --- Broadcast ---

it('formats toBroadcast correctly', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'warning',
        title: 'Low stock alert',
        message: 'Product :product is running low.',
        context: ['product' => 'Widget'],
    );

    $broadcast = $notification->toBroadcast($this->notifiable);

    expect($broadcast)->toBeInstanceOf(BroadcastMessage::class)
        ->and($broadcast->data)->toBe([
            'type' => 'warning',
            'title' => 'Low stock alert',
            'message' => 'Product :product is running low.',
            'context' => ['product' => 'Widget'],
        ]);
});

// --- Database ---

it('formats toDatabase correctly', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'error',
        title: 'Payment failed',
        message: 'Reason: :reason',
        context: ['reason' => 'Insufficient funds'],
    );

    expect($notification->toDatabase($this->notifiable))->toBe([
        'type' => 'error',
        'title' => 'Payment failed',
        'message' => 'Reason: :reason',
        'context' => ['reason' => 'Insufficient funds'],
    ]);
});
