# Usage
This package allows you to sync your remote database to your local db for local dev purposes when trying to use live data.

# Set Up
Add the following environment variables to your .env file ( LOCAL ONLY )
```
REMOTE_SYNC_URL=
REMOTE_SYNC_DB_NAME=
REMOTE_SYNC_SSH_USERNAME=
REMOTE_SYNC_SSH_PASSWORD=
```

## Artisan Command
```shell
php aritsan mwi:db:sync
```