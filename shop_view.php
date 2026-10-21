<?php
declare(strict_types=1);
/** /s/{slug} — public shop: catalogue, cart, checkout → WhatsApp order message. */
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/shops.php';
require __DIR__ . '/lib/ua.php';

header('Cache-Control: no-store');
$shop = find_shop((string) ($_GET['s'] ?? ''));
$owner = $shop ? db_row('SELECT * FROM users WHERE id = ?', [(int) $shop['user_id']]) : null;
$viewer = current_user();
$isOwner = $shop && $viewer && (int) $viewer['id'] === (int) $shop['user_id'];
$preview = $isOwner && isset($_GET['preview']);
$site = (string) cfg('site_name');

if (!$shop || (!shop_is_published($shop, $owner) && !$preview)) {
    http_response_code(404);
    echo '<!doctype html><html lang="' . lang() . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . e(t('sh_404_t')) . '</title><link rel="stylesheet" href="/assets/css/app.css"></head><body><div class="open-page"><div class="open-card"><h1>' . e(t('sh_404_t')) . '</h1><p>' . e(t('sh_404_d')) . '</p><a class="btn btn-primary" href="/">' . e($site) . '</a></div></div></body></html>';
    exit;
}
$info = click_info();
if (!$preview && !$info['is_bot']) {
    db_exec('UPDATE shops SET views = views + 1 WHERE id = ?', [(int) $shop['id']]);
}

