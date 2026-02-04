<?php

declare(strict_types=1);

namespace ArkhamDistrict\FluentNotifications\Tests;

use ArkhamDistrict\FluentNotifications\FluentNotificationsServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FluentNotificationsServiceProvider::class,
        ];
    }
}
