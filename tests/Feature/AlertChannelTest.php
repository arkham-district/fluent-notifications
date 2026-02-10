<?php

declare(strict_types=1);

use ArkhamDistrict\FluentNotifications\Channels\AlertChannel;
use ArkhamDistrict\FluentNotifications\GenericNotification;
use Illuminate\Notifications\Notification;
use Inertia\Inertia;

beforeEach(function () {
    AlertChannel::flush();

    $this->channel = new AlertChannel;
    $this->notifiable = new stdClass;

    Inertia::spy();
});

it('flashes notification via Inertia', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'error',
        title: 'Something went wrong!',
        message: 'Please try again later.',
    );

    $this->channel->send($this->notifiable, $notification);

    Inertia::shouldHaveReceived('flash')
        ->with('alerts', [[
            'type' => 'error',
            'title' => 'Something went wrong!',
            'message' => 'Please try again later.',
            'context' => [],
        ]]);
});

it('flashes title-only alert', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'warning',
        title: 'Attention',
    );

    $this->channel->send($this->notifiable, $notification);

    Inertia::shouldHaveReceived('flash')
        ->with('alerts', [[
            'type' => 'warning',
            'title' => 'Attention',
            'message' => null,
            'context' => [],
        ]]);
});

it('uses configured flash key', function () {
    config()->set('fluent-notifications.translate', false);
    config()->set('fluent-notifications.flash.alerts', 'flash_alerts');

    $notification = new GenericNotification(
        type: 'warning',
        title: 'Attention',
    );

    $this->channel->send($this->notifiable, $notification);

    Inertia::shouldHaveReceived('flash')
        ->with('flash_alerts', Mockery::any());
});

it('appends to existing alerts within same request', function () {
    config()->set('fluent-notifications.translate', false);

    $first = new GenericNotification(
        type: 'info',
        title: 'First',
    );

    $second = new GenericNotification(
        type: 'error',
        title: 'Second',
    );

    $this->channel->send($this->notifiable, $first);
    $this->channel->send($this->notifiable, $second);

    Inertia::shouldHaveReceived('flash')
        ->with('alerts', Mockery::on(function (array $alerts) {
            return count($alerts) === 2
                && $alerts[0]['title'] === 'First'
                && $alerts[1]['title'] === 'Second';
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
