# Fluent Notifications for Laravel

[![Tests](https://github.com/arkham-district/fluent-notifications/actions/workflows/tests.yml/badge.svg)](https://github.com/arkham-district/fluent-notifications/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/arkham-district/fluent-notifications.svg)](https://packagist.org/packages/arkham-district/fluent-notifications)
[![License](https://img.shields.io/packagist/l/arkham-district/fluent-notifications.svg)](https://packagist.org/packages/arkham-district/fluent-notifications)

A simplified fluent API for Laravel notifications with multi-channel support and Inertia.js integration. Send notifications to multiple channels (toast, alert, mail, database, broadcast) with a single, expressive call.

## Requirements

- PHP ^8.2
- Laravel ^11.0 or ^12.0
- Inertia.js (via `inertiajs/inertia-laravel` ^2.0)

## Installation

```bash
composer require arkham-district/fluent-notifications
```

Publish the configuration file (optional):

```bash
php artisan vendor:publish --tag=fluent-notifications-config
```

## Setup

Replace Laravel's `Notifiable` trait with the one provided by this package in your User model (or any notifiable model):

```php
<?php

namespace App\Models;

use ArkhamDistrict\FluentNotifications\Concerns\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
}
```

## Usage

### Basic Usage

```php
// Title only
$user->notify('Profile Updated')->success()->via(['toast']);

// Title + message
$user->notify('Profile Updated', 'Your changes have been saved.')->success()->via(['toast']);

// Title + message + inline context
$user->notify('Order Shipped', 'Tracking: :code', ['code' => 'ABC123'])->success()->via(['toast']);

// Context via fluent method
$user->notify('Order Shipped', 'Tracking: :code')
    ->context(['code' => 'ABC123'])
    ->success()
    ->send();
```

### Notification Types

```php
$user->notify('Something happened')
    ->success()   // Green / success style
    ->error()     // Red / error style
    ->warning()   // Yellow / warning style
    ->info();     // Blue / info style (default)
```

### Multiple Channels

```php
$user->notify('Order Created', 'Your order #:id has been placed.', ['id' => $order->id])
    ->success()
    ->via(['toast', 'mail', 'database']);
```

### Available Channels

| Channel     | Description                              |
|-------------|------------------------------------------|
| `toast`     | Inertia flash for frontend toasts        |
| `alert`     | Inertia flash for persistent alerts      |
| `mail`      | Laravel Mail (native)                    |
| `database`  | Laravel Database (native)                |
| `broadcast` | Laravel Broadcast (native)               |

### Backward Compatibility

Standard Laravel notifications still work as expected:

```php
$user->notify(new CustomNotification());
```

### Auto-Send on Destruct

The notification is automatically sent when the builder goes out of scope. You don't need to call `->send()` explicitly:

```php
// This works - notification is sent when the builder goes out of scope
$user->notify('Profile Updated')->success()->via(['toast']);
```

You can also call `->send()` explicitly if you prefer:

```php
$user->notify('Profile Updated')->success()->via(['toast'])->send();
```

## Configuration

```php
// config/fluent-notifications.php

return [
    // Pass notification title/message through __() for translation
    'translate' => true,

    // Default channels when via() is not called
    'default_channels' => ['toast'],

    // Default notification type
    'default_type' => 'info',

    // Inertia flash keys for toast and alert channels
    'flash' => [
        'toasts' => 'toasts',
        'alerts' => 'alerts',
    ],
];
```

## Frontend Integration

### How It Works

Toast and alert channels use `Inertia::flash()` to share data with the frontend. The notification payload is automatically available in your Inertia page props.

Each notification has this structure:

```json
{
    "type": "success",
    "title": "Profile Updated",
    "message": "Your changes have been saved.",
    "context": {}
}
```

### Vue.js / Inertia

```vue
<script setup>
import { usePage } from '@inertiajs/vue3'
import { watch } from 'vue'

const page = usePage()

watch(() => page.props.toasts, (toasts) => {
    toasts?.forEach(toast => {
        showToast(toast.title, toast.message, toast.type)
    })
})
</script>
```

### React / Inertia

```jsx
import { usePage } from '@inertiajs/react'
import { useEffect } from 'react'

export default function Layout({ children }) {
    const { toasts } = usePage().props

    useEffect(() => {
        toasts?.forEach(toast => {
            showToast(toast.title, toast.message, toast.type)
        })
    }, [toasts])

    return <>{children}</>
}
```

## Creating Custom Channels

You can create your own notification channels by implementing a class with a `send` method:

```php
<?php

namespace App\Channels;

use ArkhamDistrict\FluentNotifications\GenericNotification;
use Illuminate\Notifications\Notification;

class SlackChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! $notification instanceof GenericNotification) {
            return;
        }

        // Send to Slack using $notification->toArray($notifiable)
    }
}
```

Then use the fully qualified class name:

```php
$user->notify('Deploy Complete', 'Version :version is now live.', ['version' => '2.1.0'])
    ->success()
    ->via([App\Channels\SlackChannel::class]);
```

## Testing

```bash
composer test
```

With coverage:

```bash
composer test-coverage
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Write tests for your changes
4. Ensure all tests pass (`composer test`)
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
