# Fluent Notifications for Laravel

[![Tests](https://github.com/arkham-district/fluent-notifications/actions/workflows/tests.yml/badge.svg)](https://github.com/arkham-district/fluent-notifications/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/arkham-district/fluent-notifications.svg)](https://packagist.org/packages/arkham-district/fluent-notifications)
[![License](https://img.shields.io/packagist/l/arkham-district/fluent-notifications.svg)](https://packagist.org/packages/arkham-district/fluent-notifications)

A simplified fluent API for Laravel notifications with multi-channel support. Send notifications to multiple channels (toast, alert, mail, database, broadcast) with a single, expressive call.

## Requirements

- PHP ^8.2
- Laravel ^11.0 or ^12.0

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
// Send a toast notification using a translation key
$user->notify('order.created', ['tracking' => $code])
    ->success()
    ->via(['toast']);

// Send a literal string (no translation)
$user->notify('Your order was shipped!')
    ->info()
    ->via(['toast']);
```

### Notification Types

```php
$user->notify('...')
    ->success()   // Green / success style
    ->error()     // Red / error style
    ->warning()   // Yellow / warning style
    ->info();     // Blue / info style (default)
```

### Multiple Channels

```php
$user->notify('order.created', ['id' => $order->id])
    ->success()
    ->via(['toast', 'mail', 'database']);
```

### Available Channels

| Channel     | Description                        |
|-------------|------------------------------------|
| `toast`     | Session flash for frontend toasts  |
| `alert`     | Session flash for persistent alerts|
| `mail`      | Laravel Mail (native)              |
| `database`  | Laravel Database (native)          |
| `broadcast` | Laravel Broadcast (native)         |

### Backward Compatibility

Standard Laravel notifications still work as expected:

```php
$user->notify(new CustomNotification());
```

### Auto-Send on Destruct

The notification is automatically sent when the builder goes out of scope. You don't need to call `->send()` explicitly:

```php
// This works - notification is sent when $builder goes out of scope
$user->notify('order.created')->success()->via(['toast']);
```

You can also call `->send()` explicitly if you prefer:

```php
$user->notify('order.created')->success()->via(['toast'])->send();
```

## Configuration

```php
// config/fluent-notifications.php

return [
    // Pass notification keys through __() for translation
    'translate' => true,

    // Default channels when via() is not called
    'default_channels' => ['toast'],

    // Default notification type
    'default_type' => 'info',

    // Session keys for toast and alert channels
    'session' => [
        'toasts' => 'toasts',
        'alerts' => 'alerts',
    ],

    // Queue configuration
    'queue' => [
        'enabled' => false,
        'connection' => null,
        'queue' => null,
    ],
];
```

## Frontend Integration

### Reading Toasts from Session

Toasts are stored in the session under the configured key (default: `toasts`). Each toast has this structure:

```json
{
    "type": "success",
    "key": "order.created",
    "message": "Your order has been created!",
    "context": {"tracking": "ABC123"}
}
```

### Vue.js / Inertia

```php
// HandleInertiaRequests middleware
public function share(Request $request): array
{
    return [
        'toasts' => session()->pull('toasts', []),
        'alerts' => session()->pull('alerts', []),
    ];
}
```

```vue
<script setup>
import { usePage } from '@inertiajs/vue3'
import { watch } from 'vue'

const page = usePage()

watch(() => page.props.toasts, (toasts) => {
    toasts.forEach(toast => {
        // Show toast using your preferred toast library
        showToast(toast.message, toast.type)
    })
})
</script>
```

### Livewire

```php
// In your Livewire component
public function save()
{
    // ... save logic

    auth()->user()->notify('item.saved')->success()->via(['toast']);

    // Read toasts from session and dispatch browser event
    $toasts = session()->pull('toasts', []);
    foreach ($toasts as $toast) {
        $this->dispatch('toast', ...$toast);
    }
}
```

### React / Inertia

```jsx
import { usePage } from '@inertiajs/react'
import { useEffect } from 'react'

export default function Layout({ children }) {
    const { toasts } = usePage().props

    useEffect(() => {
        toasts?.forEach(toast => {
            showToast(toast.message, toast.type)
        })
    }, [toasts])

    return <>{children}</>
}
```

### Blade

```php
@if(session('toasts'))
    @foreach(session()->pull('toasts') as $toast)
        <div class="toast toast-{{ $toast['type'] }}">
            {{ $toast['message'] }}
        </div>
    @endforeach
@endif
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
$user->notify('deploy.complete')
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
