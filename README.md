# Movya

Movya is a PHP movie and TV discovery app. Search titles and browse genres using the TVmaze and OMDb APIs, then register an account and save favorites in MySQL.

## Features

- Search TVmaze and OMDb by title
- Browse the popular and top-rated discovery feeds and filter results
- Explore titles by genre
- Register, sign in, and save or remove favorites
- PHP, MySQL, and a responsive Bootstrap interface

## Requirements

- PHP 8.3 or later with the `mysqli` extension, or Docker
- MySQL database
- An OMDb API key for OMDb search and movie details. TVmaze features do not require a key.

## Run locally with XAMPP

1. Copy the project into `C:\xampp\htdocs\movie-app` and start Apache and MySQL from the XAMPP Control Panel.
2. In phpMyAdmin, create a database named `cineverse_db`, select it, and import `database.sql`.
3. Open <http://localhost/movie-app/>.

Local development defaults to MySQL host `localhost`, user `root`, an empty password, and database `cineverse_db`. To override them, set `MYSQL_HOST`, `MYSQL_PORT`, `MYSQL_DATABASE`, `MYSQL_USER`, and `MYSQL_PASSWORD` in the PHP process environment.

## Deploy to Railway

Railway provides a MySQL service that works with this app. Keep the database private within your Railway project; do not commit database credentials.

1. **Create a Railway project and MySQL database.** In Railway, create or open a project, choose **New → Database → MySQL**, and wait until the database service is running.
2. **Deploy the app from GitHub.** Choose **New → GitHub Repo** and select this repository. The app is in the repository root, so leave the root directory blank. Railway detects the root `Dockerfile` and builds the PHP/Apache web service.
3. **Connect the app service to MySQL.** Open the app service's **Variables** page and add the following variables. Replace `MySQL` in each reference with the exact name shown on your database service if you renamed it.

   | App variable | Value |
   | --- | --- |
   | `MYSQL_URL` | `${{ MySQL.MYSQL_URL }}` |
   | `OMDB_API_KEY` | Your OMDb API key |

   Add these to the **app service**, not to the MySQL service. Railway references use `${{ ServiceName.VARIABLE }}`. You can instead map the separate `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, and `MYSQLPASSWORD` values to the app's `MYSQL_*` variables.

4. **Apply the variables and deploy.** Review and deploy Railway's staged changes. If you change variables later, deploy those changes so they are available to the running app.
5. **Create the app tables.** Connect a MySQL client to Railway, select the database, and run `database.sql`. The file creates the `users` and `favorites` tables. For a connection from your computer, use Railway's **Settings → Networking** to enable a TCP Proxy and use the public host and port, or use Railway's CLI to connect.
6. **Open the app.** Use the public domain shown in the app service's **Settings → Networking**. Test registration, login, search, and favorites.

The OMDb key is sent to visitors' browsers because the current app calls OMDb directly; do not reuse a credential that must remain private.

### Render alternative

The included `render.yaml` and Dockerfile can also deploy the app on Render, but Render does not provide a MySQL service for it. Use an external MySQL provider and set the `MYSQL_*` variables in Render's service environment.

#### Render app with Railway MySQL

The Render app cannot use Railway's private `MYSQLHOST`; that hostname is only reachable by services inside the Railway project. To connect these two providers:

1. In the Railway MySQL service, open **Settings → Networking** and enable **Public Networking / TCP Proxy**. Railway provides a public connection URL for connecting from outside Railway. This may incur network charges.
2. In Render, open the app service's **Environment** page and set either:

   - `MYSQL_URL` = Railway's complete `MYSQL_PUBLIC_URL` connection string; **or**
   - the separate variables below, using the public TCP proxy hostname and proxy port (not the private `MYSQLHOST` and not necessarily port `3306`):

   | Render variable | Value |
   | --- | --- |
   | `MYSQL_HOST` | Railway's generated public TCP proxy hostname |
   | `MYSQL_PORT` | Railway's generated TCP proxy port |
   | `MYSQL_DATABASE` | Railway's `MYSQLDATABASE` value |
   | `MYSQL_USER` | Railway's `MYSQLUSER` value |
   | `MYSQL_PASSWORD` | Railway's `MYSQLPASSWORD` value |

   Enter the values in Render, not Railway variable-reference syntax. Keep the connection URL/password private.
3. If you have not initialized the tables yet, connect a MySQL client to Railway through the TCP proxy, select the database, and run `database.sql`.
4. Save the variables and redeploy the Render service. Confirm the deployment for the latest GitHub commit finishes before testing; old deployments do not include updated database configuration. In Render's deploy list, compare the deployed commit with the latest commit on the repository's `main` branch.

If Render logs report `No such file or directory` from `mysqli_connect`, PHP tried a local MySQL socket instead of a network hostname. Check the deployed commit and confirm that the Render web service has a non-empty `MYSQL_URL` or `MYSQL_HOST` set to Railway's **public** TCP proxy.

## Configuration

| Variable | Local fallback | Railway equivalent | Purpose |
| --- | --- | --- | --- |
| `MYSQL_URL` | — | `MYSQL_URL` or `MYSQL_PUBLIC_URL` | Full MySQL connection URL; takes priority over separate settings |
| `MYSQL_HOST` | `localhost` | `MYSQLHOST` | MySQL server hostname |
| `MYSQL_PORT` | `3306` | `MYSQLPORT` | MySQL server port |
| `MYSQL_DATABASE` | `cineverse_db` | `MYSQLDATABASE` | Database containing the app tables |
| `MYSQL_USER` | `root` | `MYSQLUSER` | Database username |
| `MYSQL_PASSWORD` | empty | `MYSQLPASSWORD` | Database password |
| `OMDB_API_KEY` | `trilogy` | — | OMDb API key |

Hosted deployments do not silently fall back to `localhost` when no database host is configured; they return an HTTP 500 with a configuration message instead.

## APIs and attribution

- [TVmaze API](https://www.tvmaze.com/api)
- [OMDb API](https://www.omdbapi.com/)
