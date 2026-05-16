# Computer Science Project Judging App

Small PHP rubric app for four judges and one admin.

## Features

- Judge and admin login with hashed seeded passwords
- Rubric form matching the assignment
- Developing scores allow `0-10`
- Accomplished scores allow `11-15`
- A judge cannot enter both columns for the same criterion
- Server-side validation protects the same rule even if JavaScript is bypassed
- One submission per judge per group number; submitting again updates that judge's score
- Admin dashboard shows all submissions and group averages
- SQLite database path is configurable for Render persistent disks

## Default Accounts

| Role | Username | Password |
| --- | --- | --- |
| Judge | `judge1` | `Judge123!` |
| Judge | `judge2` | `Judge123!` |
| Judge | `judge3` | `Judge123!` |
| Judge | `judge4` | `Judge123!` |
| Admin | `admin` | `Admin123!` |

## Run Locally With PHP

```bash
php -S localhost:8000
```

Then open:

```text
http://localhost:8000
```

The local database is created at:

```text
data/grading.sqlite
```

## Run Locally With Docker

```bash
docker build -t cs-project-judging .
docker run --rm -p 8080:80 -v "$PWD/data:/var/data" cs-project-judging
```

Then open:

```text
http://localhost:8080
```

## Deploy On Render

This project includes both `Dockerfile` and `render.yaml`.

Recommended Render setup:

1. Create a new Web Service from this repository.
2. Use Docker as the environment.
3. Add a persistent disk mounted at:

```text
/var/data
```

4. Set the environment variable:

```text
DB_PATH=/var/data/grading.sqlite
```

The included `render.yaml` already describes that setup.
