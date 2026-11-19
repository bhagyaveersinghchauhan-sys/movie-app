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

## Deploy to Render

Render does not provide a built-in MySQL database for this app. Create a reachable MySQL database with a managed database provider first; keep its host, port, database name, username, and password available. Do not put database credentials in Git.

1. **Push this project to GitHub.** Create an empty GitHub repository, then from this project directory run:

   ```powershell
   git init
   git add .
   git commit -m "Prepare Movya for Render"
   git branch -M main
   git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPOSITORY.git
   git push -u origin main
   ```

   Replace the remote URL with your repository's URL. If this folder is already a Git repository, add the remote and push the existing branch instead of running `git init`.

2. **Create and initialize the hosted database.** Create a database with your provider, connect to it using its dashboard or SQL client, and run the contents of `database.sql` inside the database you created. The SQL file creates the `users` and `favorites` tables; the database itself must already exist.
3. **Create the Render service.** In Render, choose **New → Blueprint**, connect the GitHub repository, and deploy. Render will use `render.yaml` and the included Dockerfile.
4. **Set environment variables** for the web service in the Render dashboard:

   | Key | Value |
   | --- | --- |
   | `MYSQL_HOST` | Hostname supplied by your MySQL provider |
   | `MYSQL_PORT` | Provider's MySQL port, usually `3306` |
   | `MYSQL_DATABASE` | Database name created with your provider |
   | `MYSQL_USER` | Database username |
   | `MYSQL_PASSWORD` | Database password |
   | `OMDB_API_KEY` | Your OMDb API key |

   The Blueprint prompts for the `sync: false` values. If you enter database values during Blueprint setup, verify them in the Render service's **Environment** page. The OMDb key is sent to visitors' browsers because the current app calls OMDb directly; do not reuse a credential that must remain private.
5. **Redeploy** after setting or changing environment variables, then open the `onrender.com` URL shown on the Render service page. Test account registration, login, search, and favorites.

The included Blueprint uses Render's free web-service plan. Free web services may spin down after inactivity, so the first request can take longer. Database availability, pricing, and network access depend on the MySQL provider; ensure it permits connections from Render.

## Configuration

| Variable | Local fallback | Purpose |
| --- | --- | --- |
| `MYSQL_HOST` | `localhost` | MySQL server hostname |
| `MYSQL_PORT` | `3306` | MySQL server port |
| `MYSQL_DATABASE` | `cineverse_db` | Database containing the app tables |
| `MYSQL_USER` | `root` | Database username |
| `MYSQL_PASSWORD` | empty | Database password |
| `OMDB_API_KEY` | `trilogy` | OMDb API key |

## APIs and attribution

- [TVmaze API](https://www.tvmaze.com/api)
- [OMDb API](https://www.omdbapi.com/)
