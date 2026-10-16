# UIU Social Media - Session Update

This update adds:

1. Secure PHP session creation on successful login.
2. `last_login` timestamp in the `users` table.
3. `last_activity` timestamp updated every 30 seconds.
4. Dynamic current-user name/account type in the header and left sidebar.
5. Dynamic "ONLINE NOW" list.
6. Logout that clears the session and activity.
7. PHP session protection for `home.php`.
8. Session regeneration after login.

## Installation

### Step 1 — Database
Import your existing `uiu_social_media` database first.

Then run `session_migration.sql` once in phpMyAdmin.

It adds:
- `users.last_login`
- `users.last_activity`

### Step 2 — Replace/add files
Put these files in the same folder as your existing PHP/HTML/JS files:

- login.php
- session_api.php
- heartbeat.php
- online_users.php
- logout.php
- auth_guard.php
- home.php
- script.js
- header.html
- left-sidebar.html
- right-sidebar.html
- db.php
- session_migration.sql

### Step 3 — Important
Pages that should only be accessible after login must be PHP pages and should start with:

<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}
?>

For pages such as groups/events/messages/notifications, rename them from `.html` to `.php` and add the same guard at the top.

Then update navigation links accordingly.

## How online status works

A user is "online" when their browser sends a heartbeat within the last 2 minutes.

The browser sends a heartbeat every 30 seconds.

This is better than only storing `is_online = 1`, because a browser can close/crash without running logout.

`last_login` tells you when the user most recently logged in.
`last_activity` tells you their most recent activity heartbeat.
