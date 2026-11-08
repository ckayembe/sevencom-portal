/* Seven.com : animations légères (apparition, compteurs, en-tête). Rien ne bouge si l'utilisateur a demandé « réduire les animations ». */
(function () {
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var root = document.documentElement;
  if (reduce || !('IntersectionObserver' in window)) { root.classList.add('no-anim'); return; }
  root.classList.add('anim');

  // Éléments qui apparaissent au défilement, avec un léger décalage dans les grilles
  var sel = 'main section > h2, main section > h1, main section > p, main section > .lead, main .grid > *, main .figure, main .steps > li, main .verse, main .artist-photo, main .doc > *:not(.contract)';
  var items = [].slice.call(document.querySelectorAll(sel)).filter(function (el) { return !el.closest('.hero') && !el.closest('.contract'); });
  items.forEach(function (el) {
    el.classList.add('reveal');
    var p = el.parentElement;
    if (p && (p.classList.contains('grid') || p.classList.contains('figures') || p.classList.contains('steps'))) {
      el.style.transitionDelay = Math.min([].indexOf.call(p.children, el), 6) * 90 + 'ms';
    }
  });
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (!e.isIntersecting) return;
      e.target.classList.add('in');
      if (e.target.classList.contains('figure')) count(e.target.querySelector('strong'));
      io.unobserve(e.target);
    });
  }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });
  items.forEach(function (el) { io.observe(el); });

  // Chiffres clés qui comptent : « 150K+ » -> de 0 à 150, suffixe conservé
  function count(el) {
    if (!el) return;
    var m = el.textContent.trim().match(/^(\D*)(\d+(?:[.,]\d+)?)(.*)$/);
    if (!m) return;
    var target = parseFloat(m[2].replace(',', '.')), dec = (m[2].split(/[.,]/)[1] || '').length, sep = m[2].indexOf(',') > -1 ? ',' : '.';
    var t0 = null, dur = 1400;
    function step(t) {
      t0 = t0 || t;
      var k = Math.min((t - t0) / dur, 1), v = target * (1 - Math.pow(1 - k, 3));
      el.textContent = m[1] + v.toFixed(dec).replace('.', sep) + m[3];
      if (k < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  // En-tête qui se resserre au défilement
  var header = document.querySelector('header.site');
  if (header) {
    var onScroll = function () { header.classList.toggle('scrolled', window.scrollY > 40); };
    window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
  }

  // Mots qui défilent sous le logo
  var rot = document.querySelector('.rotator');
  if (rot) {
    var words = rot.getAttribute('data-words').split('|'), i = 0;
    setInterval(function () {
      rot.classList.add('out');
      setTimeout(function () { i = (i + 1) % words.length; rot.textContent = words[i]; rot.classList.remove('out'); }, 350);
    }, 2600);
  }

  // Logo qui suit légèrement la souris (relief)
  var logo = document.querySelector('.hero-logo-wrap');
  if (logo && window.matchMedia('(hover: hover)').matches) {
    document.querySelector('.hero').addEventListener('mousemove', function (e) {
      var r = logo.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
      logo.style.transform = 'perspective(900px) rotateY(' + (x * 8).toFixed(2) + 'deg) rotateX(' + (-y * 8).toFixed(2) + 'deg)';
    });
    document.querySelector('.hero').addEventListener('mouseleave', function () { logo.style.transform = ''; });
  }
})();
