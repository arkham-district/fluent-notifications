<?php

declare(strict_types=1);

use ArkhamDistrict\FluentNotifications\Channels\ToastChannel;
use ArkhamDistrict\FluentNotifications\GenericNotification;
use Illuminate\Notifications\Notification;

beforeEach(function () {
    $this->channel = new ToastChannel;
    $this->notifiable = new stdClass;
});

it('stores notification in session', function () {
    config()->set('fluent-notifications.translate', false);

    $notification = new GenericNotification(
        type: 'success',
        key: 'Item saved!',
    );

    $this->channel->send($this->notifiable, $notification);

    $toasts = session()->get('toasts');

    expect($toasts)->toHaveCount(1)
        ->and($toasts[0])->toBe([
            'type' => 'success',
            'key' => 'Item saved!',
            'message' => 'Item saved!',
            'context' => [],
        ]);
});

it('uses configured session key', function () {
    config()->set('fluent-notifications.translate', false);
    config()->set('fluent-notifications.session.toasts', 'flash_toasts');

    $notification = new GenericNotification(
        type: 'info',
        key: 'Hello',
    );

    $this->channel->send($this->notifiable, $notification);

    expect(session()->get('flash_toasts'))->toHaveCount(1)
        ->and(session()->get('toasts'))->toBeNull();
});

it('appends to existing toasts', function () {
    config()->set('fluent-notifications.translate', false);

    session()->put('toasts', [
        ['type' => 'info', 'key' => 'First', 'message' => 'First', 'context' => []],
    ]);

    $notification = new GenericNotification(
        type: 'success',
        key: 'Second',
    );

    $this->channel->send($this->notifiable, $notification);

    $toasts = session()->get('toasts');

    expect($toasts)->toHaveCount(2)
        ->and($toasts[0]['key'])->toBe('First')
        ->and($toasts[1]['key'])->toBe('Second');
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

    expect(session()->get('toasts'))->toBeNull();
});
