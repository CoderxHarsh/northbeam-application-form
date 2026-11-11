/* Result page: entrance, print, copy reference. */

$(function () {
  'use strict';

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var motion = typeof window.gsap !== 'undefined' && !reduce;
  var $status = $('[data-copy-status]');

  /* Print the sheet (print CSS hides everything else). */
  $('[data-print]').on('click', function () { window.print(); });

  /* Copy the reference number. */
  $('[data-copy]').on('click', function () {
    var $btn = $(this);
    var ref = $.trim($('[data-ref]').text());
    var done = function () {
      $btn.text('Copied');
      $status.text('Reference ' + ref + ' copied.');
      if (motion) gsap.fromTo($btn, { scale: 0.94 }, { scale: 1, duration: 0.4, ease: 'back.out(3)' });
      setTimeout(function () { $btn.text('Copy'); }, 2000);
    };
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(ref).then(done, function () { $status.text('Select the reference and copy it by hand.'); });
    } else {
      var $tmp = $('<textarea>').val(ref).css({ position: 'fixed', opacity: 0 }).appendTo('body');
      $tmp[0].select();
      try { document.execCommand('copy'); done(); } catch (e) { $status.text('Select the reference and copy it by hand.'); }
      $tmp.remove();
    }
  });

  if (!motion) return;

  /* Entrance, once: tick draws, headline rises, sheet rows deal in. */
  var ring = document.querySelector('.tick__ring');
  var mark = document.querySelector('.tick__mark');
  var ringLen = ring.getTotalLength();
  var markLen = mark.getTotalLength();
  gsap.set(ring, { strokeDasharray: ringLen, strokeDashoffset: ringLen });
  gsap.set(mark, { strokeDasharray: markLen, strokeDashoffset: markLen });

  var tl = gsap.timeline({ defaults: { ease: 'power4.out' } });
  tl.to(ring, { strokeDashoffset: 0, duration: 0.9, ease: 'power2.inOut' })
    .to(mark, { strokeDashoffset: 0, duration: 0.5, ease: 'power2.out' }, '-=0.25')
    .from('.tick', { scale: 0.6, duration: 0.9, ease: 'back.out(2)', transformOrigin: '50% 50%' }, 0)
    .from('.summary__title .line > span', { yPercent: 105, duration: 1, stagger: 0.1 }, 0.25)
    .from('.summary__lede, .ref, .next li, .summary__actions', { autoAlpha: 0, y: 16, duration: 0.7, stagger: 0.06 }, '-=0.7')
    .from('.sheet', { autoAlpha: 0, y: 40, duration: 1, ease: 'expo.out' }, 0.35)
    .from('.sheet__head > *, .block h3, .row, .statement, .sheet__foot',
          { autoAlpha: 0, y: 12, duration: 0.55, stagger: 0.03 }, '-=0.7');
});
