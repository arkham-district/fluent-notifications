<?php

declare(strict_types=1);

use ArkhamDistrict\FluentNotifications\Concerns\Notifiable;
use ArkhamDistrict\FluentNotifications\FluentNotification;
use ArkhamDistrict\FluentNotifications\GenericNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

beforeEach(function () {
    $this->user = new class
    {
        use Notifiable;

        public function getKey(): int
        {
            return 1;
        }
    };
});

it('returns fluent notification when passing string', function () {
    NotificationFacade::fake();

    $result = $this->user->notify('order.created');

    expect($result)->toBeInstanceOf(FluentNotification::class);
});

it('dispatches laravel notification when passing object', function () {
    NotificationFacade::fake();

    $notification = new class extends Notification
    {
        public function via(object $notifiable): array
        {
            return ['database'];
        }

        public function toArray(object $notifiable): array
        {
            return ['test' => true];
        }
    };

    $result = $this->user->notify($notification);

    expect($result)->toBeNull();

    NotificationFacade::assertSentTo($this->user, $notification::class);
});

it('works with fluent chain', function () {
    NotificationFacade::fake();

    $this->user->notify('test.key')
        ->success()
        ->via(['toast'])
        ->send();

    NotificationFacade::assertSentTo($this->user, GenericNotification::class, function ($notification) {
        return $notification->type === 'success'
            && $notification->channels === ['toast'];
    });
});

it('passes context to notification', function () {
    NotificationFacade::fake();

    $this->user->notify('order.shipped', ['tracking' => 'XYZ'])->send();

    NotificationFacade::assertSentTo($this->user, GenericNotification::class, function ($notification) {
        return $notification->context === ['tracking' => 'XYZ'];
    });
});
