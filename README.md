# TableTalk

TableTalk is a small, mobile-friendly feedback application built with PHP 8.2+ and MySQL.

## Requirements

- PHP 8.2 or newer with the PDO MySQL and mbstring extensions enabled
- MySQL 8.0 or newer
- A web server with PHP support, or PHP's built-in development server

## Install

The feedback form offers the locations configured in `get_vestigingen()` in
`includes/functions.php`. Update that list to change the available locations;
the dashboard shows each location's reviews and average score separately.

1. Create the database and tables by running `schema.sql` with a MySQL account allowed to create databases.
2. Configure the database connection with these environment variables:
   - `TABLETALK_DB_HOST` (default: `127.0.0.1`)
   - `TABLETALK_DB_NAME` (default: `tabletalk`)
   - `TABLETALK_DB_USER` (default: `root`)
   - `TABLETALK_DB_PASSWORD` (default: empty)
3. Create an establishment and an administrator. Generate a password hash with:

   ```sh
   php -r "echo password_hash('Choose-a-strong-password', PASSWORD_DEFAULT), PHP_EOL;"
   ```

   Insert the establishment first, then use its ID and the generated hash in:

   ```sql
   INSERT INTO vestigingen (naam, slug) VALUES ('Restaurant Voorbeeld', 'restaurant-voorbeeld');
   INSERT INTO admins (vestiging_id, gebruikersnaam, password_hash)
   VALUES (1, 'beheerder', 'PASTE_THE_GENERATED_HASH_HERE');
   ```

   Replace the example name, slug, ID, username, and hash with your own values. Do not use the example password as an actual administrator password.
4. Serve the project directory as the web root. For local development, run:

   ```sh
   php -S 127.0.0.1:8000
   ```

5. Open `http://127.0.0.1:8000/?vestiging=restaurant-voorbeeld` to submit feedback. Open `/login.php` to sign in as a manager.

Use HTTPS in production so session cookies are transmitted securely. Configure database credentials through the environment rather than committing them to the project.
