<?php
/**
 * Shared settings and helpers for the Northbeam Academy application form.
 * Every option list lives here so the form (index.php), the validator
 * (submit.php) and the result page (result.php) always agree.
 */

declare(strict_types=1);

// Times on the result page; set APP_TZ on the host (e.g. Asia/Karachi) to change it.
date_default_timezone_set(getenv('APP_TZ') ?: 'UTC');

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

const SCHOOL_NAME = 'Northbeam Academy';
const INTAKE_YEAR = '2027';

const PROGRAMMES = [
    'software'  => 'Software Engineering',
    'data'      => 'Data Science',
    'design'    => 'UI and UX Design',
    'cloud'     => 'Cloud and DevOps',
    'security'  => 'Cybersecurity',
];

const MODES = [
    'campus' => ['label' => 'On campus', 'note' => 'Five days a week, in person'],
    'online' => ['label' => 'Online',    'note' => 'Live classes, from anywhere'],
    'hybrid' => ['label' => 'Hybrid',    'note' => 'Two days in, three online'],
];

const TERMS = [
    'feb-2027' => 'February 2027',
    'sep-2027' => 'September 2027',
];

const QUALIFICATIONS = [
    'secondary' => 'Secondary school',
    'diploma'   => 'Diploma or college certificate',
    'bachelor'  => "Bachelor's degree",
    'master'    => "Master's degree or higher",
];

const GENDERS = [
    'female'  => 'Female',
    'male'    => 'Male',
    'other'   => 'Another identity',
    'private' => 'Prefer not to say',
];

const SKILLS = [
    'html'   => 'HTML and CSS',
    'js'     => 'JavaScript',
    'php'    => 'PHP',
    'python' => 'Python',
    'sql'    => 'SQL',
    'git'    => 'Git',
];

const SOURCES = [
    ''        => 'Choose one (optional)',
    'friend'  => 'A friend or classmate',
    'search'  => 'A web search',
    'social'  => 'Social media',
    'event'   => 'A school or careers event',
];

const STATEMENT_MIN = 50;
const STATEMENT_MAX = 600;

/** Escape any value for safe HTML output. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** A per-session token that every POST must carry back. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}
