<?php
/**
 * Receives the application form, checks every field again on the server,
 * then either sends the visitor back with their answers and the errors,
 * or stores the application in the session and shows result.php.
 * (Post/Redirect/Get: refreshing the result page never sends twice.)
 */
require __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

/** Read a posted string, trimmed and with inner whitespace tidied. */
function field(string $key): string
{
    $v = $_POST[$key] ?? '';
    return is_string($v) ? trim(preg_replace('/\s+/u', ' ', $v)) : '';
}

$in = [
    'full_name'     => field('full_name'),
    'email'         => field('email'),
    'phone'         => field('phone'),
    'dob'           => field('dob'),
    'city'          => field('city'),
    'gender'        => field('gender'),
    'programme'     => field('programme'),
    'mode'          => field('mode'),
    'start_term'    => field('start_term'),
    'qualification' => field('qualification'),
    'skills'        => array_values(array_filter(
        (array) ($_POST['skills'] ?? []),
        static fn($s) => is_string($s) && isset(SKILLS[$s])
    )),
    // Keep the statement's line breaks.
    'statement'     => trim(str_replace("\r\n", "\n", is_string($_POST['statement'] ?? null) ? $_POST['statement'] : '')),
    'source'        => field('source'),
    'terms'         => field('terms'),
];

$errors = [];

if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
    $errors['form'] = 'Your session ran out. Please check your answers and send the form again.';
}

// About you
if ($in['full_name'] === '') {
    $errors['full_name'] = 'Write your full name.';
} elseif (!preg_match("/^\p{L}[\p{L} .'-]{2,59}$/u", $in['full_name'])) {
    $errors['full_name'] = 'Write your full name in letters, 3 to 60 characters.';
}

if ($in['email'] === '') {
    $errors['email'] = 'Write your email address.';
} elseif (!filter_var($in['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Write an email address with an @ and a domain, like name@mail.com.';
}

$digits = preg_replace('/\D/', '', $in['phone']);
if ($in['phone'] === '') {
    $errors['phone'] = 'Write your phone number.';
} elseif (!preg_match('/^\+?[\d\s()-]+$/', $in['phone']) || strlen($digits) < 7 || strlen($digits) > 15) {
    $errors['phone'] = 'Write a phone number of 7 to 15 digits; spaces and a leading + are fine.';
}

$age = null;
if ($in['dob'] === '') {
    $errors['dob'] = 'Choose your date of birth.';
} else {
    $dob = DateTime::createFromFormat('!Y-m-d', $in['dob']);
    if (!$dob || $dob->format('Y-m-d') !== $in['dob']) {
        $errors['dob'] = 'Choose a real date.';
    } else {
        $age = $dob->diff(new DateTime('today'))->y;
        if ($dob > new DateTime('today') || $age < 16) {
            $errors['dob'] = 'Applicants need to be 16 or older.';
        } elseif ($age > 90) {
            $errors['dob'] = 'Check the year of your date of birth.';
        }
    }
}

if ($in['city'] === '') {
    $errors['city'] = 'Write the city you live in.';
} elseif (mb_strlen($in['city']) < 2 || mb_strlen($in['city']) > 60) {
    $errors['city'] = 'Write the full name of your city.';
}

if (!isset(GENDERS[$in['gender']])) {
    $errors['gender'] = 'Choose one option, or "Prefer not to say".';
}

// Programme
if (!isset(PROGRAMMES[$in['programme']])) {
    $errors['programme'] = 'Choose the programme you are applying for.';
}
if (!isset(MODES[$in['mode']])) {
    $errors['mode'] = 'Choose how you want to study.';
}
if (!isset(TERMS[$in['start_term']])) {
    $errors['start_term'] = 'Choose when you want to start.';
}
if (!isset(QUALIFICATIONS[$in['qualification']])) {
    $errors['qualification'] = 'Choose your highest qualification so far.';
}

// Statement
$len = mb_strlen($in['statement']);
if ($len === 0) {
    $errors['statement'] = 'Write a few lines on why you want this programme.';
} elseif ($len < STATEMENT_MIN) {
    $errors['statement'] = 'Write at least ' . STATEMENT_MIN . " characters; you have $len.";
} elseif ($len > STATEMENT_MAX) {
    $errors['statement'] = 'Keep it to ' . STATEMENT_MAX . ' characters.';
}

if (!isset(SOURCES[$in['source']])) {
    $in['source'] = '';
}
if ($in['terms'] !== 'yes') {
    $errors['terms'] = 'Tick the box to confirm your details are true.';
}

// Back to the form with everything the visitor typed.
if ($errors) {
    $_SESSION['old']    = $in;
    $_SESSION['errors'] = $errors;
    header('Location: index.php#apply', true, 303);
    exit;
}

// Accepted: give it a reference and keep it for the result page.
$reference = sprintf('NB-%s-%s', substr(INTAKE_YEAR, -2), strtoupper(bin2hex(random_bytes(3))));

$application = $in + [
    'reference'    => $reference,
    'age'          => $age,
    'submitted_at' => (new DateTime())->format(DateTimeInterface::ATOM),
];
unset($application['terms']);

// Keep a copy on disk when the host allows it (one JSON line per application).
$dir = __DIR__ . '/data';
if ((is_dir($dir) || @mkdir($dir, 0775, true)) && is_writable($dir)) {
    @file_put_contents($dir . '/applications.jsonl', json_encode($application, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
}

$_SESSION['application'] = $application;
unset($_SESSION['csrf']); // a fresh token for the next application

header('Location: result.php', true, 303);
exit;
