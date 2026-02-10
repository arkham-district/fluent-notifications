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

// --- Title + Message ---

it('creates notification with title only', function () {
    Notification::fake();

    $this->user->notify('Profile Updated')->success()->send();

    Notification::assertSentTo($this->user, GenericNotification::class, function ($notification) {
        return $notification->title === 'Profile Updated'
            && $notification->message === null;
    });
});

it('creates notification with title and message', function () {
    Notification::fake();

    $this->user->notify('Profile Updated', 'Your changes have been saved.')->success()->send();

    Notification::assertSentTo($this->user, GenericNotification::class, function ($notification) {
        return $notification->title === 'Profile Updated'
            && $notification->message === 'Your changes have been saved.';
    });
});

it('creates notification with title, message and context', function () {
    Notification::fake();

    $this->user->notify('Order Shipped', 'Tracking: :code', ['code' => 'ABC123'])->send();

    Notification::assertSentTo($this->user, GenericNotification::class, function ($notification) {
        return $notification->title === 'Order Shipped'
            && $notification->message === 'Tracking: :code'
            && $notification->context === ['code' => 'ABC123'];
    });
});

// --- Context via fluent method ---

it('sets context via fluent method', function () {
    Notification::fake();

    $this->user->notify('Order Shipped', 'Tracking: :code')
        ->context(['code' => 'XYZ'])
        ->send();

    Notification::assertSentTo($this->user, GenericNotification::class, function ($notification) {
        return $notification->context === ['code' => 'XYZ'];
    });
});

// --- Type ---

it('uses default type from config', function () {
    Notification::fake();

    config()->set('fluent-notifications.default_type', 'warning');

    $builder = new FluentNotification($this->user, 'Test');
    $builder->send();

    Notification::assertSentTo($this->user, GenericNotification::class, function ($notification) {
        return $notification->type === 'warning';
    });
});

it('sets notification type via fluent methods', function (string $type) {
    Notification::fake();

    $this->user->notify('Test')->{$type}()->send();

    Notification::assertSentTo($this->user, GenericNotification::class, function ($notification) use ($type) {
        return $notification->type === $type;
    });
})->with(['success', 'error', 'warning', 'info']);

it('throws exception for invalid method', function () {
    $this->user->notify('Test')->invalidMethod();
})->throws(BadMethodCallException::class, 'Method invalidMethod does not exist.');

// --- Channels ---

it('uses default channels from config', function () {
    Notification::fake();

    config()->set('fluent-notifications.default_channels', ['toast', 'database']);

    $builder = new FluentNotification($this->user, 'Test');
    $builder->send();

    Notification::assertSentTo($this->user, GenericNotification::class, function ($notification) {
        return $notification->channels === ['toast', 'database'];
    });
});

it('sets channels via fluent method', function () {
    Notification::fake();

    $this->user->notify('Test')->via(['mail', 'database'])->send();

    Notification::assertSentTo($this->user, GenericNotification::class, function ($notification) {
        return $notification->channels === ['mail', 'database'];
    });
});

// --- Auto-Send ---

it('dispatches notification on destruct', function () {
    Notification::fake();

    $builder = $this->user->notify('Test')->success();
    unset($builder);

    Notification::assertSentTo($this->user, GenericNotification::class);
});

it('does not dispatch twice when send called manually', function () {
    Notification::fake();

    $builder = $this->user->notify('Test');
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

    $builder = new FluentNotification($failingNotifiable, 'Test');

    unset($builder);

    expect(true)->toBeTrue();
});

// --- Full Chain ---

it('allows chaining all methods', function () {
    Notification::fake();

    $this->user->notify('Order Shipped', 'Your order :id is on the way.', ['id' => 1])
        ->success()
        ->via(['toast', 'mail'])
        ->send();

    Notification::assertSentTo($this->user, GenericNotification::class, function ($notification) {
        return $notification->type === 'success'
            && $notification->title === 'Order Shipped'
            && $notification->message === 'Your order :id is on the way.'
            && $notification->context === ['id' => 1]
            && $notification->channels === ['toast', 'mail'];
    });
});
