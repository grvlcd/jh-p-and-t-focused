<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## About

Laravel application with protocols, threads, comments, reviews, and voting. Search is powered by Typesense (protocols and threads are indexed for full-text search).

---

## Setup instructions

### Requirements

- PHP 8.3+
- Composer
- MySQL (or SQLite/PostgreSQL)
- [Typesense](https://typesense.org) (optional; for search)

### Local setup

1. **Clone and install PHP dependencies**

    ```bash
    git clone <repo-url>
    cd example-app
    composer install
    ```

2. **Environment**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    Edit `.env`: set `DB_*` for your database, and optionally Typesense (see [Typesense key/config setup](#typesense-keyconfig-setup)).

3. **Database**

    ```bash
    php artisan migrate:fresh --seed
    ```

4. **Run the app**

    ```bash
    php artisan serve
    ```

    Open `http://localhost:8000`. Health check: `http://localhost:8000/up`.

### With Docker (e.g. for Render)

- Use the included `Dockerfile`. Build: `docker build -t example-app .`
- Run: `docker run -p 8000:8000 -e APP_KEY=base64:... -e PORT=8000 example-app`
- Set all required env vars (see `.env.example`). For migrations on deploy, use a release/pre-deploy command: `php artisan migrate --force`.

---

## API overview

Base URL: **`/api`**. All responses are JSON.

### Authentication (Sanctum)

| Method | Path            | Auth | Description                                                                              |
| ------ | --------------- | ---- | ---------------------------------------------------------------------------------------- |
| `POST` | `/api/register` | No   | Register: `name`, `email`, `password`, `password_confirmation`. Returns `token`, `user`. |
| `POST` | `/api/login`    | No   | Login: `email`, `password`. Returns `token`, `user`.                                     |
| `GET`  | `/api/user`     | Yes  | Current user.                                                                            |
| `POST` | `/api/logout`   | Yes  | Revoke current token.                                                                    |

Use the token in the `Authorization` header: `Bearer <token>`.

### Protocols

| Method   | Path                  | Auth | Description                                                                                                |
| -------- | --------------------- | ---- | ---------------------------------------------------------------------------------------------------------- |
| `GET`    | `/api/protocols`      | No   | List protocols. Query: `search`, `sort` (`recent`, `most_reviewed`, `highest_rated`), `per_page` (max 50). |
| `GET`    | `/api/protocols/{id}` | No   | Single protocol with threads and reviews.                                                                  |
| `POST`   | `/api/protocols`      | Yes  | Create: `title`, `content`, optional `tags[]`, `rating`.                                                   |
| `PUT`    | `/api/protocols/{id}` | Yes  | Update protocol.                                                                                           |
| `DELETE` | `/api/protocols/{id}` | Yes  | Delete protocol.                                                                                           |

### Threads

| Method   | Path                | Auth | Description                                     |
| -------- | ------------------- | ---- | ----------------------------------------------- |
| `GET`    | `/api/threads`      | No   | List threads. Query: `protocol_id`, `per_page`. |
| `GET`    | `/api/threads/{id}` | No   | Single thread with protocol, author, comments.  |
| `PUT`    | `/api/threads/{id}` | Yes  | Update thread (owner only).                     |
| `POST`   | `/api/threads`      | Yes  | Create: `protocol_id`, `title`, `body`.         |
| `DELETE` | `/api/threads/{id}` | Yes  | Delete thread (owner only).                     |

### Comments

| Method   | Path                         | Auth | Description                                        |
| -------- | ---------------------------- | ---- | -------------------------------------------------- |
| `GET`    | `/api/threads/{id}/comments` | No   | Comments for a thread.                             |
| `POST`   | `/api/comments`              | Yes  | Create: `thread_id`, `body`, optional `parent_id`. |
| `PUT`    | `/api/comments/{id}`         | Yes  | Update comment.                                    |
| `DELETE` | `/api/comments/{id}`         | Yes  | Delete own comment.                                |

### Reviews

| Method   | Path                          | Auth | Description                                              |
| -------- | ----------------------------- | ---- | -------------------------------------------------------- |
| `GET`    | `/api/protocols/{id}/reviews` | No   | Reviews for a protocol.                                  |
| `POST`   | `/api/reviews`                | Yes  | Create/update: `protocol_id`, `rating`, optional `body`. |
| `PUT`    | `/api/reviews/{id}`           | Yes  | Update review.                                           |
| `DELETE` | `/api/reviews/{id}`           | Yes  | Delete review.                                           |

### Votes

| Method | Path                      | Auth | Description                             |
| ------ | ------------------------- | ---- | --------------------------------------- |
| `POST` | `/api/threads/{id}/vote`  | Yes  | Vote on thread: `value` (`1` or `-1`).  |
| `POST` | `/api/comments/{id}/vote` | Yes  | Vote on comment: `value` (`1` or `-1`). |

---

## Typesense key/config setup

Search uses [Typesense](https://typesense.org). Protocols and threads are indexed for full-text search; the protocol list endpoint uses Typesense when configured and falls back to SQL `LIKE` when not.

### Environment variables

In `.env` (or your deployment env):

| Variable                        | Description                                           | Example                                       |
| ------------------------------- | ----------------------------------------------------- | --------------------------------------------- |
| `TYPESENSE_HOST`                | Typesense server hostname                             | `xxx.a1.typesense.net` (Cloud) or `localhost` |
| `TYPESENSE_PORT`                | Port (usually 443 for Cloud)                          | `443`                                         |
| `TYPESENSE_PROTOCOL`            | `https` or `http`                                     | `https`                                       |
| `TYPESENSE_ADMIN_API_KEY`       | Admin API key (create collections, index/delete docs) | Keep secret; server-side only.                |
| `TYPESENSE_SEARCH_ONLY_API_KEY` | Search-only key (optional; for client-side search)    | Can be exposed if you search from the client. |

Config is in **`config/typesense.php`** (host, port, protocol, keys, and collection names `protocols` and `threads`).

### Getting keys

- **Typesense Cloud:** Create a cluster at [cloud.typesense.org](https://cloud.typesense.org). In the dashboard you get host, port, and an admin API key. Create a search-only key if needed.
- **Self-hosted:** Run Typesense yourself and generate an API key; use your server host/port and `http` if local.

### After configuration

1. **Verify connection and collections**

    ```bash
    php artisan typesense:verify
    ```

    This checks connectivity and that the `protocols` and `threads` collections exist (they are created by the app when missing).

2. **Index existing data**

    ```bash
    php artisan typesense:index-all
    ```

    Run after deploy or when you add Typesense to an existing database. New/updated protocols and threads are indexed automatically via model observers.

3. **Test search (optional)**

    ```bash
    php artisan typesense:test-search "your query"
    ```

---

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