$products = shop_products((int) $shop['id'], true);
$cats = [];
foreach ($products as $p) {
    $cats[$p['category'] !== '' ? $p['category'] : t('sh_cat_other')][] = $p;
}
$fields = shop_fields($shop);
$shipping = shop_shipping($shop);
$cur = $shop['currency'];
$open = (int) $shop['is_open'] === 1;
$catalog = array_map(fn($p) => ['id' => (int) $p['id'], 'name' => $p['name'], 'price' => (int) $p['price'], 'image' => $p['image'], 'description' => $p['description'], 'variants' => product_variants($p)], $products);
?>
<!doctype html>
<html lang="<?= lang() ?>">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($shop['name']) ?></title>
<meta name="description" content="<?= e($shop['tagline']) ?>">
<meta property="og:title" content="<?= e($shop['name']) ?>"><meta property="og:description" content="<?= e($shop['tagline']) ?>"><?php if ($shop['cover'] !== ''): ?><meta property="og:image" content="<?= e(str_starts_with($shop['cover'], 'http') ? $shop['cover'] : base_url() . $shop['cover']) ?>"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
<style>
  :root{--acc:<?= e($shop['accent']) ?>}
  body{background:#f4f3ee}
  .spage{max-width:980px;margin:0 auto;padding:0 0 110px}
  .scover{height:clamp(140px,28vw,260px);background:var(--acc);background-size:cover;background-position:center;border-radius:0 0 24px 24px}
  .shead{display:flex;gap:16px;align-items:flex-end;padding:0 20px;margin-top:-36px}
  .slogo{width:84px;height:84px;border-radius:20px;background:#fff;box-shadow:var(--shadow-1);overflow:hidden;flex:none;display:grid;place-items:center;font-weight:800;font-size:28px;color:var(--acc)}
  .slogo img{width:100%;height:100%;object-fit:cover}
  .shead h1{font-size:clamp(22px,4vw,30px);margin-bottom:2px}.shead p{margin:0;color:var(--ink-2);font-size:14.5px}
  .sstatus{display:inline-flex;align-items:center;gap:6px;font-size:12.5px;font-weight:700;padding:4px 10px;border-radius:999px;background:#e6f4ec;color:#0b3d2e;margin-top:6px}.sstatus.off{background:#fdecec;color:#b4232c}
  .sstatus::before{content:"";width:8px;height:8px;border-radius:50%;background:currentColor}
  .sabout{padding:14px 20px 0;color:var(--ink-2);font-size:14.5px;max-width:70ch}
  .scats{display:flex;gap:8px;overflow-x:auto;padding:16px 20px 4px;scrollbar-width:none}.scats a{white-space:nowrap;padding:8px 14px;border-radius:999px;background:#fff;border:1px solid var(--line);font-weight:600;font-size:14px;color:var(--ink-2)}.scats a:hover{text-decoration:none;border-color:var(--acc);color:var(--acc)}
  .scat{padding:18px 20px 0}.scat h2{font-size:18px;margin-bottom:12px}
  .sgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px}
  .sp{background:#fff;border:1px solid var(--line);border-radius:16px;overflow:hidden;cursor:pointer;transition:transform .2s var(--ease),box-shadow .2s}.sp:hover{transform:translateY(-2px);box-shadow:var(--shadow-1)}
  .sp .img{aspect-ratio:1;background:var(--paper-2);display:grid;place-items:center;font-size:32px}.sp .img img{width:100%;height:100%;object-fit:cover}
  .sp .b{padding:10px 12px 12px}.sp .n{font-weight:700;font-size:14.5px;line-height:1.3}.sp .pr{color:var(--acc);font-weight:800;margin-top:4px}
  .cartbar{position:fixed;left:0;right:0;bottom:0;padding:12px 16px calc(12px + env(safe-area-inset-bottom));background:rgba(244,243,238,.9);backdrop-filter:blur(10px);border-top:1px solid var(--line);display:none;justify-content:center}
  .cartbar.show{display:flex}.cartbar .btn{background:var(--acc);color:#fff;width:100%;max-width:520px;justify-content:space-between}
  .modal{position:fixed;inset:0;background:rgba(16,32,26,.5);display:none;align-items:flex-end;justify-content:center;z-index:60}.modal.show{display:flex}
  .sheet{background:#fff;width:100%;max-width:560px;max-height:92vh;overflow:auto;border-radius:22px 22px 0 0;padding:20px 20px 28px}
  @media(min-width:600px){.modal{align-items:center}.sheet{border-radius:22px}}
  .sheet h3{font-size:20px;margin-bottom:6px}.sheet .close{float:right;border:0;background:var(--paper-2);width:34px;height:34px;border-radius:50%;cursor:pointer;font-size:16px}
  .vgroup{margin:14px 0}.vgroup .label{font-weight:700;font-size:14px;margin-bottom:6px;display:block}
  .vopt{display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border:1.5px solid var(--line-2);border-radius:12px;margin-bottom:6px;cursor:pointer;font-size:14.5px}.vopt:has(input:checked){border-color:var(--acc);background:rgba(0,0,0,.03)}.vopt input{margin-right:8px}
  .qty{display:inline-flex;align-items:center;border:1.5px solid var(--line-2);border-radius:12px;overflow:hidden}.qty button{border:0;background:#fff;width:40px;height:40px;font-size:20px;cursor:pointer}.qty span{min-width:36px;text-align:center;font-weight:700}
  .addrow{display:flex;gap:12px;align-items:center;margin-top:16px}.addrow .btn{flex:1;background:var(--acc);color:#fff}
  .cline{display:grid;grid-template-columns:1fr auto auto;gap:10px;align-items:center;padding:10px 0;border-bottom:1px solid var(--line);font-size:14.5px}.cline small{display:block;color:var(--ink-3)}
  .tot{display:flex;justify-content:space-between;padding:6px 0;font-size:14.5px}.tot.big{font-weight:800;font-size:18px;border-top:1px solid var(--line);margin-top:6px;padding-top:12px}
  .sfoot{text-align:center;font-size:12.5px;color:var(--ink-3);padding:30px 20px 0}.sfoot a{color:inherit}
  .fwarn{background:var(--warn-bg);color:#6a4a00;padding:10px 14px;border-radius:10px;font-size:14px;margin:12px 20px 0}
</style>
</head>
<body>
<div class="spage">
  <?php if ($preview): ?><div class="fwarn"><?= e(t('sh_preview_note')) ?></div><?php endif; ?>
  <div class="scover" <?= $shop['cover'] !== '' ? 'style="background-image:url(' . e($shop['cover']) . ')"' : '' ?>></div>
  <div class="shead">
    <div class="slogo"><?php if ($shop['logo'] !== ''): ?><img src="<?= e($shop['logo']) ?>" alt=""><?php else: ?><?= e(mb_strtoupper(mb_substr($shop['name'], 0, 1))) ?><?php endif; ?></div>
    <div><h1><?= e($shop['name']) ?></h1><?php if ($shop['tagline'] !== ''): ?><p><?= e($shop['tagline']) ?></p><?php endif; ?>
      <span class="sstatus <?= $open ? '' : 'off' ?>"><?= e($open ? t('sh_open_now') : t('sh_closed_now')) ?></span></div>
  </div>
  <?php if ($shop['about'] !== ''): ?><p class="sabout"><?= nl2br(e($shop['about'])) ?></p><?php endif; ?>
  <?php if (count($cats) > 1): ?><nav class="scats"><?php foreach (array_keys($cats) as $i => $c): ?><a href="#cat<?= $i ?>"><?= e($c) ?></a><?php endforeach; ?></nav><?php endif; ?>
  <?php if (!$products): ?><p class="sabout"><?= e(t('sh_no_products')) ?></p><?php endif; ?>
  <?php foreach (array_values($cats) as $i => $list): ?>
  <section class="scat" id="cat<?= $i ?>"><?php if (count($cats) > 1): ?><h2><?= e(array_keys($cats)[$i]) ?></h2><?php endif; ?>
    <div class="sgrid">
      <?php foreach ($list as $p): ?>
      <article class="sp" data-id="<?= (int) $p['id'] ?>" tabindex="0">
        <div class="img"><?php if ($p['image'] !== ''): ?><img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy"><?php else: ?>🛍️<?php endif; ?></div>
        <div class="b"><div class="n"><?= e($p['name']) ?></div><div class="pr"><?= e(money((int) $p['price'], $cur)) ?><?= product_variants($p) ? '+' : '' ?></div></div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endforeach; ?>
  <div class="sfoot"><?= e(t('open_pow')) ?> <a href="/"><?= e($site) ?></a></div>
</div>

<div class="cartbar" id="cartbar"><button class="btn btn-lg" id="open-cart" type="button"><span><?= e(t('sh_view_cart')) ?> · <span id="cart-count">0</span></span><b id="cart-total"></b></button></div>

<div class="modal" id="pm"><div class="sheet" id="pm-body"></div></div>
<div class="modal" id="cm"><div class="sheet">
  <button class="close" type="button" data-close="cm">✕</button><h3><?= e(t('sh_your_order')) ?></h3>
  <div id="cart-lines"></div>
  <form id="checkout" method="post" action="/api/shop_order.php" style="margin-top:14px">
    <input type="hidden" name="shop" value="<?= (int) $shop['id'] ?>"><input type="hidden" name="cart" id="cart-json"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><?php if ($preview): ?><input type="hidden" name="preview" value="1"><?php endif; ?>
    <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
    <?php if ($shipping): ?><div class="vgroup"><span class="label"><?= e(t('sh_delivery')) ?></span>
      <?php foreach ($shipping as $i => $m): ?><label class="vopt"><span><input type="radio" name="shipping" value="<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?> data-price="<?= (int) $m['price'] ?>"><?= e($m['name']) ?><?= $m['note'] !== '' ? ' <small style="color:var(--ink-3)">· ' . e($m['note']) . '</small>' : '' ?></span><b><?= (int) $m['price'] > 0 ? e(money((int) $m['price'], $cur)) : e(t('sh_free')) ?></b></label><?php endforeach; ?></div><?php endif; ?>
    <?php foreach (['name', 'phone', 'address', 'city', 'notes'] as $f): if ($fields[$f] === 'off') { continue; } ?>
    <div class="field" style="margin-bottom:12px"><label for="c-<?= $f ?>"><?= e(t('sh_f_' . $f)) ?><?= $fields[$f] === 'req' ? ' *' : '' ?></label>
      <?php if ($f === 'address' || $f === 'notes'): ?><textarea class="input" id="c-<?= $f ?>" name="<?= $f ?>" style="min-height:60px" <?= $fields[$f] === 'req' ? 'required' : '' ?>></textarea><?php else: ?><input class="input" id="c-<?= $f ?>" name="<?= $f ?>" type="<?= $f === 'phone' ? 'tel' : 'text' ?>" <?= $fields[$f] === 'req' ? 'required' : '' ?>><?php endif; ?></div>
    <?php endforeach; ?>
    <?php if (shop_coupons($shop)): ?><div class="field" style="margin-bottom:12px"><label for="c-coupon"><?= e(t('sh_coupon')) ?></label><div style="display:flex;gap:8px"><input class="input" id="c-coupon" name="coupon" style="text-transform:uppercase"><button class="btn btn-ghost" type="button" id="apply-coupon"><?= e(t('sh_apply')) ?></button></div><p class="hint" id="coupon-msg"></p></div><?php endif; ?>
    <div class="tot"><span><?= e(t('sh_msg_subtotal')) ?></span><b id="t-sub"></b></div>
    <div class="tot" id="t-ship-row"><span><?= e(t('sh_msg_shipping')) ?></span><b id="t-ship"></b></div>
    <div class="tot" id="t-disc-row" style="display:none"><span><?= e(t('sh_msg_discount')) ?></span><b id="t-disc"></b></div>
    <div class="tot big"><span><?= e(t('sh_msg_total')) ?></span><b id="t-total"></b></div>
    <?php if ($shop['payment_note'] !== ''): ?><p class="hint" style="margin-top:10px"><?= nl2br(e($shop['payment_note'])) ?></p><?php endif; ?>
    <p class="flash flash-error" id="co-error" style="display:none;margin:10px 0 0"></p>
    <?php if ($open): ?><button class="btn btn-wa btn-lg btn-block" type="submit" style="margin-top:14px"><?= e(t('sh_send_order')) ?> →</button><?php else: ?><div class="locked" style="margin-top:14px"><?= e(t('sh_closed_msg')) ?></div><?php endif; ?>
  </form>
</div></div>

<script>
(function(){
  var P = <?= json_encode($catalog, JSON_UNESCAPED_UNICODE) ?>, CUR = <?= json_encode($cur) ?>, COUPONS = <?= json_encode(array_map(fn($c) => ['code' => strtoupper($c['code']), 'type' => $c['type'], 'value' => (float) $c['value']], shop_coupons($shop))) ?>;
  var T = {add:<?= json_encode(t('sh_add_to_cart')) ?>, remove:<?= json_encode(t('sh_remove')) ?>, free:<?= json_encode(t('sh_free')) ?>, choose:<?= json_encode(t('sh_choose')) ?>, badCoupon:<?= json_encode(t('sh_bad_coupon')) ?>, okCoupon:<?= json_encode(t('sh_ok_coupon')) ?>, err:<?= json_encode(t('err_generic')) ?>};
  var SYM = {MYR:'RM',SGD:'S$',USD:'$',IDR:'Rp',THB:'฿',PHP:'₱',BND:'B$',AUD:'A$',GBP:'£',EUR:'€'};
  function money(s){ var v=s/100; return (SYM[CUR]||CUR+' ')+(v%1===0?v.toFixed(0):v.toFixed(2)); }
  var cart = []; try { cart = JSON.parse(sessionStorage.getItem('wsap_cart_<?= (int) $shop['id'] ?>')||'[]'); } catch(e){}
  var coupon = {code:'', discount:0};
  var $ = function(s){ return document.querySelector(s); };
  function byId(id){ return P.find(function(p){ return p.id===id; }); }
  function unitPrice(p, options){ var u=p.price; p.variants.forEach(function(v){ var sel=options[v.name]||[]; v.options.forEach(function(o){ if(sel.indexOf(o.label)>=0) u+=o.extra; }); }); return u; }
  function save(){ try{ sessionStorage.setItem('wsap_cart_<?= (int) $shop['id'] ?>', JSON.stringify(cart)); }catch(e){} }
  function subtotal(){ return cart.reduce(function(s,l){ var p=byId(l.id); return p? s+unitPrice(p,l.options)*l.qty : s; },0); }
  function shipPrice(){ var r=document.querySelector('input[name=shipping]:checked'); return r? parseInt(r.dataset.price,10):0; }
  function renderBar(){ var n=cart.reduce(function(s,l){return s+l.qty},0); $('#cartbar').classList.toggle('show', n>0); $('#cart-count').textContent=n; $('#cart-total').textContent=money(subtotal()); }
  function renderCart(){ var el=$('#cart-lines'); el.innerHTML=''; cart.forEach(function(l,i){ var p=byId(l.id); if(!p) return; var opts=Object.keys(l.options).map(function(k){ return k+': '+l.options[k].join(', '); }).join(' · '); var d=document.createElement('div'); d.className='cline'; d.innerHTML='<div><b></b><small></small></div><div class="qty"><button type="button" data-d="-1">−</button><span>'+l.qty+'</span><button type="button" data-d="1">+</button></div><b>'+money(unitPrice(p,l.options)*l.qty)+'</b>'; d.querySelector('b').textContent=p.name; d.querySelector('small').textContent=opts; d.querySelectorAll('[data-d]').forEach(function(b){ b.addEventListener('click', function(){ l.qty+=parseInt(b.dataset.d,10); if(l.qty<=0) cart.splice(i,1); save(); renderCart(); renderBar(); }); }); el.appendChild(d); });
    var sub=subtotal(); applyCoupon(false); var ship=shipPrice(); var disc=coupon.discount; $('#t-sub').textContent=money(sub); $('#t-ship').textContent=ship?money(ship):T.free; $('#t-disc-row').style.display=disc?'':'none'; $('#t-disc').textContent='−'+money(disc); $('#t-total').textContent=money(Math.max(0,sub+ship-disc)); $('#cart-json').value=JSON.stringify(cart); }
  function applyCoupon(announce){ var inp=$('#c-coupon'); if(!inp){ coupon={code:'',discount:0}; return; } var code=inp.value.trim().toUpperCase(); var c=COUPONS.find(function(x){return x.code===code;}); var sub=subtotal(); if(c){ coupon={code:code, discount:Math.min(sub, c.type==='fixed'? Math.round(c.value): Math.round(sub*c.value/100))}; if(announce) $('#coupon-msg').textContent=T.okCoupon+' −'+money(coupon.discount); } else { coupon={code:'',discount:0}; if(announce) $('#coupon-msg').textContent= code? T.badCoupon : ''; } }
  document.querySelectorAll('.sp').forEach(function(card){ card.addEventListener('click', function(){ openProduct(parseInt(card.dataset.id,10)); }); card.addEventListener('keydown', function(e){ if(e.key==='Enter') openProduct(parseInt(card.dataset.id,10)); }); });
  function openProduct(id){ var p=byId(id); if(!p) return; var qty=1, options={}; var b=$('#pm-body'); b.innerHTML='<button class="close" type="button" data-close="pm">✕</button>'+(p.image?'<img src="'+p.image+'" alt="" style="width:100%;max-height:260px;object-fit:cover;border-radius:14px;margin-bottom:12px">':'')+'<h3></h3><div class="pr" style="color:var(--acc);font-weight:800;font-size:18px" id="pm-price"></div><p class="muted" style="font-size:14.5px;white-space:pre-wrap" id="pm-desc"></p><div id="pm-vars"></div><div class="addrow"><div class="qty"><button type="button" id="q-minus">−</button><span id="q-val">1</span><button type="button" id="q-plus">+</button></div><button class="btn btn-lg" type="button" id="pm-add"></button></div>';
    b.querySelector('h3').textContent=p.name; $('#pm-desc').textContent=p.description; var vb=$('#pm-vars');
    p.variants.forEach(function(v,vi){ var g=document.createElement('div'); g.className='vgroup'; g.innerHTML='<span class="label"></span>'; g.querySelector('.label').textContent=v.name+(v.multi?'':' *'); v.options.forEach(function(o,oi){ var l=document.createElement('label'); l.className='vopt'; l.innerHTML='<span><input type="'+(v.multi?'checkbox':'radio')+'" name="v'+vi+'" value="'+oi+'"></span><b></b>'; l.querySelector('span').appendChild(document.createTextNode(o.label)); l.querySelector('b').textContent=o.extra?'+'+money(o.extra):''; l.querySelector('input').addEventListener('change', recalc); g.appendChild(l); }); vb.appendChild(g); });
    function recalc(){ options={}; p.variants.forEach(function(v,vi){ var sel=[].slice.call(vb.querySelectorAll('input[name=v'+vi+']:checked')).map(function(i){ return v.options[parseInt(i.value,10)].label; }); if(sel.length) options[v.name]=sel; }); var u=unitPrice(p,options); $('#pm-price').textContent=money(u); $('#pm-add').textContent=T.add+' · '+money(u*qty); $('#q-val').textContent=qty; }
    $('#q-minus').addEventListener('click', function(){ if(qty>1){qty--; recalc();} }); $('#q-plus').addEventListener('click', function(){ if(qty<99){qty++; recalc();} });
    $('#pm-add').addEventListener('click', function(){ var missing=p.variants.find(function(v){ return !v.multi && !(options[v.name]&&options[v.name].length); }); if(missing){ alert(T.choose+' '+missing.name); return; } var key=JSON.stringify(options); var line=cart.find(function(l){ return l.id===p.id && JSON.stringify(l.options)===key; }); if(line) line.qty=Math.min(99,line.qty+qty); else cart.push({id:p.id, qty:qty, options:options}); save(); renderBar(); close('pm'); });
    recalc(); $('#pm').classList.add('show'); }
  function close(id){ $('#'+id).classList.remove('show'); }
  document.addEventListener('click', function(e){ var c=e.target.closest('[data-close]'); if(c) close(c.dataset.close); if(e.target.classList.contains('modal')) e.target.classList.remove('show'); });
  $('#open-cart').addEventListener('click', function(){ renderCart(); $('#cm').classList.add('show'); });
  document.querySelectorAll('input[name=shipping]').forEach(function(r){ r.addEventListener('change', renderCart); });
  var ac=$('#apply-coupon'); if(ac) ac.addEventListener('click', function(){ applyCoupon(true); renderCart(); });
  $('#checkout').addEventListener('submit', function(e){ e.preventDefault(); if(!cart.length) return; var f=e.target; if(!f.checkValidity()){ f.reportValidity(); return; } $('#cart-json').value=JSON.stringify(cart); var btn=f.querySelector('button[type=submit]'); btn.disabled=true;
    fetch(f.action,{method:'POST', body:new FormData(f)}).then(function(r){return r.json();}).then(function(j){ if(!j.ok){ throw new Error(j.error||T.err); } cart=[]; save(); renderBar(); location.href=j.url; }).catch(function(err){ var el=$('#co-error'); el.textContent=err.message; el.style.display=''; btn.disabled=false; }); });
  renderBar();
})();
</script>
</body></html>
