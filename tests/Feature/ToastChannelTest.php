<?php

declare(strict_types=1);

use ArkhamDistrict\FluentNotifications\Channels\ToastChannel;
use ArkhamDistrict\FluentNotifications\GenericNotification;
use Illuminate\Notifications\Notification;
use Inertia\Inertia;

beforeEach(function () {
    ToastChannel::flush();

    $this->channel = new ToastChannel;
    $this->notifiable = new stdClass;

    Inertia::spy();
});

it('flashes notification via Inertia', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'success',
        title: 'Item saved!',
        message: 'Your changes have been saved.',
    );

    $this->channel->send($this->notifiable, $notification);

    Inertia::shouldHaveReceived('flash')
        ->with('toasts', [[
            'type' => 'success',
            'title' => 'Item saved!',
            'message' => 'Your changes have been saved.',
            'context' => [],
        ]]);
});

it('flashes title-only notification', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'info',
        title: 'Settings saved',
    );

    $this->channel->send($this->notifiable, $notification);

    Inertia::shouldHaveReceived('flash')
        ->with('toasts', [[
            'type' => 'info',
            'title' => 'Settings saved',
            'message' => null,
            'context' => [],
        ]]);
});

it('uses configured flash key', function () {
    config()->set('fluent-notifications.translate', false);
    config()->set('fluent-notifications.flash.toasts', 'flash_toasts');

    $notification = new GenericNotification(
        type: 'info',
        title: 'Hello',
    );

    $this->channel->send($this->notifiable, $notification);

    Inertia::shouldHaveReceived('flash')
        ->with('flash_toasts', Mockery::any());
});

it('appends to existing toasts within same request', function () {
    config()->set('fluent-notifications.translate', false);

    $first = new GenericNotification(
        type: 'info',
        title: 'First',
    );

    $second = new GenericNotification(
        type: 'success',
        title: 'Second',
    );

    $this->channel->send($this->notifiable, $first);
    $this->channel->send($this->notifiable, $second);

    Inertia::shouldHaveReceived('flash')
        ->with('toasts', Mockery::on(function (array $toasts) {
            return count($toasts) === 2
                && $toasts[0]['title'] === 'First'
                && $toasts[1]['title'] === 'Second';
        }));
});

it('ignores non-generic notifications', function () {
    $notification = new class extends Notification
    {
        public function via(object $notifiable): array
        {
            return ['database'];
        }
    };

    $this->channel->send($this->notifiable, $notification);

    Inertia::shouldNotHaveReceived('flash');
});
