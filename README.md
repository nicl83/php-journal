# php-journal

## What is this?
An attempt to write a simple web-based journal in PHP.
The front-end is pure HTML with no JS, with the logic being handled server-side.
Posts and user metadata are stored encrypted at rest using `Defuse\Crypto`. 

## What does it require?
Typical LAMP stack - Linux of your preference (developed on Debian), Apache2, MySQL, PHP. Latest versions as of March 5th, 2023.

## What can it currently do?
- Register a new user to a "pending_users" table.
- Login using a user on a "users" table.
- Create new posts.
- View existing posts.

## What can't it currently do that I want it to do?
- Admin console to move users from pending to main (e.g user activation)
- Edit posts
- Delete posts

## What have other people asked for?
- Per-entry random keys, for when you want to scream into the void and then never look at it again

## Anything else?
- Styling needs a MASSIVE tidy-up. It's currently raw HTML with no CSS, and the layout is iffy at best.
- Removing the hardcoded credentials is probably a good idea.

## How do I use it?
- Set up LAMP stack
- Import schema from included file
- Copy PHP files + composer.json to your web dir of choice
- Run composer update command
- Go to index.php
- ???
- Profit

In current lieu of an admin panel, to activate an account, copy the data in a row of pending-users to users. It will then work for login.
