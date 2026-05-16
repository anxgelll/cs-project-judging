<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

$user = require_login('admin');

$submissions = db()->query(
    'SELECT s.*, u.username
     FROM submissions s
     JOIN users u ON u.id = s.judge_id
     ORDER BY CAST(s.group_number AS INTEGER), s.group_number, s.project_title, s.judge_name'
)->fetchAll();

$averages = db()->query(
    'SELECT
        group_number,
        project_title,
        group_members,
        COUNT(*) AS judge_count,
        ROUND(AVG(total), 2) AS average_total,
        MIN(total) AS lowest_total,
        MAX(total) AS highest_total
     FROM submissions
     GROUP BY group_number, project_title
     ORDER BY CAST(group_number AS INTEGER), group_number, project_title'
)->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <header class="topbar">
        <div>
            <strong><?= e(APP_NAME) ?></strong>
            <span><?= e($user['display_name']) ?></span>
        </div>
        <a href="logout.php">Log out</a>
    </header>

    <main class="page-shell admin-shell">
        <section class="section-block">
            <div class="section-heading">
                <h1>Group Averages</h1>
                <p class="muted">Averages are calculated from submitted judge totals.</p>
            </div>

            <?php if ($averages === []): ?>
                <div class="empty-state">No scores have been submitted yet.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Group</th>
                                <th>Project Title</th>
                                <th>Members</th>
                                <th>Judges</th>
                                <th>Average</th>
                                <th>Low</th>
                                <th>High</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($averages as $row): ?>
                                <tr>
                                    <td><?= e($row['group_number']) ?></td>
                                    <td><?= e($row['project_title']) ?></td>
                                    <td><?= e($row['group_members']) ?></td>
                                    <td><?= e((string) $row['judge_count']) ?></td>
                                    <td><strong><?= e((string) $row['average_total']) ?></strong></td>
                                    <td><?= e((string) $row['lowest_total']) ?></td>
                                    <td><?= e((string) $row['highest_total']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <section class="section-block">
            <div class="section-heading">
                <h2>All Judge Submissions</h2>
            </div>

            <?php if ($submissions !== []): ?>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Group</th>
                                <th>Judge</th>
                                <th>Requirements</th>
                                <th>Tools</th>
                                <th>Presentation</th>
                                <th>Teamwork</th>
                                <th>Total</th>
                                <th>Comments</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $submission): ?>
                                <tr>
                                    <td><?= e($submission['group_number']) ?></td>
                                    <td><?= e($submission['judge_name']) ?></td>
                                    <td><?= e((string) $submission['articulate_requirements']) ?></td>
                                    <td><?= e((string) $submission['choose_tools']) ?></td>
                                    <td><?= e((string) $submission['oral_presentation']) ?></td>
                                    <td><?= e((string) $submission['teamwork']) ?></td>
                                    <td><strong><?= e((string) $submission['total']) ?></strong></td>
                                    <td><?= e($submission['comments']) ?></td>
                                    <td><?= e($submission['updated_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
