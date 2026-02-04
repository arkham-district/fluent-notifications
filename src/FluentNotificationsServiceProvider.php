<?php

declare(strict_types=1);

namespace ArkhamDistrict\FluentNotifications;

use Illuminate\Support\ServiceProvider;

final class FluentNotificationsServiceProvider extends ServiceProvider
{
    /**
     * Register package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/fluent-notifications.php', 'fluent-notifications');
    }

    /**
     * Boot package services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/fluent-notifications.php' => config_path('fluent-notifications.php'),
            ], 'fluent-notifications-config');
        }
    }
}
