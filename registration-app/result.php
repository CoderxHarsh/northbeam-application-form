<?php
require __DIR__ . '/includes/config.php';

$a = $_SESSION['application'] ?? null;
if (!$a) {
    header('Location: index.php');
    exit;
}

$firstName = explode(' ', $a['full_name'])[0];
$submitted = new DateTime($a['submitted_at']);
$dob       = DateTime::createFromFormat('!Y-m-d', $a['dob']);
$mode      = MODES[$a['mode']];
$skills    = array_map(static fn($k) => SKILLS[$k], $a['skills']);
?>
<!doctype html>
<html lang="en" data-radius="soft">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Application <?= e($a['reference']) ?> · <?= e(SCHOOL_NAME) ?></title>
  <meta name="description" content="Your <?= e(SCHOOL_NAME) ?> application has been received.">
  <meta name="robots" content="noindex">
  <meta name="theme-color" content="#26251e">
  <link rel="icon" href="assets/img/mark.svg" type="image/svg+xml">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@400..700&family=Red+Hat+Text:wght@400..600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/result.css">
  <script>document.documentElement.classList.add('js');</script>
</head>
<body class="result-page">
  <a class="skip" href="#details">Skip to your application details</a>

  <header class="topbar">
    <div class="topbar__inner">
      <a class="brand brand--ink" href="index.php" aria-label="<?= e(SCHOOL_NAME) ?> home">
        <img src="assets/img/mark.svg" width="36" height="36" alt="">
        <span><?= e(SCHOOL_NAME) ?></span>
      </a>
      <p class="topbar__meta">Admissions · <?= e(INTAKE_YEAR) ?> intake</p>
    </div>
  </header>

  <main class="result">
    <!-- Summary: the confirmation and what happens next -->
    <section class="summary" data-enter aria-labelledby="done-title">
      <svg class="tick" viewBox="0 0 64 64" width="64" height="64" aria-hidden="true">
        <circle class="tick__ring" cx="32" cy="32" r="29" fill="none" stroke-width="3"/>
        <path class="tick__mark" d="M20 33l8 8 16-17" fill="none" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>

      <h1 id="done-title" class="summary__title">
        <span class="line"><span>Application received,</span></span>
        <span class="line"><span><?= e($firstName) ?>.</span></span>
      </h1>

      <p class="summary__lede">Your place on <strong><?= e(PROGRAMMES[$a['programme']]) ?></strong> is under review. The admissions team replies to <strong><?= e($a['email']) ?></strong> within 10 working days.</p>

      <div class="ref">
        <span class="ref__label">Reference</span>
        <code class="ref__code" data-ref><?= e($a['reference']) ?></code>
        <button type="button" class="ref__copy" data-copy aria-label="Copy reference <?= e($a['reference']) ?>">Copy</button>
      </div>

      <ol class="next">
        <li><span>1</span>Admissions reads your statement</li>
        <li><span>2</span>A 20-minute call, if we have questions</li>
        <li><span>3</span>Your decision, by email</li>
      </ol>

      <div class="summary__actions">
        <button type="button" class="btn btn--primary" data-print>Print this application</button>
        <a class="btn btn--ghost" href="index.php">Start a new application</a>
      </div>
      <p class="form__status" role="status" data-copy-status></p>
    </section>

    <!-- Details: everything that was submitted -->
    <article class="sheet" id="details" tabindex="-1" aria-labelledby="sheet-title">
      <header class="sheet__head">
        <div>
          <p class="sheet__eyebrow"><?= e(SCHOOL_NAME) ?> · Application form</p>
          <h2 id="sheet-title"><?= e($a['full_name']) ?></h2>
        </div>
        <dl class="sheet__stamp">
          <div><dt>Reference</dt><dd><?= e($a['reference']) ?></dd></div>
          <div><dt>Submitted</dt><dd><?= e($submitted->format('j F Y, H:i')) ?></dd></div>
        </dl>
      </header>

      <section class="block" aria-labelledby="b1">
        <h3 id="b1"><span>01</span>About you</h3>
        <dl class="rows">
          <div class="row"><dt>Full name</dt><dd><?= e($a['full_name']) ?></dd></div>
          <div class="row"><dt>Email</dt><dd><?= e($a['email']) ?></dd></div>
          <div class="row"><dt>Phone</dt><dd><?= e($a['phone']) ?></dd></div>
          <div class="row"><dt>Date of birth</dt><dd><?= e($dob ? $dob->format('j F Y') : $a['dob']) ?> <span class="muted">· age <?= (int) $a['age'] ?></span></dd></div>
          <div class="row"><dt>City</dt><dd><?= e($a['city']) ?></dd></div>
          <div class="row"><dt>Gender</dt><dd><?= e(GENDERS[$a['gender']]) ?></dd></div>
        </dl>
      </section>

      <section class="block" aria-labelledby="b2">
        <h3 id="b2"><span>02</span>Programme</h3>
        <dl class="rows">
          <div class="row"><dt>Programme</dt><dd><strong><?= e(PROGRAMMES[$a['programme']]) ?></strong></dd></div>
          <div class="row"><dt>Study mode</dt><dd><?= e($mode['label']) ?> <span class="muted">· <?= e($mode['note']) ?></span></dd></div>
          <div class="row"><dt>Start date</dt><dd><?= e(TERMS[$a['start_term']]) ?></dd></div>
          <div class="row"><dt>Highest qualification</dt><dd><?= e(QUALIFICATIONS[$a['qualification']]) ?></dd></div>
          <div class="row"><dt>Skills</dt><dd>
            <?php if ($skills): ?>
              <ul class="tags"><?php foreach ($skills as $s): ?><li><?= e($s) ?></li><?php endforeach; ?></ul>
            <?php else: ?>
              <span class="muted">None yet</span>
            <?php endif; ?>
          </dd></div>
        </dl>
      </section>

      <section class="block" aria-labelledby="b3">
        <h3 id="b3"><span>03</span>Statement</h3>
        <blockquote class="statement"><?= nl2br(e($a['statement'])) ?></blockquote>
        <?php if ($a['source'] !== ''): ?>
          <dl class="rows">
            <div class="row"><dt>Heard about us</dt><dd><?= e(SOURCES[$a['source']]) ?></dd></div>
          </dl>
        <?php endif; ?>
      </section>

      <footer class="sheet__foot">
        <p>Applicant confirmed the details are true · <?= e($submitted->format('Y-m-d H:i T')) ?></p>
      </footer>
    </article>
  </main>

  <footer class="foot foot--wide">
    <p>© <?= date('Y') ?> <?= e(SCHOOL_NAME) ?> · <a class="link" href="mailto:admissions@northbeam.example">Email the admissions team</a></p>
  </footer>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <script src="assets/js/result.js"></script>
</body>
</html>
