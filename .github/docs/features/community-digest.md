# Community digest

A weekly email summary of recent community activity.

[← Back to features](README.md)

## Schedule

Sent every **Saturday at 9:00** (app timezone) via the application scheduler (`digest:send`). Hosts must run `php artisan schedule:run` every minute — see the [hosting guide](../hosting-guide.md#step-8-configure-cron-scheduler).

## Content

Each digest covers activity from the past seven days:

- New public events
- Updated volunteer events the member is signed up for
- New projects needing volunteers
- Updated volunteer projects the member is signed up for

## Opt-out

Members can opt out from **Settings → Notifications**. Each email includes a signed unsubscribe link.

## Requirements

Requires a configured mail driver (`MAIL_*` in `.env`) and a running cron entry for Laravel’s scheduler. See [Configure cron (scheduler)](../hosting-guide.md#step-8-configure-cron-scheduler) in the hosting guide.
