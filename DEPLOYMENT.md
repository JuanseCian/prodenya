# Prodenya Deployment Checklist

This checklist targets the current Laravel application on shared hosting such as Hostinger.

## Production Environment

Set these values in the production `.env` file. Do not commit the file.

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example

DB_CONNECTION=mysql
SESSION_DRIVER=database
CACHE_STORE=database
FILESYSTEM_DISK=local
```

The application already stores avatars on the `public` disk. Run `php artisan storage:link` after deployment so those files are reachable.

## Queue Strategy

Team invitation notifications implement `ShouldQueue`. Choose one strategy and verify it before accepting real invitations:

- Use `QUEUE_CONNECTION=sync` when the hosting plan cannot run a worker. Mail is sent during the request.
- Use `QUEUE_CONNECTION=database` only when a cron job runs `php artisan queue:work --stop-when-empty --max-time=50` every minute.

Do not leave database-backed jobs enabled without a worker. Invitations will remain in the `jobs` table and users will not receive email.

## Release Commands

Run from the application root, using the production PHP binary when the host requires a custom path.

```sh
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm ci
npm run build
```

If Node is not available on the server, run `npm ci && npm run build` in CI or locally and upload the generated `public/build` directory with the release.

## Scheduler

The current scheduler removes expired team invitations daily. Configure one cron entry:

```cron
* * * * * cd /path/to/prodenya && php artisan schedule:run >> /dev/null 2>&1
```

## Backups

Before migrations or a release, create both backups:

- A database dump from the hosting panel or MySQL tooling.
- A copy of `storage/app/public`, including uploaded avatars and documents.

Keep the backup outside the public web root and verify that at least one restore has been tested.

## Rollback

If a release must be reverted:

1. Put the application in maintenance mode if the hosting plan supports it.
2. Restore the previous code release and its `public/build` directory.
3. Restore the database backup only when the release applied incompatible migrations or data changes.
4. Run `php artisan optimize:clear`, then rebuild the production caches.
5. Verify `GET /up`, authentication, uploads, invitations and queue behavior before reopening traffic.

Do not roll back application code across a migration unless the database compatibility has been confirmed.

## Health Check

Laravel exposes `GET /up` as the health endpoint. Add it to uptime monitoring and verify these checks after deployment:

- Login and logout.
- A role-restricted route as an administrator and as a professional.
- Avatar upload and retrieval.
- Team invitation send, accept and decline.
- Queue behavior according to the selected strategy.
- Scheduler execution and expired-invitation cleanup.

## Release Gate

The release is not ready until these commands pass in the release environment:

```sh
php artisan test
npm run lint:check
npm run types:check
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
