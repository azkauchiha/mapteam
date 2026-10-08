# WebGIS

The map frontend runs as a CodeIgniter 4 application. Static map layers, Excel
datasets, images, styles, and JavaScript stay in their existing project
directories and are served through authenticated CodeIgniter routes. The
default user database is SQLite at `writable/webgis.sqlite`.

## Run with Laragon

Use PHP 8.2 or newer. In Laragon, start Apache and open the application at
`http://localhost/webgis/login`. The project's root `index.php` forwards requests
to CodeIgniter; map files and datasets are served through authenticated routes.
If Composer dependencies are missing, install them from the project directory.
Alternatively, start CodeIgniter's standalone development server:

```powershell
php spark serve
```

The standalone server uses `http://localhost:8080/`; when using it instead of
Laragon Apache, set `app.baseURL` in `.env` to `http://localhost:8080/`.
Map assets and datasets are only available after signing in.

## Create the first administrator

Run the database migration and create an administrator account from an
interactive terminal. The password is visible while typing; use a private
terminal and choose a password of at least 12 characters.

```powershell
php spark migrate
php spark admin:create
```

There is no preset username or password. `admin:create` asks you to choose the
admin username, display name, and password; it does not print or store a
default password. The terminal confirms when the account is saved or displays
an error if it cannot be created. After creating the admin, sign in at
`http://localhost/webgis/login`. Administrators can manage the application from
the sidebar at `/admin`, including the FAT diagram, full map data, Excel uploads,
and user management.
The admin sidebar separates these features into individual pages.
User management supports assigning `user` or `admin` roles and viewing,
editing, or deleting accounts. The active administrator and the last remaining
administrator cannot be deleted or demoted.
Uploaded workbooks append the first sheet's rows to the original map data;
include Latitude and Longitude columns, and keep each file at or below 25 MB.
Regular accounts can sign in and use the map but cannot create other accounts.
Login, user creation, logout, and CSV/map-data writes are protected by CSRF.
Map-data workbook uploads are restricted to administrators; uploaded files are
stored under `writable/map_uploads` and served only to signed-in users.

## GitHub source repository

The public source repository intentionally excludes customer workbooks, CSV
exports, SQLite databases, local environment files, runtime uploads, and build
logs. To run the original map dataset locally, place an authorized copy of
`Data FAT Full.xlsx` at `data/Data FAT Full.xlsx`; do not commit operational map
data to a public repository.

## Deploy to InfinityFree

GitHub stores the source; GitHub Pages cannot run this PHP application. The
`Deploy CodeIgniter to InfinityFree` workflow builds the Composer dependencies,
runs the PHP tests, and uploads the production files to `/htdocs/` after a push
to `main`.

Before deploying:

1. In the GitHub repository, open **Settings > Secrets and variables >
   Actions** and add `FTP_USERNAME` and `FTP_PASSWORD` from the hosting panel.
   Never put FTP credentials in a commit or share them in chat.
2. The workflow uses plain FTP on port 21 because this host configuration uses
   FTP. FTP does not encrypt credentials or files in transit. Use a dedicated
   FTP password, rotate it if it was shared, and keep it only in GitHub
   Actions Secrets.
3. Create `/htdocs/.env` in the hosting File Manager (copy the shape of
   `.env.example`) and set:

   ```ini
   CI_ENVIRONMENT = production
   app.baseURL = 'https://fat.gt.tc/'
   app.setupKey = 'REPLACE_WITH_A_PRIVATE_RANDOM_VALUE'
   ```

   Generate the setup key locally with
   `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`. Do not commit or share
   the resulting key. The setup page requires it to create the first admin.
4. After a successful deployment, open `https://fat.gt.tc/setup`, enter the
   setup key, and create the first admin account. The users table is created by
   the application's migration. Remove the `app.setupKey` line from
   `/htdocs/.env` after setup.
5. Sign in at `https://fat.gt.tc/login` and upload an authorized copy of the
   main FAT workbook from **Menu Admin > Upload Excel**. The SQLite database
   and uploaded workbooks remain on the host and are excluded from deployments.

The workflow skips its FTP step with a warning until both GitHub Actions
secrets exist. Add them before deployment or rerun the workflow from the
repository's **Actions** tab after adding them. It never runs a destructive
clean-slate deploy. Confirm that the hosting PHP version is 8.2+ and has
SQLite/PDO_SQLite enabled. PHP upload and execution limits may be lower than
the application's 25 MB workbook limit.

For Laragon virtual hosts or other deployments, set `app.baseURL` in `.env`;
the default remains `http://localhost/webgis/`.

## Password storage

Passwords are stored using SHA-256 and an independently generated, random
128-bit salt per account. The salt is stored separately beside the 64-character
hash and is included in verification. SHA-256 is a fast hash, not encryption,
and is not a password-specific key derivation function; use PHP
`password_hash()` and `password_verify()` for applications requiring stronger
resistance to offline password guessing.
