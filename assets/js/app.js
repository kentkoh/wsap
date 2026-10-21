/* WSAP front-end: nav, generator, QR, copy/share, reveals. No build step. */
(function () {
  'use strict';
  const $ = (s, r) => (r || document).querySelector(s);
  const $$ = (s, r) => Array.from((r || document).querySelectorAll(s));

  // ---- mobile nav
  const burger = $('[data-burger]');
  if (burger) {
    burger.addEventListener('click', () => {
      const open = document.body.classList.toggle('nav-open');
      burger.setAttribute('aria-expanded', String(open));
    });
  }

  // ---- products dropdown: tap/click toggles (touch, keyboard, mobile menu); hover still works on desktop
  $$('.nav-dd > a').forEach((a) => {
    a.addEventListener('click', (ev) => {
      const dd = a.parentElement;
      const coarse = window.matchMedia('(hover: none)').matches || window.innerWidth <= 860;
      if (coarse || !dd.classList.contains('open')) { ev.preventDefault(); dd.classList.toggle('open'); }
    });
  });
  document.addEventListener('click', (ev) => { if (!ev.target.closest('.nav-dd')) $$('.nav-dd.open').forEach((d) => d.classList.remove('open')); });

  // ---- reveal on scroll (only hidden when JS runs, so content is never lost)
  document.body.classList.add('js');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((es) => es.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }), { rootMargin: '0px 0px -8% 0px' });
    $$('.reveal').forEach((el) => io.observe(el));
  } else {
    $$('.reveal').forEach((el) => el.classList.add('in'));
  }

  // ---- QR helper (qr-code-styling loaded lazily from jsDelivr)
  let qrLibPromise = null;
  function loadQrLib() {
    if (window.QRCodeStyling) return Promise.resolve();
    if (!qrLibPromise) {
      qrLibPromise = new Promise((res, rej) => {
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/qr-code-styling@1.6.0-rc.1/lib/qr-code-styling.js';
        s.onload = res; s.onerror = rej; document.head.appendChild(s);
      });
    }
    return qrLibPromise;
  }
  window.wsapQr = function (el, data, opts) {
    opts = opts || {};
    return loadQrLib().then(() => {
      el.innerHTML = '';
      const qr = new window.QRCodeStyling({
        width: opts.size || 600, height: opts.size || 600, type: 'canvas', data,
        margin: 8,
        qrOptions: { errorCorrectionLevel: opts.logo ? 'H' : 'M' },
        dotsOptions: { color: opts.color || '#10201a', type: opts.style || 'square' },
        cornersSquareOptions: { color: opts.color || '#10201a', type: opts.corners || (opts.style && opts.style !== 'square' ? 'extra-rounded' : 'square') },
        cornersDotOptions: { color: opts.color || '#10201a' },
        backgroundOptions: { color: opts.bg || '#ffffff' },
        image: opts.logo || undefined,
        imageOptions: { crossOrigin: 'anonymous', margin: 6, imageSize: 0.28 },
      });
      qr.append(el);
      return qr;
    });
  };

  // ---- copy / share
  function copyText(text, btn, done) {
    const ok = () => { if (btn) { const o = btn.textContent; btn.textContent = done || 'Copied!'; setTimeout(() => (btn.textContent = o), 1600); } };
    if (navigator.clipboard) navigator.clipboard.writeText(text).then(ok);
    else { const t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); document.execCommand('copy'); t.remove(); ok(); }
  }
  document.addEventListener('click', (ev) => {
    const c = ev.target.closest('[data-copy]');
    if (c) { ev.preventDefault(); copyText(c.getAttribute('data-copy'), c, c.getAttribute('data-copied')); }
    const s = ev.target.closest('[data-share]');
    if (s && navigator.share) { ev.preventDefault(); navigator.share({ url: s.getAttribute('data-share') }).catch(() => {}); }
    else if (s) { ev.preventDefault(); copyText(s.getAttribute('data-share'), s, s.getAttribute('data-copied')); }
  });

  // ---- free generator (homepage)
  const form = $('#gen-form');
  if (form) {
    const err = $('#gen-error');
    const result = $('#gen-result');
    const btn = $('#gen-submit');
    const t = (k) => (form.dataset[k] || k);
    form.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      err.classList.remove('show');
      btn.disabled = true; const label = btn.textContent; btn.textContent = t('working');
      const fd = new FormData(form);
      try {
        const r = await fetch('/api/create.php', { method: 'POST', body: fd, headers: { 'X-CSRF': form.dataset.csrf } });
        const j = await r.json();
        if (!j.ok) throw new Error(j.error || 'Error');
        showResult(j.link);
      } catch (e) {
        err.textContent = e.message; err.classList.add('show');
      } finally { btn.disabled = false; btn.textContent = label; }
    });
    function showResult(link) {
      $('#res-url').textContent = link.display;
      $('#res-copy').setAttribute('data-copy', link.url);
      $('#res-share').setAttribute('data-share', link.url);
      $('#res-try').href = link.wa_url;
      const meta = $('#res-meta');
      meta.textContent = (link.message ? t('opens') : t('opensNoMsg')).replace('{phone}', '+' + link.phone);
      const b = $('#res-bubble'); b.textContent = link.message || ''; b.style.display = link.message ? '' : 'none';
      const qrEl = $('#res-qr');
      window.wsapQr(qrEl, link.url + '?qr=1', { size: 600 }).then((qr) => {
        $('#res-dl').onclick = (e) => { e.preventDefault(); qr.download({ name: 'wsap-' + link.code, extension: 'png' }); };
      });
      const save = $('#res-save'); if (save) save.href = '/signup?claim=1';
      result.classList.add('show');
      form.closest('.gen-card').classList.add('has-result');
      $('#gen-fields').style.display = 'none';
      result.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    const again = $('#res-again');
    if (again) again.addEventListener('click', (e) => { e.preventDefault(); result.classList.remove('show'); $('#gen-fields').style.display = ''; form.closest('.gen-card').classList.remove('has-result'); });
  }

  // ---- phone input: digits only, drop leading zero hint
  $$('input[data-phone]').forEach((inp) => {
    inp.addEventListener('input', () => { inp.value = inp.value.replace(/[^\d]/g, ''); });
  });

  // ---- live handle preview (link editor)
  const handle = $('#handle');
  if (handle) {
    const prev = $('#handle-preview');
    handle.addEventListener('input', () => { handle.value = handle.value.toLowerCase().replace(/[^a-z0-9-]/g, ''); if (prev) prev.textContent = handle.value || '······'; });
  }

  // ---- local time rendering
  $$('time[data-utc]').forEach((el) => {
    const d = new Date(el.getAttribute('data-utc').replace(' ', 'T') + 'Z');
    if (!isNaN(d)) el.textContent = d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
  });

  // ---- confirm
  $$('[data-confirm]').forEach((f) => f.addEventListener('submit', (e) => { if (!confirm(f.getAttribute('data-confirm'))) e.preventDefault(); }));
})();
