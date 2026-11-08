/* Seven.com : animations légères (apparition, compteurs, en-tête). Rien ne bouge si l'utilisateur a demandé « réduire les animations ». */
(function () {
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var root = document.documentElement;
  if (reduce || !('IntersectionObserver' in window)) { root.classList.add('no-anim'); return; }
  root.classList.add('anim');

  // Titres découpés en mots (montée derrière un masque) et verset mot à mot
  [].forEach.call(document.querySelectorAll('main section > h2'), function (h) {
    if (h.closest('.hero') || h.children.length) return;
    var words = h.textContent.trim().split(/\s+/);
    h.setAttribute('aria-label', h.textContent.trim());
    h.innerHTML = words.map(function (w, k) { return '<span class="w" aria-hidden="true"><span style="--k:' + k + '">' + w.replace(/</g, '&lt;') + '</span></span>'; }).join(' ');
    h.classList.add('split');
  });
  [].forEach.call(document.querySelectorAll('.verse blockquote'), function (q) {
    var words = q.textContent.trim().split(/\s+/);
    q.setAttribute('aria-label', q.textContent.trim());
    q.innerHTML = words.map(function (w, k) { return '<span class="vw" aria-hidden="true" style="--k:' + k + '">' + w.replace(/</g, '&lt;') + '</span>'; }).join(' ');
  });

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

  // Défilement : en-tête qui se resserre, logo qui recule dans la profondeur, bandeau qui s'incline avec la vitesse
  var header = document.querySelector('header.site'), hero = document.querySelector('.portal .hero');
  var marq = document.querySelector('.marquee-track'), lastY = window.scrollY, skew = 0, ticking = false;
  var recede = hero && hero.querySelector('.hero-logo-wrap');
  if (recede) hero.classList.add('hero-scroll');
  function frame() {
    ticking = false;
    var y = window.scrollY;
    if (header) header.classList.toggle('scrolled', y > 40);
    if (recede) {
      var h = hero.offsetHeight || 1, k = Math.min(Math.max(-hero.getBoundingClientRect().top / h, 0), 1);
      hero.style.setProperty('--hz', (-k * 420).toFixed(1) + 'px');
      hero.style.setProperty('--hy', (k * h * 0.12).toFixed(1) + 'px');
      hero.style.setProperty('--hr', (k * 10).toFixed(2) + 'deg');
      hero.style.setProperty('--ho', (1 - k * 1.7).toFixed(3));
    }
    if (marq) {
      var v = y - lastY; skew += (Math.max(-12, Math.min(12, -v * 0.35)) - skew) * 0.2;
      marq.style.setProperty('--skew', skew.toFixed(2) + 'deg');
      if (Math.abs(skew) > 0.05) { ticking = true; requestAnimationFrame(frame); }
    }
    lastY = y;
  }
  window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(frame); } }, { passive: true });
  frame();

  // Cartes : reflet qui suit la souris + légère inclinaison 3D ; boutons magnétiques
  if (window.matchMedia('(hover: hover)').matches) {
    [].forEach.call(document.querySelectorAll('main .card'), function (c) {
      c.addEventListener('mousemove', function (e) {
        var r = c.getBoundingClientRect(), x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
        c.style.setProperty('--mx', (x * 100) + '%'); c.style.setProperty('--my', (y * 100) + '%');
        if (c.tagName === 'A') c.style.transform = 'perspective(800px) translateY(-6px) rotateX(' + ((.5 - y) * 8).toFixed(2) + 'deg) rotateY(' + ((x - .5) * 10).toFixed(2) + 'deg)';
      });
      c.addEventListener('mouseleave', function () { c.style.transform = ''; });
    });
    [].forEach.call(document.querySelectorAll('main .btn'), function (b) {
      b.addEventListener('mousemove', function (e) {
        var r = b.getBoundingClientRect(), x = e.clientX - r.left - r.width / 2, y = e.clientY - r.top - r.height / 2;
        b.style.transform = 'translate(' + (x * .18).toFixed(1) + 'px,' + (y * .3).toFixed(1) + 'px)';
      });
      b.addEventListener('mouseleave', function () { b.style.transform = ''; });
    });
  }

  // Accueil : objets qui sortent du fond de l'écran, derrière tout le contenu
  if (hero) {
    var bg = document.createElement('div'); bg.className = 'depth-bg'; bg.setAttribute('aria-hidden', 'true');
    var html = '';
    for (var k = 0; k < 12; k++) {
      var a = k * 2.399, r = 30 + (k * 37) % 40, dot = k % 3 === 2;
      html += '<i class="' + (dot ? 'dot' : '') + '" style="--x:' + Math.round(Math.cos(a) * r) + 'vw;--y:' + Math.round(Math.sin(a) * r * .7) + 'vh;--s:' + (dot ? 14 + k % 4 * 6 : 40 + (k * 13) % 50) + 'px;--d:' + (11 + (k * 7) % 9) + 's;--w:-' + (k * 1.6).toFixed(1) + 's;--r:' + ((k * 29) % 60 - 30) + 'deg"></i>';
    }
    bg.innerHTML = html; document.body.insertBefore(bg, document.body.firstChild);
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

/* Diaporama 3D en profondeur : chaque diapo arrive du fond de l'écran en zoomant, puis passe devant le visiteur et disparaît. */
(function () {
  var box = document.querySelector('.carousel3d');
  if (!box) return;
  var slides = [].slice.call(box.querySelectorAll('.c3d-slide')), n = slides.length, cur = 0;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var states = ['is-front', 'is-next', 'is-next2', 'is-past', 'is-prev'];

  function apply() {
    slides.forEach(function (s, i) {
      var d = ((i - cur) % n + n) % n;
      states.forEach(function (c) { s.classList.remove(c); });
      if (d === 0) s.classList.add('is-front');
      else if (d === 1) s.classList.add('is-next');
      else if (d === 2) s.classList.add('is-next2');
      else if (d === n - 1) s.classList.add('is-past');
      s.tabIndex = d === 0 ? 0 : -1;
      s.setAttribute('aria-hidden', d === 0 ? 'false' : 'true');
    });
  }
  function go(k) { cur = ((k % n) + n) % n; apply(); }
  function next() { go(cur + 1); } function prev() { go(cur - 1); }

  box.querySelector('.c3d-next').addEventListener('click', function () { next(); restart(); });
  box.querySelector('.c3d-prev').addEventListener('click', function () { prev(); restart(); });
  slides.forEach(function (s, i) { s.addEventListener('click', function (e) { if (i !== cur) { e.preventDefault(); go(i); restart(); } }); });

  // Glisser au doigt
  var x0 = null;
  box.addEventListener('pointerdown', function (e) { x0 = e.clientX; });
  box.addEventListener('pointerup', function (e) {
    if (x0 === null) return; var dx = e.clientX - x0; x0 = null;
    if (Math.abs(dx) > 40) { dx < 0 ? next() : prev(); restart(); }
  });
  box.addEventListener('keydown', function (e) { if (e.key === 'ArrowRight') { next(); restart(); } if (e.key === 'ArrowLeft') { prev(); restart(); } });

  // Inclinaison de toute la scène selon la souris
  var stage = box.querySelector('.c3d-stage');
  if (!reduce && window.matchMedia('(hover: hover)').matches) {
    box.addEventListener('mousemove', function (e) {
      var r = box.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
      stage.style.setProperty('--ry', (x * 10).toFixed(2) + 'deg'); stage.style.setProperty('--rx', (-y * 8).toFixed(2) + 'deg');
    });
    box.addEventListener('mouseleave', function () { stage.style.setProperty('--ry', '0deg'); stage.style.setProperty('--rx', '0deg'); });
  }

  var timer = null;
  function start() { if (!reduce && !timer) timer = setInterval(next, 4200); }
  function stop() { clearInterval(timer); timer = null; }
  function restart() { stop(); start(); }
  document.addEventListener('visibilitychange', function () { document.hidden ? stop() : start(); });
  apply(); start();
})();

/* Frise des supports audio : défilement vertical -> voyage en profondeur d'un support à l'autre.
   L'année géante compte d'une date à l'autre, le bras de lecture avance, et les disques tournent plus vite quand on défile vite. */
(function () {
  var box = document.querySelector('.audio-history');
  if (!box || document.documentElement.classList.contains('no-anim')) return;
  var pin = box.querySelector('.ah-pin'), items = [].slice.call(box.querySelectorAll('.ah-item')), ticks = [].slice.call(box.querySelectorAll('.ah-tick'));
  var yearEl = box.querySelector('.ah-year'), axis = box.querySelector('.ah-axis'), n = items.length;
  var years = items.map(function (it) { return parseInt(it.querySelector('.ah-date').firstChild.nodeValue, 10); });
  var last = -1, lastY = window.scrollY, speed = 1, raf = null;
  function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }
  function render() {
    raf = null;
    var r = pin.getBoundingClientRect(), total = pin.offsetHeight - window.innerHeight;
    var p = clamp(-r.top / total, 0, 1), f = p * (n - 1);
    var narrow = window.innerWidth < 700;
    items.forEach(function (it, i) {
      var d = i - f, ad = Math.abs(d);
      var x = d * (narrow ? 70 : 58), z = -ad * 520, ry = clamp(-d * 32, -60, 60), sc = 1 - Math.min(ad, 2) * 0.06;
      it.style.transform = 'perspective(1300px) translate3d(' + x + '%,0,' + z + 'px) rotateY(' + ry + 'deg) scale(' + sc + ')';
      it.style.opacity = clamp(1 - ad * 0.8, 0, 1).toFixed(3);
      it.style.filter = ad > 0.05 ? 'blur(' + Math.min(ad * 5, 8).toFixed(1) + 'px)' : 'none';
      it.style.zIndex = 100 - Math.round(ad * 10);
      it.style.visibility = ad > 1.6 ? 'hidden' : 'visible';
    });
    var k = Math.round(f);
    if (k !== last) { items.forEach(function (it, i) { it.classList.toggle('on', i === k); }); ticks.forEach(function (t, i) { t.classList.toggle('on', i === k); }); last = k; }
    var a = Math.floor(f), b = Math.min(a + 1, n - 1), t = f - a, eased = t * t * (3 - 2 * t);
    yearEl.textContent = Math.round(years[a] + (years[b] - years[a]) * eased);
    yearEl.style.transform = 'translate3d(' + (-(t - .5) * 6).toFixed(2) + '%,0,0) scale(' + (1 + Math.sin(t * Math.PI) * 0.06).toFixed(3) + ')';
    axis.style.setProperty('--p', p.toFixed(4));
    // Vitesse de défilement -> vitesse de rotation des disques et bobines
    var v = Math.abs(window.scrollY - lastY); lastY = window.scrollY;
    speed += (clamp(1 + v * 0.08, 1, 7) - speed) * 0.25;
    if (box.getAnimations) box.getAnimations({ subtree: true }).forEach(function (an) { an.playbackRate = speed; });
    if (speed > 1.02) raf = requestAnimationFrame(render);
  }
  box.style.setProperty('--n', n);
  window.addEventListener('scroll', function () { if (!raf) raf = requestAnimationFrame(render); }, { passive: true });
  window.addEventListener('resize', render);
  render();
})();
