<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

$user = current_user();
if ($user !== null) {
    redirect($user['role'] === 'admin' ? '/admin.php' : '/judge.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->execute([':username' => $username]);
    $foundUser = $stmt->fetch();

    if ($foundUser && password_verify($password, $foundUser['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $foundUser['id'];
        redirect($foundUser['role'] === 'admin' ? '/admin.php' : '/judge.php');
    }

    $error = 'The username or password is incorrect.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-panel" aria-labelledby="login-title">
            <p class="eyebrow">Computer Science Project</p>
            <h1 id="login-title">Judge Login</h1>
            <p class="muted">Sign in as a judge to submit scores, or as admin to review group averages.</p>

            <?php if ($error): ?>
                <div class="alert error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" class="stacked-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <label>
                    Username
                    <input type="text" name="username" autocomplete="username" required autofocus>
                </label>

                <label>
                    Password
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>

                <button type="submit">Log In</button>
            </form>

            <div class="credential-card">
                <strong>Use one of these accounts</strong>
                <div class="credential-grid">
                    <span>Judge username</span>
                    <code>judge1</code>
                    <span>Judge password</span>
                    <code>Judge123!</code>
                    <span>Other judges</span>
                    <code>judge2, judge3, judge4</code>
                    <span>Admin username</span>
                    <code>admin</code>
                    <span>Admin password</span>
                    <code>Admin123!</code>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
