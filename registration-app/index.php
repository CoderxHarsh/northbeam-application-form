<?php
require __DIR__ . '/includes/config.php';

// Values and errors handed back by submit.php when the server check fails.
$old    = $_SESSION['old']    ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$val = static fn(string $k): string => e(isset($old[$k]) && is_string($old[$k]) ? $old[$k] : '');
$is  = static fn(string $k, string $v): bool => isset($old[$k]) && $old[$k] === $v;
$has = static fn(string $k, string $v): bool => isset($old[$k]) && is_array($old[$k]) && in_array($v, $old[$k], true);
$err = static fn(string $k): string => e($errors[$k] ?? '');
$inv = static fn(string $k): string => isset($errors[$k]) ? ' aria-invalid="true"' : '';

$maxDob = (new DateTime('-16 years'))->format('Y-m-d');
?>
<!doctype html>
<html lang="en" data-radius="soft">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Apply to <?= e(SCHOOL_NAME) ?></title>
  <meta name="description" content="Apply online for a <?= e(INTAKE_YEAR) ?> place at <?= e(SCHOOL_NAME) ?>: software, data, design, cloud and security programmes. Three steps, about six minutes.">
  <meta name="theme-color" content="#26251e">
  <meta property="og:title" content="Apply to <?= e(SCHOOL_NAME) ?>">
  <meta property="og:description" content="Online application for the <?= e(INTAKE_YEAR) ?> intake. Three steps, about six minutes.">
  <meta property="og:image" content="assets/img/photo-d183-960.jpg">
  <link rel="icon" href="assets/img/mark.svg" type="image/svg+xml">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@400..700&family=Red+Hat+Text:wght@400..600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <script>document.documentElement.classList.add('js');</script>
