/* Northbeam Academy · application form
   jQuery: validation, step changes, counter, submit.
   GSAP: page entrance, step transitions, progress bar, error shake.
   The rules mirror submit.php; the server checks everything again. */

$(function () {
  'use strict';

  var $form    = $('#applyForm');
  if (!$form.length) return;

  var $steps   = $form.find('.step');
  var $dots    = $('[data-step-dot]');
  var $fill    = $('.stepper__fill');
  var $status  = $('[data-form-status]');
  var last     = $steps.length - 1;
  var current  = 0;
  var busy     = false;
  var hasGsap  = typeof window.gsap !== 'undefined';
  var reduce   = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var motion   = hasGsap && !reduce;

  if (hasGsap) gsap.defaults({ ease: 'power3.out', duration: 0.6 });

  /* ---------- Rules (same as submit.php) ------------------------------- */

  var STATEMENT_MIN = 50, STATEMENT_MAX = 600;

  function ageFrom(iso) {
    var d = new Date(iso + 'T00:00:00');
    if (isNaN(d)) return NaN;
    var now = new Date();
    var age = now.getFullYear() - d.getFullYear();
    var m = now.getMonth() - d.getMonth();
    if (m < 0 || (m === 0 && now.getDate() < d.getDate())) age--;
    return age;
  }

  var rules = {
    full_name: function (v) {
      if (!v) return 'Write your full name.';
      if (!/^[\p{L}][\p{L} .'-]{2,59}$/u.test(v)) return 'Write your full name in letters, 3 to 60 characters.';
    },
    email: function (v) {
      if (!v) return 'Write your email address.';
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return 'Write an email address with an @ and a domain, like name@mail.com.';
    },
    phone: function (v) {
      var digits = v.replace(/\D/g, '');
      if (!v) return 'Write your phone number.';
      if (!/^\+?[\d\s()-]+$/.test(v) || digits.length < 7 || digits.length > 15) return 'Write a phone number of 7 to 15 digits; spaces and a leading + are fine.';
    },
    dob: function (v) {
      if (!v) return 'Choose your date of birth.';
      var age = ageFrom(v);
      if (isNaN(age)) return 'Choose a real date.';
      if (age < 16) return 'Applicants need to be 16 or older.';
      if (age > 90) return 'Check the year of your date of birth.';
    },
    city: function (v) {
      if (!v) return 'Write the city you live in.';
      if (v.length < 2) return 'Write the full name of your city.';
    },
    gender: function (v) { if (!v) return 'Choose one option, or "Prefer not to say".'; },
    programme: function (v) { if (!v) return 'Choose the programme you are applying for.'; },
    mode: function (v) { if (!v) return 'Choose how you want to study.'; },
    start_term: function (v) { if (!v) return 'Choose when you want to start.'; },
    qualification: function (v) { if (!v) return 'Choose your highest qualification so far.'; },
    statement: function (v) {
      if (!v) return 'Write a few lines on why you want this programme.';
      if (v.length < STATEMENT_MIN) return 'Write at least ' + STATEMENT_MIN + ' characters; you have ' + v.length + '.';
      if (v.length > STATEMENT_MAX) return 'Keep it to ' + STATEMENT_MAX + ' characters.';
    },
    terms: function (v) { if (!v) return 'Tick the box to confirm your details are true.'; }
  };

  function valueOf(name) {
    var $el = $form.find('[name="' + name + '"]');
    if ($el.is(':radio')) return $el.filter(':checked').val() || '';
    if ($el.is(':checkbox')) return $el.is(':checked') ? $el.val() : '';
    return $.trim($el.val() || '');
  }

  /* The element that carries aria-invalid: the input, or the radio group. */
  function holderOf(name) {
    var $el = $form.find('[name="' + name + '"]');
    return $el.is(':radio') ? $el.closest('[role="radiogroup"]') : $el;
  }

  function setError(name, msg) {
    var $holder = holderOf(name);
    var $msg = $('#' + name + '-error');
    if (msg) {
      $holder.attr('aria-invalid', 'true');
      $msg.text(msg);
    } else {
      $holder.removeAttr('aria-invalid');
      $msg.text('');
    }
  }

  function validateStep(i) {
    var failed = [];
    $steps.eq(i).find('[name]').each(function () {
      var name = this.name;
      if (!rules[name] || failed.indexOf(name) > -1) return;
      var msg = rules[name](valueOf(name));
      setError(name, msg);
      if (msg) failed.push(name);
    });

    if (failed.length) {
      var first = failed[0];
      var $focus = $form.find('[name="' + first + '"]').first();
      $focus.trigger('focus');
      setStatus(failed.length === 1 ? '1 field needs a fix.' : failed.length + ' fields need a fix.', true);
      if (motion) {
        gsap.fromTo($steps.eq(i).find('[aria-invalid="true"]').closest('.field'),
          { x: 0 },
          { keyframes: { x: [-6, 6, -4, 4, 0] }, duration: 0.4, ease: 'power1.inOut', stagger: 0.04 });
      }
    } else {
      setStatus('');
    }
    return failed.length === 0;
  }

  /* Clear a field's error once it is fixed (never shown while typing). */
  $form.on('change', 'input, select, textarea', function () {
    var name = this.name;
    if (rules[name] && holderOf(name).attr('aria-invalid') === 'true') {
      if (!rules[name](valueOf(name))) setError(name, '');
    }
  });

  function setStatus(text, isError) {
    $status.text(text).toggleClass('is-error', !!isError);
  }

  /* ---------- Steps ----------------------------------------------------- */

  function paintStepper(i) {
    $dots.each(function (n) {
      $(this)
        .toggleClass('is-current', n === i)
        .toggleClass('is-done', n < i)
        .attr('aria-current', n === i ? 'step' : null);
    });
    var scale = (i + 1) / $steps.length;
    if (motion) gsap.to($fill, { scaleX: scale, duration: 0.7, ease: 'power2.inOut' });
    else $fill.css('transform', 'scaleX(' + scale + ')');
  }

  function goTo(next, focusField) {
    if (next === current || next < 0 || next > last || busy) return;
    var dir = next > current ? 1 : -1;
    var $from = $steps.eq(current);
    var $to = $steps.eq(next);

    function show() {
      $from.removeClass('is-active');
      $to.addClass('is-active');
      current = next;
      paintStepper(current);
      var $target = focusField ? $to.find('[aria-invalid="true"]').find('input').addBack('input, select, textarea').first() : $();
      if (!$target.length) $target = $to.find('legend');
      $to.find('legend').attr('tabindex', '-1');
      $target.first().trigger('focus');

      var top = $('.apply').offset().top;
      if ($(window).scrollTop() > top) window.scrollTo({ top: top, behavior: motion ? 'smooth' : 'auto' });

      if (motion) {
        var tl = gsap.timeline({ onComplete: function () { busy = false; } });
        tl.fromTo($to, { autoAlpha: 0, x: 28 * dir }, { autoAlpha: 1, x: 0, duration: 0.5 })
          .from($to.find('.field, .actions'), { y: 14, autoAlpha: 0, duration: 0.45, stagger: 0.05 }, '<0.05');
      } else {
        busy = false;
      }
    }

    busy = true;
    if (motion) {
      gsap.to($from, { autoAlpha: 0, x: -28 * dir, duration: 0.28, ease: 'power2.in', onComplete: function () {
        gsap.set($from, { clearProps: 'all' });
        show();
      } });
    } else {
      show();
    }
  }

  $form.on('click', '[data-next]', function () {
    if (validateStep(current)) goTo(current + 1);
  });
  $form.on('click', '[data-prev]', function () {
    setStatus('');
    goTo(current - 1);
  });

  /* Enter in a text field moves on instead of sending early. */
  $form.on('keydown', 'input:not([type=checkbox]):not([type=radio])', function (ev) {
    if (ev.key === 'Enter' && current < last) {
      ev.preventDefault();
      if (validateStep(current)) goTo(current + 1);
    }
  });

  /* ---------- Statement counter ---------------------------------------- */

  var $statement = $('#statement');
  var $count = $('[data-count]');
  function paintCount() {
    var n = $statement.val().length;
    $count.text(n);
    $count.parent().toggleClass('is-ok', n >= STATEMENT_MIN && n <= STATEMENT_MAX);
  }
  $statement.on('input', paintCount);
  paintCount();

  /* ---------- Submit ----------------------------------------------------- */

  $form.on('submit', function (ev) {
    ev.preventDefault();
    if (busy) return;

    // Check every step; jump to the first one that fails.
    for (var i = 0; i <= last; i++) {
      if (!validateStep(i)) {
        if (i !== current) goTo(i, true);
        return;
      }
    }

    var $btn = $form.find('[data-submit]');
    busy = true;
    $btn.attr('aria-busy', 'true');
    var slow = setTimeout(function () { setStatus('Sending your application…'); }, 300);
    var formEl = this;

    function send() { clearTimeout(slow); setStatus('Sending your application…'); formEl.submit(); }

    if (motion) {
      gsap.timeline({ onComplete: send })
        .to($btn, { scale: 0.97, duration: 0.12, ease: 'power2.out' })
        .to($btn, { scale: 1, duration: 0.25, ease: 'back.out(3)' })
        .to('.apply__inner', { autoAlpha: 0.5, y: -6, duration: 0.3, ease: 'power2.in' }, '<');
    } else {
      send();
    }
  });

  /* Coming back from the browser's back button: put the page back to normal. */
  $(window).on('pageshow', function (e) {
    if (e.originalEvent && e.originalEvent.persisted) {
      busy = false;
      $form.find('[data-submit]').removeAttr('aria-busy');
      setStatus('');
      if (hasGsap) gsap.set('.apply__inner', { clearProps: 'all' });
    }
  });

  /* ---------- Server errors: open the first step that has one ----------- */

  if ($('[data-server-errors]').length) {
    var $bad = $steps.find('[aria-invalid="true"]').first();
    var badStep = $bad.closest('.step').index('.step');
    if (badStep > 0) {
      $steps.removeClass('is-active').eq(badStep).addClass('is-active');
      current = badStep;
    }
    paintStepper(current);
    var $first = $bad.is('[role="radiogroup"]') ? $bad.find('input').first() : $bad;
    setTimeout(function () { $first.trigger('focus'); }, 50);
  }

  /* ---------- Entrance (once, on load) ---------------------------------- */

  if (motion) {
    var mm = gsap.matchMedia();
    mm.add({ wide: '(min-width: 960px)', narrow: '(max-width: 959px)' }, function (ctx) {
      var wide = ctx.conditions.wide;
      var tl = gsap.timeline({ defaults: { ease: 'power4.out' } });
      tl.from('.brand', { autoAlpha: 0, y: -10, duration: 0.6 })
        .from('.eyebrow', { autoAlpha: 0, y: 10, duration: 0.5 }, '-=0.35')
        .from('.intro__title .line > span', { yPercent: 105, duration: 1, stagger: 0.09 }, '-=0.3')
        .from('.intro__lede', { autoAlpha: 0, y: 14, duration: 0.7 }, '-=0.65')
        .from('.facts li', { autoAlpha: 0, y: 14, duration: 0.6, stagger: 0.07 }, '-=0.5')
        .from('.intro__photo', { clipPath: 'inset(100% 0% 0% 0% round 18px)', duration: 1.1, ease: 'expo.out' }, wide ? 0.35 : '-=0.4')
        .from('.intro__photo img', { scale: 1.15, duration: 1.4, ease: 'expo.out', clearProps: 'transform' }, '<')
        .from('.apply__head, .stepper', { autoAlpha: 0, y: 18, duration: 0.7, stagger: 0.08 }, wide ? 0.3 : '-=0.6')
        .from('.step.is-active .step__title, .step.is-active .field, .step.is-active .actions',
              { autoAlpha: 0, y: 18, duration: 0.6, stagger: 0.045 }, '-=0.45')
        .fromTo($fill, { scaleX: 0 }, { scaleX: (current + 1) / $steps.length, duration: 0.9, ease: 'power2.inOut' }, '-=0.6');
    });
  }
});
