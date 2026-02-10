<?php

declare(strict_types=1);

it('has default translate value', function () {
    expect(config('fluent-notifications.translate'))->toBeTrue();
});

it('has default channels value', function () {
    expect(config('fluent-notifications.default_channels'))->toBe(['toast']);
});

it('has default type value', function () {
    expect(config('fluent-notifications.default_type'))->toBe('info');
});

it('has session keys configured', function () {
    expect(config('fluent-notifications.flash.toasts'))->toBe('toasts')
        ->and(config('fluent-notifications.flash.alerts'))->toBe('alerts');
});

it('has queue configuration', function () {
    expect(config('fluent-notifications.queue.enabled'))->toBeFalse()
        ->and(config('fluent-notifications.queue.connection'))->toBeNull()
        ->and(config('fluent-notifications.queue.queue'))->toBeNull();
});
