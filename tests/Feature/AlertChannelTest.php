<?php

declare(strict_types=1);

use ArkhamDistrict\FluentNotifications\Channels\AlertChannel;
use ArkhamDistrict\FluentNotifications\GenericNotification;
use Illuminate\Notifications\Notification;

beforeEach(function () {
    $this->channel = new AlertChannel;
    $this->notifiable = new stdClass;
});

it('stores notification in session', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'error',
        key: 'Something went wrong!',
    );

    $this->channel->send($this->notifiable, $notification);

    $alerts = session()->get('alerts');

    expect($alerts)->toHaveCount(1)
        ->and($alerts[0])->toBe([
            'type' => 'error',
            'key' => 'Something went wrong!',
            'message' => 'Something went wrong!',
            'context' => [],
        ]);
});

it('uses configured session key', function () {
    config()->set('fluent-notifications.translate', false);
    config()->set('fluent-notifications.session.alerts', 'flash_alerts');

    $notification = new GenericNotification(
        type: 'warning',
        key: 'Attention',
    );

    $this->channel->send($this->notifiable, $notification);

    expect(session()->get('flash_alerts'))->toHaveCount(1)
        ->and(session()->get('alerts'))->toBeNull();
});

it('appends to existing alerts', function () {
    config()->set('fluent-notifications.translate', false);

    session()->put('alerts', [
        ['type' => 'info', 'key' => 'First', 'message' => 'First', 'context' => []],
    ]);

    $notification = new GenericNotification(
        type: 'error',
        key: 'Second',
    );

    $this->channel->send($this->notifiable, $notification);

    $alerts = session()->get('alerts');

    expect($alerts)->toHaveCount(2)
        ->and($alerts[0]['key'])->toBe('First')
        ->and($alerts[1]['key'])->toBe('Second');
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

    expect(session()->get('alerts'))->toBeNull();
});
