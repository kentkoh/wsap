<?php
declare(strict_types=1);
/** wsap.me/{slug} (via go.php) — public shop: editorial catalogue, cart, checkout → WhatsApp order message. */
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/shops.php';
require_once __DIR__ . '/lib/ua.php';

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

$groups = shop_catalog((int) $shop['id'], true);
$fields = shop_fields($shop);
$shipping = shop_shipping($shop);
$cur = $shop['currency'];
$open = (int) $shop['is_open'] === 1;
$catalog = [];
foreach ($groups as $g) {
    foreach ($g['products'] as $p) {
        $catalog[] = ['id' => (int) $p['id'], 'name' => $p['name'], 'price' => (int) $p['price'], 'image' => $p['image'], 'description' => $p['description'], 'variants' => product_variants($p)];
    }
}
$abs = fn(string $u) => $u === '' ? '' : (str_starts_with($u, 'http') ? $u : base_url() . $u);
$social = array_filter(['instagram' => $shop['instagram'] ?? '', 'facebook' => $shop['facebook'] ?? '', 'tiktok' => $shop['tiktok'] ?? '']);
$waIcon = '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2m0 1.67c4.56 0 8.24 3.68 8.24 8.24s-3.68 8.24-8.24 8.24c-1.5 0-2.97-.41-4.26-1.18l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.35c0-4.56 3.68-8.24 8.24-8.24m-3.4 4.43c-.19 0-.5.07-.76.35-.26.28-1 .98-1 2.38 0 1.4 1.02 2.76 1.17 2.95.14.19 2 3.17 4.93 4.32 2.44.96 2.93.77 3.46.72.53-.05 1.71-.7 1.95-1.37.24-.68.24-1.26.17-1.38-.07-.12-.26-.19-.55-.33-.29-.14-1.71-.84-1.97-.94-.26-.1-.46-.14-.65.14-.19.29-.75.94-.92 1.13-.17.19-.34.22-.63.07-.29-.14-1.22-.45-2.32-1.43-.86-.76-1.44-1.71-1.6-2-.17-.29-.02-.45.13-.59.13-.13.29-.34.43-.5.14-.17.19-.29.29-.48.1-.19.05-.36-.02-.5-.07-.14-.65-1.56-.89-2.14-.23-.56-.47-.49-.65-.5h-.55z"/></svg>';
?>
<!doctype html>
<html lang="<?= lang() ?>">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($shop['name']) ?></title>
<meta name="description" content="<?= e($shop['tagline']) ?>">
<meta property="og:title" content="<?= e($shop['name']) ?>"><meta property="og:description" content="<?= e($shop['tagline']) ?>"><?php if ($shop['cover'] !== ''): ?><meta property="og:image" content="<?= e($abs($shop['cover'])) ?>"><?php endif; ?>
<link rel="icon" href="<?= $shop['logo'] !== '' ? e($abs($shop['logo'])) : '/assets/img/favicon.svg' ?>">
<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Instrument+Serif&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
<style>
  :root{--acc:<?= e($shop['accent']) ?>;--sp:#faf9f5;--spt:#111}
  body{background:var(--sp);color:var(--spt);font-family:"Plus Jakarta Sans",system-ui,sans-serif}
  .sw{max-width:1120px;margin:0 auto;padding:0 clamp(16px,4vw,40px)}
  .scover{height:clamp(180px,34vw,420px);background:var(--acc);background-size:cover;background-position:center;position:relative}
  .scover::after{content:"";position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.28),transparent 50%)}
  .shead{position:relative;padding:0 0 28px}
  .slogo{width:92px;height:92px;border-radius:22px;background:#fff;padding:4px;box-shadow:0 10px 30px -10px rgba(0,0,0,.35);margin-top:-46px;overflow:hidden;display:grid;place-items:center;font-weight:800;font-size:34px;color:var(--acc)}
  .slogo img{width:100%;height:100%;object-fit:cover;border-radius:18px}
  .kick{font-family:"JetBrains Mono",monospace;font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:var(--acc);margin:18px 0 6px;display:block}
  .shead h1{font-size:clamp(30px,5vw,48px);letter-spacing:-.03em;line-height:1.05;margin:0 0 8px}
  .shead .tag{font-size:16px;color:#555;max-width:60ch;margin:0 0 12px}
  .meta{display:flex;gap:14px;flex-wrap:wrap;font-family:"JetBrains Mono",monospace;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#666}
  .meta .dot{display:inline-flex;align-items:center;gap:6px;color:#0b3d2e}.meta .dot::before{content:"";width:8px;height:8px;border-radius:50%;background:#25d366}.meta .dot.off{color:#b4232c}.meta .dot.off::before{background:#b4232c}
  .sec{padding:clamp(28px,5vw,56px) 0 0}
  .sec-head{display:flex;align-items:flex-end;gap:14px;margin-bottom:18px}
  .sec-head .num{font-family:"JetBrains Mono",monospace;color:var(--acc);font-size:14px;padding-bottom:10px}
  .sec-head h2{font-family:"Instrument Serif",Georgia,serif;font-weight:400;font-size:clamp(32px,5vw,52px);letter-spacing:-.02em;line-height:1;margin:0}
  .sec-head .d{color:#666;font-size:14.5px;margin:6px 0 0}
  .sec-head .line{flex:1;height:1px;background:#e4e2da;margin-bottom:12px}
  .cat-row{display:flex;gap:12px;overflow-x:auto;padding-bottom:6px;scrollbar-width:none}
  .cat{flex:0 0 120px;text-decoration:none;color:inherit}.cat .ci{width:120px;height:92px;border-radius:14px;background:#e9e6dc;overflow:hidden;display:grid;place-items:center;font-size:28px}.cat .ci img{width:100%;height:100%;object-fit:cover}
  .cat b{display:block;font-size:14.5px;margin-top:8px}.cat small{color:#888}
  .sgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:16px}
  @media(min-width:700px){.sgrid{grid-template-columns:repeat(auto-fill,minmax(200px,1fr))}}
  .sp{cursor:pointer}.sp .img{position:relative;aspect-ratio:1;border-radius:16px;overflow:hidden;background:#ece9df;display:grid;place-items:center;font-size:34px;transition:transform .25s cubic-bezier(.16,1,.3,1)}.sp:hover .img{transform:translateY(-3px)}.sp .img img{width:100%;height:100%;object-fit:cover}
  .sp .plus{position:absolute;right:10px;bottom:10px;background:#fff;border-radius:999px;min-width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:0 10px;font-family:"JetBrains Mono",monospace;font-size:11px;letter-spacing:.1em;text-transform:uppercase;box-shadow:0 4px 14px rgba(0,0,0,.15)}
  .sp .row{display:flex;justify-content:space-between;gap:10px;margin-top:10px;font-size:14.5px}.sp .row b{font-weight:600}.sp .row span{color:#555}
  .sfoot{margin-top:clamp(40px,7vw,80px);border-top:1px solid #e4e2da;padding:36px 0 28px;font-size:14px;color:#555}
  .sfoot .cols{display:grid;grid-template-columns:1.6fr 1fr 1fr;gap:24px}@media(max-width:640px){.sfoot .cols{grid-template-columns:1fr}}
  .sfoot h3{font-size:20px;color:var(--spt);margin:8px 0 6px;letter-spacing:-.02em}.sfoot h4{font-family:"JetBrains Mono",monospace;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#888;margin:0 0 10px}
  .sfoot a{color:var(--spt);display:block;margin-bottom:6px}.sfoot .bottom{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:28px;font-size:12.5px;color:#888}
  .cartbar{position:fixed;left:0;right:0;bottom:0;padding:12px 16px calc(12px + env(safe-area-inset-bottom));background:rgba(250,249,245,.92);backdrop-filter:blur(10px);border-top:1px solid #e4e2da;display:none;justify-content:center;z-index:40}
  .cartbar.show{display:flex}.cartbar .btn{background:var(--acc);color:#fff;width:100%;max-width:560px;justify-content:space-between;border-radius:999px}
  .modal{position:fixed;inset:0;background:rgba(20,20,20,.55);backdrop-filter:blur(3px);display:none;align-items:flex-end;justify-content:center;z-index:60}.modal.show{display:flex}
  .sheet{background:#fff;width:100%;max-width:560px;max-height:92vh;overflow:auto;border-radius:22px 22px 0 0;padding:22px 20px 28px;position:relative}
  .sheet.wide{max-width:900px;padding:0;display:grid;grid-template-columns:1fr;overflow:hidden}
  .sheet.wide .pimg{aspect-ratio:4/3;background:#ece9df;display:grid;place-items:center;font-size:48px}.sheet.wide .pimg img{width:100%;height:100%;object-fit:cover}
  .sheet.wide .pbody{padding:24px 22px 28px;overflow:auto}
  @media(min-width:700px){.modal{align-items:center}.sheet{border-radius:22px}.sheet.wide{grid-template-columns:1fr 1fr;max-height:88vh}.sheet.wide .pimg{aspect-ratio:auto;height:100%}}
  .sheet h3{font-size:24px;letter-spacing:-.02em;margin:0 0 4px}.sheet .close{position:absolute;right:14px;top:14px;border:0;background:#f1efe8;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:16px;z-index:2}
  .pprice{font-size:18px;color:#444;margin-bottom:12px}.pdesc{color:#555;font-size:14.5px;white-space:pre-wrap;border-bottom:1px solid #eee;padding-bottom:14px;margin-bottom:14px}
  .vgroup{margin:0 0 16px}.vgroup .label{font-family:"JetBrains Mono",monospace;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#777;margin-bottom:8px;display:block}
  .pills{display:flex;gap:8px;flex-wrap:wrap}.pill-opt{position:relative;display:inline-flex;align-items:center;gap:6px;padding:9px 14px;border:1.5px solid #ddd;border-radius:999px;cursor:pointer;font-size:14px;background:#fff}.pill-opt:has(input:checked){border-color:var(--acc);background:var(--acc);color:#fff}.pill-opt input{position:absolute;opacity:0;pointer-events:none}.pill-opt small{opacity:.75}
  .qty{display:inline-flex;align-items:center;border:1.5px solid #ddd;border-radius:999px;overflow:hidden}.qty button{border:0;background:#fff;width:40px;height:40px;font-size:20px;cursor:pointer}.qty span{min-width:36px;text-align:center;font-weight:700}
  .addrow{display:flex;gap:12px;align-items:center;margin-top:18px}.addrow .btn{flex:1;background:var(--acc);color:#fff;border-radius:999px}
  .verr{color:#b4232c;font-size:13px;margin:-8px 0 10px;display:none}
  .cline{display:grid;grid-template-columns:1fr auto auto;gap:10px;align-items:center;padding:10px 0;border-bottom:1px solid #eee;font-size:14.5px}.cline small{display:block;color:#888}
  .tot{display:flex;justify-content:space-between;padding:6px 0;font-size:14.5px}.tot.big{font-weight:800;font-size:18px;border-top:1px solid #eee;margin-top:6px;padding-top:12px}
  .vopt{display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border:1.5px solid #ddd;border-radius:12px;margin-bottom:6px;cursor:pointer;font-size:14.5px}.vopt:has(input:checked){border-color:var(--acc);background:rgba(0,0,0,.03)}.vopt input{margin-right:8px}
  .fwarn{background:#fff6d6;color:#6a4a00;padding:10px 14px;border-radius:10px;font-size:14px;margin:12px auto 0;max-width:1120px}
  .page-pad{padding-bottom:110px}
</style>
</head>
<body class="page-pad">
<?php if ($preview): ?><div class="fwarn"><?= e(t('sh_preview_note')) ?></div><?php endif; ?>
<header>
  <div class="scover" <?= $shop['cover'] !== '' ? 'style="background-image:url(' . e($shop['cover']) . ')"' : '' ?>></div>
  <div class="sw shead">
    <div class="slogo"><?php if ($shop['logo'] !== ''): ?><img src="<?= e($shop['logo']) ?>" alt="<?= e($shop['name']) ?>"><?php else: ?><?= e(mb_strtoupper(mb_substr($shop['name'], 0, 1))) ?><?php endif; ?></div>
    <span class="kick"><?= e(t('sh_kicker')) ?></span>
    <h1><?= e($shop['name']) ?></h1>
    <?php if ($shop['tagline'] !== ''): ?><p class="tag"><?= e($shop['tagline']) ?></p><?php endif; ?>
    <div class="meta"><span class="dot <?= $open ? '' : 'off' ?>"><?= e($open ? t('sh_open_now') : t('sh_closed_now')) ?></span><?php if (($shop['hours_text'] ?? '') !== ''): ?><span><?= e($shop['hours_text']) ?></span><?php endif; ?><span><?= e(t('sh_order_ships')) ?></span></div>
  </div>
</header>

<main class="sw">
  <?php $named = array_values(array_filter($groups, fn($g) => $g['category'] !== null)); if (count($named) > 1): ?>
  <section class="sec" style="padding-top:8px">
    <div class="sec-head" style="align-items:baseline"><div><span class="kick" style="margin-top:0"><?= e(t('sh_browse')) ?></span><h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:28px;letter-spacing:-.03em"><?= e(t('sh_nav_categories')) ?></h2></div><div class="line"></div><span class="d"><?= count($named) ?> <?= e(t('sh_collections')) ?></span></div>
    <div class="cat-row">
      <?php foreach ($groups as $i => $g): if ($g['category'] === null) { continue; } $c = $g['category']; ?>
      <a class="cat" href="#cat<?= $i ?>"><div class="ci"><?php if ($c['image'] !== ''): ?><img src="<?= e($c['image']) ?>" alt="" loading="lazy"><?php elseif (!empty($g['products'][0]['image'])): ?><img src="<?= e($g['products'][0]['image']) ?>" alt="" loading="lazy"><?php else: ?>🛍️<?php endif; ?></div><b><?= e($c['name']) ?> <small>(<?= count($g['products']) ?>)</small></b></a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!$groups): ?><p class="tag" style="padding:40px 0"><?= e(t('sh_no_products')) ?></p><?php endif; ?>
  <?php foreach ($groups as $i => $g): $c = $g['category']; ?>
  <section class="sec" id="cat<?= $i ?>">
    <?php if ($c !== null || count($groups) > 1): ?>
    <div class="sec-head"><span class="num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span><div><h2><?= e($c ? $c['name'] : t('sh_cat_other')) ?></h2><?php if ($c && $c['description'] !== ''): ?><p class="d"><?= e($c['description']) ?></p><?php endif; ?></div><div class="line"></div></div>
    <?php endif; ?>
    <div class="sgrid">
      <?php foreach ($g['products'] as $p): $hasOpt = (bool) product_variants($p); ?>
      <article class="sp" data-id="<?= (int) $p['id'] ?>" tabindex="0" role="button" aria-label="<?= e($p['name']) ?>">
        <div class="img"><?php if ($p['image'] !== ''): ?><img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy"><?php else: ?>🛍️<?php endif; ?><span class="plus"><?= $hasOpt ? e(t('sh_options')) . ' →' : '+' ?></span></div>
        <div class="row"><b><?= e($p['name']) ?></b><span><?= e(money((int) $p['price'], $cur)) ?></span></div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endforeach; ?>
</main>

<footer class="sfoot"><div class="sw">
  <div class="cols">
    <div><?php if ($shop['logo'] !== ''): ?><img src="<?= e($shop['logo']) ?>" alt="" style="width:44px;height:44px;border-radius:12px;object-fit:cover"><?php endif; ?><h3><?= e($shop['name']) ?></h3><p><?= nl2br(e($shop['about'] !== '' ? $shop['about'] : $shop['tagline'])) ?></p></div>
    <div><h4><?= e(t('sh_links_h')) ?></h4><a href="https://wa.me/<?= e($shop['phone']) ?>" rel="noopener"><?= e(t('sh_contact')) ?></a><?php if (($shop['hours_text'] ?? '') !== ''): ?><span><?= e($shop['hours_text']) ?></span><?php endif; ?></div>
    <div><h4><?= e(t('sh_social_h')) ?></h4><?php foreach ($social as $k => $url): ?><a href="<?= e($url) ?>" target="_blank" rel="noopener"><?= ucfirst($k) ?></a><?php endforeach; ?><?php if (!$social): ?><span style="color:#aaa">—</span><?php endif; ?></div>
  </div>
  <div class="bottom"><span>© <?= date('Y') ?> <?= e($shop['name']) ?>. <?= e(t('sh_rights')) ?></span><span><?= e(t('open_pow')) ?> <a href="/" style="display:inline"><?= e($site) ?></a></span></div>
</div></footer>

<div class="cartbar" id="cartbar"><button class="btn btn-lg" id="open-cart" type="button"><span><?= e(t('sh_view_cart')) ?> · <span id="cart-count">0</span></span><b id="cart-total"></b></button></div>

<div class="modal" id="pm"><div class="sheet wide" id="pm-body"></div></div>
<div class="modal" id="cm"><div class="sheet">
  <button class="close" type="button" data-close="cm">✕</button><h3><?= e(t('sh_your_order')) ?></h3>
  <div id="cart-lines"></div>
  <form id="checkout" method="post" action="/api/shop_order.php" style="margin-top:14px">
    <input type="hidden" name="shop" value="<?= (int) $shop['id'] ?>"><input type="hidden" name="cart" id="cart-json"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><?php if ($preview): ?><input type="hidden" name="preview" value="1"><?php endif; ?>
    <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
    <?php if ($shipping): ?><div class="vgroup"><span class="label"><?= e(t('sh_delivery')) ?></span>
      <?php foreach ($shipping as $i => $m): ?><label class="vopt"><span><input type="radio" name="shipping" value="<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?> data-price="<?= (int) $m['price'] ?>"><?= e($m['name']) ?><?= $m['note'] !== '' ? ' <small style="color:#888">· ' . e($m['note']) . '</small>' : '' ?></span><b><?= (int) $m['price'] > 0 ? e(money((int) $m['price'], $cur)) : e(t('sh_free')) ?></b></label><?php endforeach; ?></div><?php endif; ?>
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
    <?php if ($open): ?><button class="btn btn-wa btn-lg btn-block" type="submit" style="margin-top:14px;border-radius:999px"><?= $waIcon ?> <?= e(t('sh_send_order')) ?></button><?php else: ?><div class="locked" style="margin-top:14px"><?= e(t('sh_closed_msg')) ?></div><?php endif; ?>
  </form>
</div></div>

<script>
(function(){
  var P = <?= json_encode($catalog, JSON_UNESCAPED_UNICODE) ?>, CUR = <?= json_encode($cur) ?>, COUPONS = <?= json_encode(array_map(fn($c) => ['code' => strtoupper($c['code']), 'type' => $c['type'], 'value' => (float) $c['value']], shop_coupons($shop))) ?>;
  var T = {add:<?= json_encode(t('sh_add_to_cart')) ?>, free:<?= json_encode(t('sh_free')) ?>, choose:<?= json_encode(t('sh_choose_one')) ?>, badCoupon:<?= json_encode(t('sh_bad_coupon')) ?>, okCoupon:<?= json_encode(t('sh_ok_coupon')) ?>, err:<?= json_encode(t('err_generic')) ?>};
  var SYM = {MYR:'RM',SGD:'S$',USD:'$',IDR:'Rp',THB:'฿',PHP:'₱',BND:'B$',AUD:'A$',GBP:'£',EUR:'€'};
  function money(s){ var v=s/100; return (SYM[CUR]||CUR+' ')+(v%1===0?v.toFixed(0):v.toFixed(2)); }
  var KEY='wsap_cart_<?= (int) $shop['id'] ?>', cart=[]; try{ cart=JSON.parse(sessionStorage.getItem(KEY)||'[]'); }catch(e){}
  var coupon={code:'',discount:0};
  var $=function(s){ return document.querySelector(s); };
  function byId(id){ return P.find(function(p){ return p.id===id; }); }
  function unitPrice(p,options){ var u=p.price; p.variants.forEach(function(v){ var sel=options[v.name]||[]; v.options.forEach(function(o){ if(sel.indexOf(o.label)>=0) u+=o.extra; }); }); return u; }
  function save(){ try{ sessionStorage.setItem(KEY, JSON.stringify(cart)); }catch(e){} }
  function subtotal(){ return cart.reduce(function(s,l){ var p=byId(l.id); return p? s+unitPrice(p,l.options)*l.qty : s; },0); }
  function shipPrice(){ var r=document.querySelector('input[name=shipping]:checked'); return r? parseInt(r.dataset.price,10):0; }
  function renderBar(){ var n=cart.reduce(function(s,l){return s+l.qty},0); $('#cartbar').classList.toggle('show', n>0); $('#cart-count').textContent=n; $('#cart-total').textContent=money(subtotal()); }
  function renderCart(){ var el=$('#cart-lines'); el.innerHTML=''; cart.forEach(function(l,i){ var p=byId(l.id); if(!p) return; var opts=Object.keys(l.options).map(function(k){ return k+': '+l.options[k].join(', '); }).join(' · '); var d=document.createElement('div'); d.className='cline'; d.innerHTML='<div><b></b><small></small></div><div class="qty"><button type="button" data-d="-1">−</button><span>'+l.qty+'</span><button type="button" data-d="1">+</button></div><b>'+money(unitPrice(p,l.options)*l.qty)+'</b>'; d.querySelector('b').textContent=p.name; d.querySelector('small').textContent=opts; d.querySelectorAll('[data-d]').forEach(function(b){ b.addEventListener('click', function(){ l.qty+=parseInt(b.dataset.d,10); if(l.qty<=0) cart.splice(i,1); save(); renderCart(); renderBar(); }); }); el.appendChild(d); });
    var sub=subtotal(); applyCoupon(false); var ship=shipPrice(); var disc=coupon.discount; $('#t-sub').textContent=money(sub); $('#t-ship').textContent=ship?money(ship):T.free; $('#t-disc-row').style.display=disc?'':'none'; $('#t-disc').textContent='−'+money(disc); $('#t-total').textContent=money(Math.max(0,sub+ship-disc)); $('#cart-json').value=JSON.stringify(cart); }
  function applyCoupon(announce){ var inp=$('#c-coupon'); if(!inp){ coupon={code:'',discount:0}; return; } var code=inp.value.trim().toUpperCase(); var c=COUPONS.find(function(x){return x.code===code;}); var sub=subtotal(); if(c){ coupon={code:code, discount:Math.min(sub, c.type==='fixed'? Math.round(c.value): Math.round(sub*c.value/100))}; if(announce) $('#coupon-msg').textContent=T.okCoupon+' −'+money(coupon.discount); } else { coupon={code:'',discount:0}; if(announce) $('#coupon-msg').textContent= code? T.badCoupon : ''; } }
  document.querySelectorAll('.sp').forEach(function(card){ card.addEventListener('click', function(){ openProduct(parseInt(card.dataset.id,10)); }); card.addEventListener('keydown', function(e){ if(e.key==='Enter') openProduct(parseInt(card.dataset.id,10)); }); });
  function openProduct(id){ var p=byId(id); if(!p) return; var qty=1, options={}; var b=$('#pm-body');
    b.innerHTML='<button class="close" type="button" data-close="pm">✕</button><div class="pimg">'+(p.image?'<img src="'+p.image+'" alt="">':'🛍️')+'</div><div class="pbody"><h3></h3><div class="pprice" id="pm-price"></div><p class="pdesc" id="pm-desc"></p><div id="pm-vars"></div><p class="verr" id="pm-err"></p><div class="addrow"><div class="qty"><button type="button" id="q-minus">−</button><span id="q-val">1</span><button type="button" id="q-plus">+</button></div><button class="btn btn-lg" type="button" id="pm-add"></button></div></div>';
    b.querySelector('h3').textContent=p.name; $('#pm-desc').textContent=p.description; if(!p.description) $('#pm-desc').style.display='none'; var vb=$('#pm-vars');
    p.variants.forEach(function(v,vi){ var g=document.createElement('div'); g.className='vgroup'; g.innerHTML='<span class="label"></span><div class="pills"></div>'; g.querySelector('.label').textContent=v.name+(v.multi?'':' *'); var pills=g.querySelector('.pills'); v.options.forEach(function(o,oi){ var l=document.createElement('label'); l.className='pill-opt'; l.innerHTML='<input type="'+(v.multi?'checkbox':'radio')+'" name="v'+vi+'" value="'+oi+'"><span></span><small></small>'; l.querySelector('span').textContent=o.label; l.querySelector('small').textContent=o.extra?'+'+money(o.extra):''; l.querySelector('input').addEventListener('change', recalc); pills.appendChild(l); }); vb.appendChild(g); });
    function recalc(){ options={}; p.variants.forEach(function(v,vi){ var sel=[].slice.call(vb.querySelectorAll('input[name=v'+vi+']:checked')).map(function(i){ return v.options[parseInt(i.value,10)].label; }); if(sel.length) options[v.name]=sel; }); var u=unitPrice(p,options); $('#pm-price').textContent=money(u); $('#pm-add').textContent=T.add+' · '+money(u*qty)+' →'; $('#q-val').textContent=qty; $('#pm-err').style.display='none'; }
    $('#q-minus').addEventListener('click', function(){ if(qty>1){qty--; recalc();} }); $('#q-plus').addEventListener('click', function(){ if(qty<99){qty++; recalc();} });
    $('#pm-add').addEventListener('click', function(){ var missing=p.variants.find(function(v){ return !v.multi && !(options[v.name]&&options[v.name].length); }); if(missing){ var e=$('#pm-err'); e.textContent=T.choose+' '+missing.name; e.style.display='block'; return; } var key=JSON.stringify(options); var line=cart.find(function(l){ return l.id===p.id && JSON.stringify(l.options)===key; }); if(line) line.qty=Math.min(99,line.qty+qty); else cart.push({id:p.id, qty:qty, options:options}); save(); renderBar(); close('pm'); });
    recalc(); $('#pm').classList.add('show'); }
  function close(id){ $('#'+id).classList.remove('show'); }
  document.addEventListener('click', function(e){ var c=e.target.closest('[data-close]'); if(c) close(c.dataset.close); if(e.target.classList.contains('modal')) e.target.classList.remove('show'); });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ close('pm'); close('cm'); } });
  $('#open-cart').addEventListener('click', function(){ renderCart(); $('#cm').classList.add('show'); });
  document.querySelectorAll('input[name=shipping]').forEach(function(r){ r.addEventListener('change', renderCart); });
  var ac=$('#apply-coupon'); if(ac) ac.addEventListener('click', function(){ applyCoupon(true); renderCart(); });
  $('#checkout').addEventListener('submit', function(e){ e.preventDefault(); if(!cart.length) return; var f=e.target; if(!f.checkValidity()){ f.reportValidity(); return; } $('#cart-json').value=JSON.stringify(cart); var btn=f.querySelector('button[type=submit]'); btn.disabled=true;
    fetch(f.action,{method:'POST', body:new FormData(f)}).then(function(r){return r.json();}).then(function(j){ if(!j.ok){ throw new Error(j.error||T.err); } cart=[]; save(); renderBar(); location.href=j.url; }).catch(function(err){ var el=$('#co-error'); el.textContent=err.message; el.style.display=''; btn.disabled=false; }); });
  renderBar();
})();
</script>
</body></html>
