/**
 * HelloPassenger — Hublot theme: scroll-triggered animations
 */
(function () {
  'use strict';

  var ANIMATE_ATTR = 'data-hp-hublot';
  var VISIBLE_CLASS = 'hp-hublot-visible';
  var ANIMATE_CLASS = 'hp-hublot-animate-in';
  var FADE_ONLY_CLASS = 'hp-hublot-fade-only';

  function run() {
    if (!document.body.classList.contains('hp-hublot')) return;

    // Elements that already have data-hp-hublot
    var explicit = document.querySelectorAll('[' + ANIMATE_ATTR + ']');
    var toAnimate = [];

    explicit.forEach(function (el) {
      var kind = (el.getAttribute(ANIMATE_ATTR) || 'fade-up').toLowerCase();
      el.classList.add(ANIMATE_CLASS);
      if (kind === 'fade' || kind === 'fade-in') el.classList.add(FADE_ONLY_CLASS);
      toAnimate.push(el);
    });

    // On home: auto-mark key sections for fade-up (no HTML change needed)
    var pageContent = document.getElementById('page-content');
    if (pageContent) {
      var selectors = [
        '#travel-light',
        '.about-eight__single',
        '.feature-three__single',
        '.elementor-element.elementor-element-69f7d23',  /* Our Process heading */
        '.gva-element-gva-features-block'
      ];
      selectors.forEach(function (sel) {
        var nodes = document.querySelectorAll(sel);
        nodes.forEach(function (el) {
          if (el.closest && !el.closest('[data-hp-hublot]') && toAnimate.indexOf(el) === -1) {
            el.setAttribute(ANIMATE_ATTR, 'fade-up');
            el.classList.add(ANIMATE_CLASS);
            toAnimate.push(el);
          }
        });
      });
    }

    if (toAnimate.length === 0) return;

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add(VISIBLE_CLASS);
          }
        });
      },
      { rootMargin: '0px 0px -8% 0px', threshold: 0.01 }
    );

    toAnimate.forEach(function (el) {
      observer.observe(el);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
})();
