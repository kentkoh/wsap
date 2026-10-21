/* Auto-select the visitor's country in phone pickers.
   Order: server hint (data-geo on <html>) → cached result → timezone → browser locale → ipapi.co (2s timeout).
   Only applies to selects the user has not touched. */
(function () {
  'use strict';
  var selects = Array.prototype.slice.call(document.querySelectorAll('select[name="cc"]'));
  if (!selects.length) return;
  var touched = false;
  selects.forEach(function (s) { s.addEventListener('change', function () { touched = true; }); });

  var TZ = { 'Asia/Kuala_Lumpur': 'MY', 'Asia/Kuching': 'MY', 'Asia/Singapore': 'SG', 'Asia/Jakarta': 'ID', 'Asia/Makassar': 'ID', 'Asia/Jayapura': 'ID', 'Asia/Brunei': 'BN', 'Asia/Bangkok': 'TH', 'Asia/Manila': 'PH', 'Asia/Ho_Chi_Minh': 'VN', 'Asia/Saigon': 'VN', 'Asia/Kolkata': 'IN', 'Asia/Calcutta': 'IN', 'Asia/Dubai': 'AE', 'Asia/Hong_Kong': 'HK', 'Asia/Tokyo': 'JP', 'Asia/Seoul': 'KR', 'Asia/Taipei': 'TW', 'Asia/Shanghai': 'CN', 'Asia/Dhaka': 'BD', 'Asia/Karachi': 'PK', 'Asia/Kathmandu': 'NP', 'Asia/Colombo': 'LK', 'Asia/Phnom_Penh': 'KH', 'Asia/Yangon': 'MM', 'Asia/Riyadh': 'SA', 'Asia/Qatar': 'QA', 'Europe/London': 'GB', 'Europe/Lisbon': 'PT', 'Europe/Madrid': 'ES', 'Europe/Paris': 'FR', 'Europe/Berlin': 'DE', 'Europe/Rome': 'IT', 'Europe/Amsterdam': 'NL', 'Europe/Istanbul': 'TR', 'Africa/Lagos': 'NG', 'Africa/Nairobi': 'KE', 'Africa/Cairo': 'EG', 'Africa/Johannesburg': 'ZA', 'Australia/Sydney': 'AU', 'Australia/Melbourne': 'AU', 'Australia/Perth': 'AU', 'Australia/Brisbane': 'AU', 'Pacific/Auckland': 'NZ', 'America/Sao_Paulo': 'BR', 'America/Mexico_City': 'MX', 'America/Bogota': 'CO', 'America/Toronto': 'CA', 'America/Vancouver': 'CA' };
  var DIAL = {};
  selects[0].querySelectorAll('option').forEach(function (o) { var iso = o.textContent.trim().split(' ')[0]; DIAL[iso] = o.value; });

  function apply(iso) {
    if (touched || !iso || !DIAL[iso]) return false;
    selects.forEach(function (s) { if (s.value !== DIAL[iso]) { s.value = DIAL[iso]; s.dispatchEvent(new Event('change', { bubbles: true })); touched = false; } });
    try { localStorage.setItem('wsap_geo', JSON.stringify({ iso: iso, t: Date.now() })); } catch (e) {}
    return true;
  }
  // 1. server hint (CDN / proxy header)
  var hint = document.documentElement.getAttribute('data-geo');
  if (hint && apply(hint)) return;
  // 2. cache (7 days)
  try { var c = JSON.parse(localStorage.getItem('wsap_geo') || 'null'); if (c && c.iso && Date.now() - c.t < 7 * 864e5 && apply(c.iso)) return; } catch (e) {}
  // 3. timezone
  var tz = ''; try { tz = Intl.DateTimeFormat().resolvedOptions().timeZone || ''; } catch (e) {}
  if (TZ[tz] && apply(TZ[tz])) return;
  // 4. browser locale region (e.g. ms-MY, en-SG)
  var langs = navigator.languages || [navigator.language || ''];
  for (var i = 0; i < langs.length; i++) { var m = /[-_]([A-Za-z]{2})$/.exec(langs[i] || ''); if (m && apply(m[1].toUpperCase())) return; }
  // 5. IP lookup (best effort)
  if (window.fetch && window.AbortController) {
    var ctl = new AbortController(); setTimeout(function () { ctl.abort(); }, 2000);
    fetch('https://ipapi.co/country/', { signal: ctl.signal }).then(function (r) { return r.ok ? r.text() : ''; }).then(function (t) { apply((t || '').trim().toUpperCase()); }).catch(function () {});
  }
})();
