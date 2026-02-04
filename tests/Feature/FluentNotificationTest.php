<?php

declare(strict_types=1);

use ArkhamDistrict\FluentNotifications\Concerns\Notifiable;
use ArkhamDistrict\FluentNotifications\FluentNotification;
use ArkhamDistrict\FluentNotifications\GenericNotification;
use Illuminate\Support\Facades\Notification;

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

it('creates fluent notification with key and context', function () {
    Notification::fake();

    $builder = $this->user->notify('order.created', ['tracking' => 'ABC123']);

    expect($builder)->toBeInstanceOf(FluentNotification::class);

    $builder->send();

    Notification::assertSentTo($this->user, GenericNotification::class, function ($notification) {
        return $notification->key === 'order.created'
            && $notification->context === ['tracking' => 'ABC123'];
    });
});

it('uses default type from config', function () {
    Notification::fake();

    config()->set('fluent-notifications.default_type', 'warning');

    $builder = new FluentNotification($this->user, 'test.key');
    $builder->send();

    Notification::assertSentTo($this->user, GenericNotification::class, function ($notification) {
        return $notification->type === 'warning';
    });
});

it('uses default channels from config', function () {
    Notification::fake();

    config()->set('fluent-notifications.default_channels', ['toast', 'database']);

    $builder = new FluentNotification($this->user, 'test.key');
    $builder->send();

    Notification::assertSentTo($this->user, GenericNotification::class, function ($notification) {
        return $notification->channels === ['toast', 'database'];
    });
});

it('sets notification type via fluent methods', function (string $type) {
    Notification::fake();

    $builder = $this->user->notify('test.key')->{$type}();
    $builder->send();

    Notification::assertSentTo($this->user, GenericNotification::class, function ($notification) use ($type) {
        return $notification->type === $type;
    });
})->with(['success', 'error', 'warning', 'info']);

it('throws exception for invalid method', function () {
    $builder = $this->user->notify('test.key');

    $builder->invalidMethod();
})->throws(BadMethodCallException::class, 'Method invalidMethod does not exist.');

it('sets channels via fluent method', function () {
    Notification::fake();

    $builder = $this->user->notify('test.key')->via(['mail', 'database']);
    $builder->send();

    Notification::assertSentTo($this->user, GenericNotification::class, function ($notification) {
        return $notification->channels === ['mail', 'database'];
    });
});

it('dispatches notification on destruct', function () {
    Notification::fake();

    $builder = $this->user->notify('test.key')->success();
    unset($builder);

    Notification::assertSentTo($this->user, GenericNotification::class);
});

it('does not dispatch twice when send called manually', function () {
    Notification::fake();

    $builder = $this->user->notify('test.key');
    $builder->send();
    unset($builder);

    Notification::assertSentToTimes($this->user, GenericNotification::class, 1);
});

it('suppresses exceptions in destructor', function () {
    $failingNotifiable = new class
    {
        public function baseNotify(mixed $notification): void
        {
            throw new RuntimeException('Send failed');
        }
    };

    $builder = new FluentNotification($failingNotifiable, 'test.key');

    // Destructor should not throw even though baseNotify throws
    unset($builder);

    expect(true)->toBeTrue();
});

it('allows chaining all methods', function () {
    Notification::fake();

    $builder = $this->user->notify('order.shipped', ['id' => 1])
        ->success()
        ->via(['toast', 'mail']);

    $builder->send();

    Notification::assertSentTo($this->user, GenericNotification::class, function ($notification) {
        return $notification->type === 'success'
            && $notification->key === 'order.shipped'
            && $notification->context === ['id' => 1]
            && $notification->channels === ['toast', 'mail'];
    });
});
