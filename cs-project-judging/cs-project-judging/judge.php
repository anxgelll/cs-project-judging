<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

$user = require_login('judge');
$success = flash('success');
$error = flash('error');
$criteria = [
    'articulate_requirements' => 'Articulate requirements',
    'choose_tools' => 'Choose appropriate tools and methods for each task',
    'oral_presentation' => 'Give clear and coherent oral presentation',
    'teamwork' => 'Functioned well as a team',
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Judge Form | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/styles.css">
    <script src="assets/app.js" defer></script>
</head>
<body>
    <header class="topbar">
        <div>
            <strong><?= e(APP_NAME) ?></strong>
            <span><?= e($user['display_name']) ?></span>
        </div>
        <a href="logout.php">Log out</a>
    </header>

    <main class="page-shell">
        <?php if ($success): ?>
            <div class="alert success"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="submit.php" class="rubric-form" data-rubric-form>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <table class="rubric-table">
                <thead>
                    <tr>
                        <th colspan="3" class="project-heading">Computer Science Project</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">
                            <label class="inline-field">
                                <span>Group Members:</span>
                                <input type="text" name="group_members" value="<?= old('group_members') ?>" required>
                            </label>
                        </td>
                        <td>
                            <label class="inline-field">
                                <span>Group Number:</span>
                                <input type="text" name="group_number" value="<?= old('group_number') ?>" required>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <label class="inline-field">
                                <span>Project Title:</span>
                                <input type="text" name="project_title" value="<?= old('project_title') ?>" required>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th>Criteria</th>
                        <th>Developing (0-10)</th>
                        <th>Accomplished (11-15)</th>
                    </tr>

                    <?php foreach ($criteria as $key => $label): ?>
                        <tr class="criterion-row">
                            <td class="criterion-label"><?= e($label) ?></td>
                            <td>
                                <input
                                    type="number"
                                    name="<?= e($key) ?>_developing"
                                    min="0"
                                    max="10"
                                    step="1"
                                    inputmode="numeric"
                                    value="<?= old($key . '_developing') ?>"
                                    data-score
                                    data-pair="<?= e($key) ?>"
                                    data-column="developing"
                                    aria-label="<?= e($label) ?> developing score"
                                >
                            </td>
                            <td>
                                <input
                                    type="number"
                                    name="<?= e($key) ?>_accomplished"
                                    min="11"
                                    max="15"
                                    step="1"
                                    inputmode="numeric"
                                    value="<?= old($key . '_accomplished') ?>"
                                    data-score
                                    data-pair="<?= e($key) ?>"
                                    data-column="accomplished"
                                    aria-label="<?= e($label) ?> accomplished score"
                                >
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <tr class="total-row">
                        <td>Total</td>
                        <td colspan="2"><output data-total>0</output></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <label class="inline-field">
                                <span>Judge's name:</span>
                                <input type="text" name="judge_name" value="<?= old('judge_name', $user['display_name']) ?>" required>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <label class="comments-field">
                                <span>Comments:</span>
                                <textarea name="comments" rows="4"><?= old('comments') ?></textarea>
                            </label>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="form-actions">
                <p class="muted">Each row accepts either a developing score or an accomplished score, never both.</p>
                <button type="submit">Submit Scores</button>
            </div>
        </form>
    </main>
</body>
</html>
