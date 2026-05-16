<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

$user = require_login('judge');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('judge.php');
}

verify_csrf();

$criteria = [
    'articulate_requirements' => ['developing' => [0, 10], 'accomplished' => [11, 15]],
    'choose_tools' => ['developing' => [0, 10], 'accomplished' => [11, 15]],
    'oral_presentation' => ['developing' => [0, 10], 'accomplished' => [11, 15]],
    'teamwork' => ['developing' => [0, 10], 'accomplished' => [11, 15]],
];

$_SESSION['old'] = $_POST;
$errors = [];
$scores = [];

$groupMembers = trim((string) ($_POST['group_members'] ?? ''));
$groupNumber = trim((string) ($_POST['group_number'] ?? ''));
$projectTitle = trim((string) ($_POST['project_title'] ?? ''));
$judgeName = trim((string) ($_POST['judge_name'] ?? ''));
$comments = trim((string) ($_POST['comments'] ?? ''));

foreach ([
    'Group members' => $groupMembers,
    'Group number' => $groupNumber,
    'Project title' => $projectTitle,
    "Judge's name" => $judgeName,
] as $label => $value) {
    if ($value === '') {
        $errors[] = $label . ' is required.';
    }
}

foreach ($criteria as $key => $ranges) {
    $developingRaw = trim((string) ($_POST[$key . '_developing'] ?? ''));
    $accomplishedRaw = trim((string) ($_POST[$key . '_accomplished'] ?? ''));

    if ($developingRaw !== '' && $accomplishedRaw !== '') {
        $errors[] = 'Enter only one score for each criterion.';
        continue;
    }

    if ($developingRaw === '' && $accomplishedRaw === '') {
        $errors[] = 'Every criterion needs a score.';
        continue;
    }

    $column = $developingRaw !== '' ? 'developing' : 'accomplished';
    $raw = $developingRaw !== '' ? $developingRaw : $accomplishedRaw;

    if (!ctype_digit($raw)) {
        $errors[] = 'Scores must be whole numbers.';
        continue;
    }

    $score = (int) $raw;
    [$min, $max] = $ranges[$column];

    if ($score < $min || $score > $max) {
        $errors[] = ucfirst(str_replace('_', ' ', $key)) . ' must be in the ' . $column . ' range.';
        continue;
    }

    $scores[$key] = $score;
}

if ($errors !== []) {
    set_flash('error', implode(' ', array_unique($errors)));
    redirect('judge.php');
}

$total = array_sum($scores);

$stmt = db()->prepare(
    'INSERT INTO submissions (
        judge_id,
        judge_name,
        group_members,
        group_number,
        project_title,
        articulate_requirements,
        choose_tools,
        oral_presentation,
        teamwork,
        total,
        comments,
        created_at,
        updated_at
    ) VALUES (
        :judge_id,
        :judge_name,
        :group_members,
        :group_number,
        :project_title,
        :articulate_requirements,
        :choose_tools,
        :oral_presentation,
        :teamwork,
        :total,
        :comments,
        CURRENT_TIMESTAMP,
        CURRENT_TIMESTAMP
    )
    ON CONFLICT(judge_id, group_number) DO UPDATE SET
        judge_name = excluded.judge_name,
        group_members = excluded.group_members,
        project_title = excluded.project_title,
        articulate_requirements = excluded.articulate_requirements,
        choose_tools = excluded.choose_tools,
        oral_presentation = excluded.oral_presentation,
        teamwork = excluded.teamwork,
        total = excluded.total,
        comments = excluded.comments,
        updated_at = CURRENT_TIMESTAMP'
);

$stmt->execute([
    ':judge_id' => (int) $user['id'],
    ':judge_name' => $judgeName,
    ':group_members' => $groupMembers,
    ':group_number' => $groupNumber,
    ':project_title' => $projectTitle,
    ':articulate_requirements' => $scores['articulate_requirements'],
    ':choose_tools' => $scores['choose_tools'],
    ':oral_presentation' => $scores['oral_presentation'],
    ':teamwork' => $scores['teamwork'],
    ':total' => $total,
    ':comments' => $comments,
]);

unset($_SESSION['old']);
set_flash('success', 'Scores saved. Your total for this group is ' . $total . '.');
redirect('judge.php');
