<?php
declare(strict_types=1);

session_start();

const APP_NAME = 'Computer Science Project Judging';

function db_path(): string
{
    $envPath = getenv('DB_PATH');
    if ($envPath !== false && trim($envPath) !== '') {
        return $envPath;
    }

    return __DIR__ . '/data/grading.sqlite';
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $path = db_path();
    $dir = dirname($path);

    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');

    initialize_database($pdo);

    return $pdo;
}

function initialize_database(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL CHECK (role IN ("judge", "admin")),
            display_name TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS submissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            judge_id INTEGER NOT NULL,
            judge_name TEXT NOT NULL,
            group_members TEXT NOT NULL,
            group_number TEXT NOT NULL,
            project_title TEXT NOT NULL,
            articulate_requirements INTEGER NOT NULL,
            choose_tools INTEGER NOT NULL,
            oral_presentation INTEGER NOT NULL,
            teamwork INTEGER NOT NULL,
            total INTEGER NOT NULL,
            comments TEXT NOT NULL DEFAULT "",
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (judge_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE (judge_id, group_number)
        )'
    );

    seed_users($pdo);
}

function seed_users(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $users = [
        ['judge1', 'Judge123!', 'judge', 'Judge 1'],
        ['judge2', 'Judge123!', 'judge', 'Judge 2'],
        ['judge3', 'Judge123!', 'judge', 'Judge 3'],
        ['judge4', 'Judge123!', 'judge', 'Judge 4'],
        ['admin', 'Admin123!', 'admin', 'Administrator'],
    ];

    $stmt = $pdo->prepare(
        'INSERT INTO users (username, password_hash, role, display_name)
         VALUES (:username, :password_hash, :role, :display_name)'
    );

    foreach ($users as [$username, $password, $role, $displayName]) {
        $stmt->execute([
            ':username' => $username,
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':role' => $role,
            ':display_name' => $displayName,
        ]);
    }
}

function current_user(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, username, role, display_name FROM users WHERE id = :id');
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function require_login(?string $role = null): array
{
    $user = current_user();
    if ($user === null) {
        redirect('/index.php');
    }

    if ($role !== null && $user['role'] !== $role) {
        redirect($user['role'] === 'admin' ? '/admin.php' : '/judge.php');
    }

    return $user;
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(400);
        exit('Invalid request token.');
    }
}

function old(string $key, string $default = ''): string
{
    $value = $_SESSION['old'][$key] ?? $default;
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function flash(string $key): ?string
{
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $message = (string) $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $message;
}

function set_flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