</head>
<body>
  <a class="skip" href="#apply">Skip to the application form</a>

  <div class="shell">
    <!-- Left: what this is, for whom, the hard facts -->
    <aside class="intro" data-enter>
      <a class="brand" href="index.php" aria-label="<?= e(SCHOOL_NAME) ?> home">
        <img src="assets/img/mark.svg" width="36" height="36" alt="">
        <span><?= e(SCHOOL_NAME) ?></span>
      </a>

      <div class="intro__copy">
        <p class="eyebrow">Admissions · <?= e(INTAKE_YEAR) ?> intake</p>
        <h1 class="intro__title">
          <span class="line"><span>Apply for a place</span></span>
          <span class="line"><span>on a Northbeam</span></span>
          <span class="line"><span>tech programme</span></span>
        </h1>
        <p class="intro__lede">Five full-time programmes, from software engineering to cybersecurity. Tell us who you are and what you want to build; a person on our admissions team reads every application.</p>

        <ul class="facts">
          <li><strong>3 steps</strong><span>about six minutes</span></li>
          <li><strong>10 days</strong><span>to a decision by email</span></li>
          <li><strong>2 starts</strong><span>February and September</span></li>
        </ul>
      </div>

      <figure class="intro__photo">
        <img src="assets/img/photo-d183-960.jpg"
             srcset="assets/img/photo-d183-500.jpg 500w, assets/img/photo-d183-960.jpg 960w, assets/img/photo-d183.jpg 1280w"
             sizes="(max-width: 959px) 100vw, 40vw" width="1280" height="853"
             alt="Open notebook with a ribbon marker and a pencil on a dark wooden table"
             decoding="async" data-source="library-057" data-subject="desk">
      </figure>
    </aside>

    <!-- Right: the form -->
    <main class="apply" id="apply" tabindex="-1">
      <div class="apply__inner">
        <header class="apply__head">
          <h2>Your application</h2>
          <p>Fields marked <span class="req" aria-hidden="true">*</span><span class="sr">with a star</span> are required.</p>
        </header>

        <ol class="stepper" aria-label="Application steps">
          <li class="stepper__item is-current" data-step-dot="0"><span class="stepper__num">1</span><span class="stepper__label">About you</span></li>
          <li class="stepper__item" data-step-dot="1"><span class="stepper__num">2</span><span class="stepper__label">Programme</span></li>
          <li class="stepper__item" data-step-dot="2"><span class="stepper__num">3</span><span class="stepper__label">Statement</span></li>
          <li class="stepper__track" aria-hidden="true"><span class="stepper__fill"></span></li>
        </ol>

        <?php if ($errors):
          $fieldErrors = count(array_diff_key($errors, ['form' => 1])); ?>
          <div class="alert" role="alert" data-server-errors>
            <?php if (isset($errors['form'])): ?>
              <strong><?= e($errors['form']) ?></strong>
            <?php endif; ?>
            <?php if ($fieldErrors): ?>
              <strong><?= $fieldErrors === 1 ? '1 field needs' : $fieldErrors . ' fields need' ?> a fix.</strong>
              Nothing you typed was lost; the fields are marked below.
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <form id="applyForm" class="form" action="submit.php" method="post" novalidate>
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

          <!-- STEP 1 ---------------------------------------------------- -->
          <fieldset class="step is-active" data-step="0">
            <legend class="step__title"><span>Step 1 of 3</span>About you</legend>

            <div class="grid">
              <div class="field field--full">
                <label for="full_name">Full name <span class="req" aria-hidden="true">*</span></label>
                <input id="full_name" name="full_name" type="text" autocomplete="name" maxlength="60" value="<?= $val('full_name') ?>" aria-describedby="full_name-error"<?= $inv('full_name') ?>>
                <p class="field__error" id="full_name-error"><?= $err('full_name') ?></p>
              </div>

              <div class="field">
                <label for="email">Email <span class="req" aria-hidden="true">*</span></label>
                <input id="email" name="email" type="email" autocomplete="email" maxlength="100" value="<?= $val('email') ?>" aria-describedby="email-error"<?= $inv('email') ?>>
                <p class="field__error" id="email-error"><?= $err('email') ?></p>
              </div>

              <div class="field">
                <label for="phone">Phone <span class="req" aria-hidden="true">*</span></label>
                <input id="phone" name="phone" type="tel" autocomplete="tel" maxlength="20" value="<?= $val('phone') ?>" aria-describedby="phone-hint phone-error"<?= $inv('phone') ?>>
                <p class="field__hint" id="phone-hint">With country code, for example +92</p>
                <p class="field__error" id="phone-error"><?= $err('phone') ?></p>
              </div>

              <div class="field">
                <label for="dob">Date of birth <span class="req" aria-hidden="true">*</span></label>
                <input id="dob" name="dob" type="date" autocomplete="bday" max="<?= e($maxDob) ?>" value="<?= $val('dob') ?>" aria-describedby="dob-error"<?= $inv('dob') ?>>
                <p class="field__error" id="dob-error"><?= $err('dob') ?></p>
              </div>

              <div class="field">
                <label for="city">City <span class="req" aria-hidden="true">*</span></label>
                <input id="city" name="city" type="text" autocomplete="address-level2" maxlength="60" value="<?= $val('city') ?>" aria-describedby="city-error"<?= $inv('city') ?>>
                <p class="field__error" id="city-error"><?= $err('city') ?></p>
              </div>

              <div class="field field--full" role="radiogroup" aria-labelledby="gender-label" aria-describedby="gender-error"<?= $inv('gender') ?>>
                <span class="label" id="gender-label">Gender <span class="req" aria-hidden="true">*</span></span>
                <div class="chips">
                  <?php foreach (GENDERS as $key => $label): ?>
                    <label class="chip">
                      <input type="radio" name="gender" value="<?= e($key) ?>"<?= $is('gender', $key) ? ' checked' : '' ?>>
                      <span><?= e($label) ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
                <p class="field__error" id="gender-error"><?= $err('gender') ?></p>
              </div>
            </div>

            <div class="actions">
              <button type="button" class="btn btn--primary" data-next>Continue to programme <span class="btn__arrow" aria-hidden="true">→</span></button>
            </div>
          </fieldset>

          <!-- STEP 2 ---------------------------------------------------- -->
          <fieldset class="step" data-step="1">
            <legend class="step__title"><span>Step 2 of 3</span>Programme</legend>

            <div class="grid">
              <div class="field field--full">
                <label for="programme">Programme <span class="req" aria-hidden="true">*</span></label>
                <div class="select">
                  <select id="programme" name="programme" aria-describedby="programme-error"<?= $inv('programme') ?>>
                    <option value="">Choose a programme</option>
                    <?php foreach (PROGRAMMES as $key => $label): ?>
                      <option value="<?= e($key) ?>"<?= $is('programme', $key) ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <p class="field__error" id="programme-error"><?= $err('programme') ?></p>
              </div>

              <div class="field field--full" role="radiogroup" aria-labelledby="mode-label" aria-describedby="mode-error"<?= $inv('mode') ?>>
                <span class="label" id="mode-label">Study mode <span class="req" aria-hidden="true">*</span></span>
                <div class="options">
                  <?php foreach (MODES as $key => $m): ?>
                    <label class="option">
                      <input type="radio" name="mode" value="<?= e($key) ?>"<?= $is('mode', $key) ? ' checked' : '' ?>>
                      <span class="option__body">
                        <span class="option__title"><?= e($m['label']) ?></span>
                        <span class="option__note"><?= e($m['note']) ?></span>
                      </span>
                      <span class="option__tick" aria-hidden="true"></span>
                    </label>
                  <?php endforeach; ?>
                </div>
                <p class="field__error" id="mode-error"><?= $err('mode') ?></p>
              </div>

              <div class="field">
                <label for="start_term">Start date <span class="req" aria-hidden="true">*</span></label>
                <div class="select">
                  <select id="start_term" name="start_term" aria-describedby="start_term-error"<?= $inv('start_term') ?>>
                    <option value="">Choose a start</option>
                    <?php foreach (TERMS as $key => $label): ?>
                      <option value="<?= e($key) ?>"<?= $is('start_term', $key) ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <p class="field__error" id="start_term-error"><?= $err('start_term') ?></p>
              </div>

              <div class="field">
                <label for="qualification">Highest qualification <span class="req" aria-hidden="true">*</span></label>
                <div class="select">
                  <select id="qualification" name="qualification" aria-describedby="qualification-error"<?= $inv('qualification') ?>>
                    <option value="">Choose one</option>
                    <?php foreach (QUALIFICATIONS as $key => $label): ?>
                      <option value="<?= e($key) ?>"<?= $is('qualification', $key) ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <p class="field__error" id="qualification-error"><?= $err('qualification') ?></p>
              </div>

              <div class="field field--full">
                <span class="label" id="skills-label">Skills you already have</span>
                <div class="chips" role="group" aria-labelledby="skills-label">
                  <?php foreach (SKILLS as $key => $label): ?>
                    <label class="chip chip--check">
                      <input type="checkbox" name="skills[]" value="<?= e($key) ?>"<?= $has('skills', $key) ? ' checked' : '' ?>>
                      <span><?= e($label) ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
                <p class="field__hint">Pick any that apply. None is fine too.</p>
              </div>
            </div>

            <div class="actions">
              <button type="button" class="btn btn--ghost" data-prev><span class="btn__arrow" aria-hidden="true">←</span> Back</button>
              <button type="button" class="btn btn--primary" data-next>Continue to statement <span class="btn__arrow" aria-hidden="true">→</span></button>
            </div>
          </fieldset>

          <!-- STEP 3 ---------------------------------------------------- -->
          <fieldset class="step" data-step="2">
            <legend class="step__title"><span>Step 3 of 3</span>Statement</legend>

            <div class="grid">
              <div class="field field--full">
                <label for="statement">Why this programme? <span class="req" aria-hidden="true">*</span></label>
                <textarea id="statement" name="statement" rows="6" maxlength="<?= STATEMENT_MAX ?>" aria-describedby="statement-hint statement-error"<?= $inv('statement') ?>><?= $val('statement') ?></textarea>
                <div class="field__row">
                  <p class="field__hint" id="statement-hint">What you want to build, and what you have tried so far. <?= STATEMENT_MIN ?> to <?= STATEMENT_MAX ?> characters.</p>
                  <p class="counter" aria-hidden="true"><span data-count>0</span> / <?= STATEMENT_MAX ?></p>
                </div>
                <p class="field__error" id="statement-error"><?= $err('statement') ?></p>
              </div>

              <div class="field field--full">
                <label for="source">How did you hear about us?</label>
                <div class="select">
                  <select id="source" name="source">
                    <?php foreach (SOURCES as $key => $label): ?>
                      <option value="<?= e($key) ?>"<?= $is('source', $key) ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="field field--full">
                <label class="check">
                  <input type="checkbox" name="terms" value="yes" aria-describedby="terms-error"<?= $is('terms', 'yes') ? ' checked' : '' ?><?= $inv('terms') ?>>
                  <span class="check__box" aria-hidden="true"></span>
                  <span>The details I have given are true, and Northbeam may email me about this application. <span class="req" aria-hidden="true">*</span></span>
                </label>
                <p class="field__error" id="terms-error"><?= $err('terms') ?></p>
              </div>
            </div>

            <div class="actions">
              <button type="button" class="btn btn--ghost" data-prev><span class="btn__arrow" aria-hidden="true">←</span> Back</button>
              <button type="submit" class="btn btn--primary" data-submit>Submit application</button>
            </div>
          </fieldset>

          <p class="form__status" role="status" data-form-status></p>
        </form>
      </div>

      <footer class="foot">
        <p>© <?= date('Y') ?> <?= e(SCHOOL_NAME) ?> · <a class="link" href="mailto:admissions@northbeam.example">Email the admissions team</a></p>
        <p class="foot__credit">Photo: Jan Kahánek, CC0, via Wikimedia Commons</p>
      </footer>
    </main>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>
