# Notification Service

> A Small Laravel Package for buck-hill pet-shop for managing order
> status events integrating with MS Teams.

# Installations

<p>On Parent Project Root, Follow these steps to integrate.</p>

```bash
composer require "celestine/notification-services @dev"
```

```bash
php artisan vendor:publish
```
then input the number associated with `Celestine\NotificationServices\NotificationServiceProvider`

update the .env file with teams webhook url;
e.g `NOTIFICATION_SERVICE_WEBHOOK=https://webhook.site/8b148898-b27e-49a9-ad64-21e592ec77b3`

you can now import the and use the event anywhere in the application.
