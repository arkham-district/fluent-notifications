<?php

declare(strict_types=1);

namespace ArkhamDistrict\FluentNotifications\Tests;

use ArkhamDistrict\FluentNotifications\FluentNotificationsServiceProvider;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            InertiaServiceProvider::class,
            FluentNotificationsServiceProvider::class,
        ];
    }
}
