# How enable web notifications on Admin

Run maker
```bash
php bin/console make:admin:notification
```

Customize generated code as you want, then create your first notification :
```php 
$notification = new AdminNotification();
$notification->title = 'Hello';

$em->persist($notification);
$em->flush();
```