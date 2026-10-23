<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
require __DIR__ . '/lib/seo.php';
require __DIR__ . '/lib/marketing.php';
require_once __DIR__ . '/lib/home_graphics.php';
require __DIR__ . '/lib/demo.php';

$price = price('shop');                 // synced with admin pricing (settings table)
$demoShop = null;
try {
    $demoShop = db_row('SELECT slug, domain FROM shops WHERE demo = 1 AND active = 1 ORDER BY id LIMIT 1');
} catch (Throwable $e) {
}
$demoHref = $demoShop ? '/' . $demoShop['slug'] : '/shop';
$domain = current_domain();
$split = fn(string $k) => explode('|', t($k));
$tour = ['dashboard', 'products', 'categories', 'coupons', 'customers', 'orders', 'settings', 'storefront', 'checkout'];
$cmp = [['ps2_c1a', 'ps2_c1b'], ['ps2_c2a', 'ps2_c2b'], ['ps2_c3a', 'ps2_c3b'], ['ps2_c4a', 'ps2_c4b'], ['ps2_c5a', 'ps2_c5b'], ['ps2_c6a', 'ps2_c6b']];
$ver = filemtime(__DIR__ . '/assets/img/shop/dashboard.webp');

page_start(['title' => t('seo_ps_title'), 'description' => t('ps2_lead'), 'jsonld' => [seo_breadcrumbs([[t('nav_home'), '/'], [t('nav_p_shops'), '/whatsapp-shop']]), seo_app(cfg('site_name') . ' ' . t('nav_p_shops'), t('ps2_lead'), '/whatsapp-shop', seo_price_amount('shop'), currency())]]);
echo demo_popup_css();
?>
<section class="hero">
  <div class="wrap hero-product">
    <div>
      <span class="kicker"><?= e(t('ps2_kicker') ?: t('ps_kicker')) ?></span>
      <h1><?= e(t('ps2_h1a')) ?><br><span class="serif-i"><?= e(t('ps2_h1b')) ?></span></h1>
      <p class="lead"><?= e(t('ps2_lead')) ?></p>
      <div class="hero-ctas"><a class="btn btn-wa btn-lg" href="<?= e($demoHref) ?>" target="_blank" rel="noopener"><?= e(t('ps_cta_demo')) ?> ↗</a><a class="btn btn-ghost btn-lg" href="/shop"><?= e(t('ps_cta_open')) ?></a></div>
      <div class="stat-row"><div><b>0%</b><span><?= e(t('ps2_stat_comm')) ?></span></div><div><b><?= e(t('ps2_unlimited')) ?></b><span><?= e(t('ps2_stat_products')) ?></span></div><div><b><?= e($price) ?></b><span><?= e(t('ps2_stat_price')) ?></span></div></div>
    </div>
    <div class="hero-shop-pic reveal in">
      <div class="browser"><div class="browser-bar"><i></i><i></i><i></i><span><?= e($domain) ?>/bakery</span></div><img src="/assets/img/shop/storefront.webp?v=<?= $ver ?>" alt="WSAP shop storefront" width="1280" height="860" loading="eager" fetchpriority="high"></div>
      <div class="wa-float"><b>New order · The Golden Bakery</b> #9SBF86<br>▪ 2 × Butter Croissant (Almond cream)<br>▪ 1 × Caffè Latte (Large, Oat)<br><b>Total: RM24.80</b></div>
    </div>
  </div>
</section>

<?= hg_flow_explainer('shop', current_domain(), '#7c3aed', '#ede9fe') ?>

<section class="section band-dark">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('ps2_steps_k')) ?></span><h2><?= e(t('ps2_steps_t')) ?></h2></div>
    <div class="steps">
      <div class="step reveal"><h3><?= e(t('ps2_s1t')) ?></h3><p><?= e(t('ps2_s1d')) ?></p></div>
      <div class="step reveal"><h3><?= e(t('ps2_s2t')) ?></h3><p><?= e(t('ps2_s2d')) ?></p></div>
      <div class="step reveal"><h3><?= e(t('ps2_s3t')) ?></h3><p><?= e(t('ps2_s3d')) ?></p></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('ps2_look_k')) ?></span><h2><?= e(t('ps2_look_t')) ?></h2></div>
    <div class="bento">
      <div class="cell wide reveal"><div class="ico">🎨</div><h3><?= e(t('ps2_l1t')) ?></h3><p><?= e(t('ps2_l1d')) ?></p></div>
      <div class="cell wide reveal"><div class="ico">🛒</div><h3><?= e(t('ps2_l2t')) ?></h3><p><?= e(t('ps2_l2d')) ?></p></div>
      <div class="cell accent reveal" style="grid-column:span 6"><div class="ico">💬</div><h3><?= e(t('ps2_l3t')) ?></h3><p><?= e(t('ps2_l3d')) ?></p></div>
    </div>
  </div>
</section>

<section class="section band" id="features">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('ps2_built_k')) ?></span><h2><?= e(t('ps2_built_t')) ?></h2><p class="lead"><?= e(t('ps2_built_lead')) ?></p></div>
    <div class="feat-grid">
      <div class="feat reveal"><div class="mock" data-mock="options"></div><h3><?= e(t('ps2_f1t')) ?></h3><p><?= e(t('ps2_f1d')) ?></p><button class="read" data-open="m1"><?= e(t('ps2_read')) ?> →</button></div>
      <div class="feat reveal"><div class="mock" data-mock="fields"></div><h3><?= e(t('ps2_f2t')) ?></h3><p><?= e(t('ps2_f2d')) ?></p><button class="read" data-open="m2"><?= e(t('ps2_read')) ?> →</button></div>
      <div class="feat reveal"><div class="mock" data-mock="delivery"></div><h3><?= e(t('ps2_f3t')) ?></h3><p><?= e(t('ps2_f3d')) ?></p><button class="read" data-open="m3"><?= e(t('ps2_read')) ?> →</button></div>
      <div class="feat reveal"><div class="mock" data-mock="coupon"></div><h3><?= e(t('ps2_f4t')) ?></h3><p><?= e(t('ps2_f4d')) ?></p><button class="read" data-open="m4"><?= e(t('ps2_read')) ?> →</button></div>
      <div class="feat reveal"><div class="mock" data-mock="open"></div><h3><?= e(t('ps2_f5t')) ?></h3><p><?= e(t('ps2_f5d')) ?></p><button class="read" data-open="m5"><?= e(t('ps2_read')) ?> →</button></div>
      <div class="feat reveal"><div class="mock" data-mock="orders"></div><h3><?= e(t('ps2_f6t')) ?></h3><p><?= e(t('ps2_f6d')) ?></p><button class="read" data-open="m6"><?= e(t('ps2_read')) ?> →</button></div>
    </div>
  </div>
</section>

<section class="section" id="dashboard">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('ps2_dash_k')) ?></span><h2><?= e(t('ps2_dash_t')) ?></h2><p class="lead"><?= e(t('ps2_dash_lead')) ?></p></div>
    <div class="tour reveal">
      <div class="tour-tabs"><?php foreach ($tour as $i => $k): ?><button type="button" class="<?= $i === 0 ? 'on' : '' ?>" data-tab="<?= $k ?>"><?= e(t('ps2_t_' . $k)) ?></button><?php endforeach; ?></div>
      <div>
        <?php foreach ($tour as $i => $k): ?>
        <div class="tour-panel <?= $i === 0 ? 'on' : '' ?>" data-panel="<?= $k ?>">
          <div class="browser"><div class="browser-bar"><i></i><i></i><i></i><span><?= e($domain) ?>/<?= in_array($k, ['storefront', 'checkout'], true) ? 'bakery' : ($k === 'settings' ? 'shop?id=1' : $k . '?shop=1') ?></span></div><img src="/assets/img/shop/<?= $k ?>.webp?v=<?= $ver ?>" alt="<?= e(t('ps2_t_' . $k)) ?>" width="1280" height="860" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>"></div>
          <div class="tour-desc"><p><?= e(t('ps2_t_' . $k . '_d')) ?></p><div><span class="kicker" style="margin-bottom:8px"><?= e(t('ps2_see')) ?></span><ul><?php foreach ($split('ps2_t_' . $k . '_b') as $b): ?><li><?= e($b) ?></li><?php endforeach; ?></ul></div></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="section band">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('ps2_cmp_k')) ?></span><h2><?= e(t('ps2_cmp_t')) ?></h2></div>
    <table class="cmp2 reveal"><thead><tr><th><?= e(t('ps2_cmp_col1')) ?></th><th><?= e(t('ps2_cmp_col2')) ?></th></tr></thead><tbody>
      <?php foreach ($cmp as [$a, $b]): ?><tr><td class="a"><?= e(t($a, ['price' => $price])) ?></td><td class="b"><?= e(t($b, ['price' => $price])) ?></td></tr><?php endforeach; ?>
    </tbody></table>
  </div>
</section>

<section class="section" id="pricing">
  <div class="wrap">
    <div class="section-head reveal"><span class="kicker"><?= e(t('ps2_price_k')) ?></span><h2><?= e(t('ps2_price_t', ['price' => $price])) ?></h2><p class="lead"><?= e(t('ps2_price_lead')) ?></p></div>
    <div class="price-box reveal">
      <div class="card"><h3 style="font-size:20px"><?= e(t('ps2_inc_t')) ?></h3><ul class="inc-list"><?php foreach ($split('ps2_inc') as $b): ?><li><?= e($b) ?></li><?php endforeach; ?></ul></div>
      <div class="price-big"><b><?= e($price) ?></b><small><?= e(t('ps2_stat_price')) ?></small><a class="btn btn-wa btn-lg" href="/shop"><?= e(t('ps2_price_cta')) ?> →</a><p class="small" style="opacity:.75;margin:14px 0 0"><?= e(t('ps2_price_note')) ?></p></div>
    </div>
  </div>
</section>

<section class="section band">
  <div class="wrap">
    <div class="section-head reveal"><h2><?= e(t('ps2_faq_t')) ?></h2></div>
    <div class="faq reveal">
      <?php for ($i = 1; $i <= 6; $i++): ?><details<?= $i === 1 ? ' open' : '' ?>><summary><?= e(t("ps2_q$i", ['price' => $price])) ?></summary><div class="a"><?= e(t("ps2_a$i", ['price' => $price])) ?></div></details><?php endfor; ?>
    </div>
  </div>
</section>

<?= product_cta_band(t('ps2_final_t'), t('ps2_final_d'), '/shop', t('ps2_price_cta')) ?>

<?php
// ---- feature modals (copy on the left, live mock on the right)
$modals = [
    'm1' => ['ps2_m1_k', 'ps2_m1_t', 'ps2_m1_d', 'options', ['ps2_m1_tab1', 'ps2_m1_tab2', 'ps2_m1_tab3']],
    'm2' => ['ps2_m2_k', 'ps2_m2_t', 'ps2_m2_d', 'fields', []],
    'm3' => ['ps2_m3_k', 'ps2_m3_t', 'ps2_m3_d', 'delivery', []],
    'm4' => ['ps2_m4_k', 'ps2_m4_t', 'ps2_m4_d', 'coupon', []],
    'm5' => ['ps2_m5_k', 'ps2_m5_t', 'ps2_m5_d', 'open', []],
    'm6' => ['ps2_m6_k', 'ps2_m6_t', 'ps2_m6_d', 'orders', []],
];
foreach ($modals as $id => [$k, $tt, $d, $mock, $tabs]): ?>
<div class="fmodal" id="<?= $id ?>" role="dialog" aria-modal="true"><div class="fmodal-box">
  <button class="close" type="button" data-close="<?= $id ?>">✕</button>
  <div class="fmodal-copy"><span class="kicker"><?= e(t($k)) ?></span><h3><?= e(t($tt)) ?></h3><p><?= e(t($d)) ?></p></div>
  <div class="fmodal-demo">
    <?php if ($tabs): ?><nav class="tabs"><?php foreach ($tabs as $i => $tk): ?><a class="<?= $i === 1 ? 'on' : '' ?>" data-mode="<?= $i ?>"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?> · <?= e(t($tk)) ?></a><?php endforeach; ?></nav><?php endif; ?>
    <div class="browser"><div class="browser-bar"><i></i><i></i><i></i><span><?= e($domain) ?>/bakery</span></div><div class="mock" data-mock="<?= $mock ?>" data-big="1" style="border:0;border-radius:0"></div></div>
  </div>
</div></div>
<?php endforeach; ?>

<script>
(function(){
  var T = {add:<?= json_encode(t('ps2_mock_add')) ?>, qty:<?= json_encode(t('ps2_mock_qty')) ?>, order:<?= json_encode(t('ps2_mock_your_order')) ?>, apply:<?= json_encode(t('ps2_mock_apply')) ?>, cok:<?= json_encode(t('ps2_mock_coupon_ok')) ?>, cbad:<?= json_encode(t('ps2_mock_coupon_bad')) ?>, req:<?= json_encode(t('ps2_mock_required')) ?>, opt:<?= json_encode(t('ps2_mock_optional')) ?>, hid:<?= json_encode(t('ps2_mock_hidden')) ?>, open:<?= json_encode(t('ps2_mock_status_open')) ?>, closed:<?= json_encode(t('ps2_mock_status_closed')) ?>, send:<?= json_encode(t('ps2_mock_order_btn')) ?>, closedNote:<?= json_encode(t('ps2_mock_closed_note')) ?>, sub:<?= json_encode(t('sh_msg_subtotal')) ?>, ship:<?= json_encode(t('sh_msg_shipping')) ?>, disc:<?= json_encode(t('sh_msg_discount')) ?>, total:<?= json_encode(t('sh_msg_total')) ?>, free:<?= json_encode(t('sh_free')) ?>, coupon:<?= json_encode(t('sh_coupon')) ?>, st:{pending:<?= json_encode(t('sh_st_pending')) ?>,confirmed:<?= json_encode(t('sh_st_confirmed')) ?>,completed:<?= json_encode(t('sh_st_completed')) ?>}, fields:{name:<?= json_encode(t('sh_f_name')) ?>,phone:<?= json_encode(t('sh_f_phone')) ?>,address:<?= json_encode(t('sh_f_address')) ?>,city:<?= json_encode(t('sh_f_city')) ?>,notes:<?= json_encode(t('sh_f_notes')) ?>}};
  function rm(s){ var v=s/100; return 'RM'+(v%1===0?v.toFixed(0):v.toFixed(2)); }
  function el(h){ var d=document.createElement('div'); d.innerHTML=h; return d.firstElementChild; }
  function bump(n){ n.classList.remove('bump'); void n.offsetWidth; n.classList.add('bump'); }

  // 1. options
  function mockOptions(root, mode){
    mode = mode === undefined ? (root.dataset.big ? 1 : 1) : mode;
    var products = [
      {name:'Almond croissant', price:550, groups:[]},
      {name:'Caffè Latte', price:900, groups:[{name:'Size', multi:false, opts:[['Regular',0],['Large',200]]},{name:'Milk', multi:false, opts:[['Fresh milk',0],['Oat milk',150]]}]},
      {name:'Pizza Napoletana', price:2800, groups:[{name:'Size', multi:false, opts:[['Regular',0],['Family',1200]]},{name:'Toppings', multi:true, opts:[['Mozzarella',300],['Basil',100],['Mushroom',250],['Pepperoni',400]]}]}
    ];
    var p = products[mode], qty = 1, sel = {};
    root.innerHTML = '<div style="display:flex;justify-content:space-between;align-items:baseline"><b style="font-size:'+(root.dataset.big?'20px':'15px')+'">'+p.name+'</b><span class="mk-price"></span></div><div class="groups"></div><div style="display:flex;gap:10px;align-items:center;margin-top:10px"><span class="mk-pills" style="margin:0"><span class="mk-pill" data-q="-1">−</span><span class="mk-pill" style="border:0;background:none;font-weight:700" data-qv>1</span><span class="mk-pill" data-q="1">+</span></span><div class="mk-btn" style="flex:1;margin:0"></div></div>';
    var g = root.querySelector('.groups');
    p.groups.forEach(function(gr, gi){ sel[gr.name] = gr.multi ? [] : (gr.opts[0][0]); var h='<div><small class="kicker" style="margin:8px 0 2px">'+gr.name+(gr.multi?' · multi':'')+'</small><div class="mk-pills">'; gr.opts.forEach(function(o,oi){ var on = gr.multi ? false : oi===0; h+='<span class="mk-pill'+(on?' on':'')+'" data-g="'+gi+'" data-o="'+oi+'">'+o[0]+(o[1]?'<small>+'+rm(o[1])+'</small>':'')+'</span>'; }); h+='</div></div>'; g.appendChild(el(h)); });
    function price(){ var u=p.price; p.groups.forEach(function(gr){ gr.opts.forEach(function(o){ var s=sel[gr.name]; if(gr.multi? s.indexOf(o[0])>=0 : s===o[0]) u+=o[1]; }); }); return u; }
    function render(){ var u=price(); var pr=root.querySelector('.mk-price'); pr.textContent=rm(u); bump(pr); root.querySelector('[data-qv]').textContent=qty; root.querySelector('.mk-btn').textContent=T.add+' · '+rm(u*qty); }
    root.addEventListener('click', function(e){ var pill=e.target.closest('.mk-pill'); if(!pill) return; if(pill.dataset.q){ qty=Math.max(1,Math.min(9,qty+parseInt(pill.dataset.q,10))); render(); return; } if(pill.dataset.g===undefined) return; var gr=p.groups[+pill.dataset.g], o=gr.opts[+pill.dataset.o]; if(gr.multi){ var i=sel[gr.name].indexOf(o[0]); if(i>=0) sel[gr.name].splice(i,1); else sel[gr.name].push(o[0]); pill.classList.toggle('on'); } else { sel[gr.name]=o[0]; pill.parentNode.querySelectorAll('.mk-pill').forEach(function(x){x.classList.remove('on')}); pill.classList.add('on'); } render(); });
    render();
  }
  // 2. checkout fields
  function mockFields(root){
    var state = {name:'req', phone:'req', address:'on', city:'off', notes:'on'};
    root.innerHTML = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px"><div class="ctl"></div><div class="prev" style="background:var(--paper);border-radius:12px;padding:10px"></div></div>';
    if (!root.dataset.big) root.firstElementChild.style.gridTemplateColumns='1fr';
    var ctl=root.querySelector('.ctl'), prev=root.querySelector('.prev');
    function render(){ ctl.innerHTML=''; prev.innerHTML='<small class="kicker" style="margin:0 0 6px">'+T.order+'</small>'; Object.keys(state).forEach(function(f){ var row=el('<div class="mk-row"><span>'+T.fields[f]+'</span><span class="mk-seg"><span data-f="'+f+'" data-v="req" class="'+(state[f]==='req'?'on':'')+'">'+T.req+'</span><span data-f="'+f+'" data-v="on" class="'+(state[f]==='on'?'on':'')+'">'+T.opt+'</span><span data-f="'+f+'" data-v="off" class="'+(state[f]==='off'?'on':'')+'">'+T.hid+'</span></span></div>'); if(f==='name') row.querySelector('.mk-seg').innerHTML='<span class="on">'+T.req+'</span>'; ctl.appendChild(row); if(state[f]!=='off') prev.appendChild(el('<div class="mk-input'+(state[f]==='req'?' req':'')+'">'+T.fields[f]+'</div>')); }); prev.appendChild(el('<div class="mk-btn">'+T.send+'</div>')); }
    ctl.addEventListener('click', function(e){ var s=e.target.closest('[data-f]'); if(!s) return; state[s.dataset.f]=s.dataset.v; render(); });
    render();
  }
  // 3. delivery
  function mockDelivery(root){
    var methods=[['Store pickup',0,'Ready in 30 min'],['Delivery (Kuantan)',500,'Same day, before 2pm'],['Courier (Malaysia)',900,'1–3 days']]; var sub=2200, cur=0;
    root.innerHTML='<small class="kicker" style="margin:0 0 6px">'+T.ship+'</small><div class="list"></div><div class="mk-row"><span>'+T.sub+'</span><b>'+rm(sub)+'</b></div><div class="mk-row"><span>'+T.ship+'</span><b class="s"></b></div><div class="mk-total"><span>'+T.total+'</span><span class="mk-price t"></span></div>';
    var list=root.querySelector('.list');
    function render(){ list.innerHTML=''; methods.forEach(function(m,i){ list.appendChild(el('<div class="mk-radio'+(i===cur?' on':'')+'" data-i="'+i+'"><span>'+m[0]+' <small style="color:var(--ink-3)">· '+m[2]+'</small></span><b>'+(m[1]?rm(m[1]):T.free)+'</b></div>')); }); root.querySelector('.s').textContent=methods[cur][1]?rm(methods[cur][1]):T.free; var t=root.querySelector('.t'); t.textContent=rm(sub+methods[cur][1]); bump(t); }
    list.addEventListener('click', function(e){ var r=e.target.closest('[data-i]'); if(!r) return; cur=+r.dataset.i; render(); });
    render();
  }
  // 4. coupon
  function mockCoupon(root){
    var sub=2200, ship=500, codes={WELCOME10:['percent',10],RAYA5:['fixed',500],FLYER15:['percent',15]}; var disc=0, code='';
    root.innerHTML='<small class="kicker" style="margin:0 0 6px">'+T.coupon+'</small><div style="display:flex;gap:6px"><input class="input" style="padding:8px 10px;font-size:13px;text-transform:uppercase" value="WELCOME10"><button class="btn btn-sm btn-ghost" type="button">'+T.apply+'</button></div><p class="hint msg" style="min-height:18px;margin:4px 0 8px"></p><div class="mk-row"><span>'+T.sub+'</span><b>'+rm(sub)+'</b></div><div class="mk-row"><span>'+T.ship+'</span><b>'+rm(ship)+'</b></div><div class="d"></div><div class="mk-total"><span>'+T.total+'</span><span class="mk-price t"></span></div><p class="hint" style="margin-top:8px">WELCOME10 · RAYA5 · FLYER15</p>';
    var inp=root.querySelector('input'), msg=root.querySelector('.msg'), d=root.querySelector('.d');
    function render(){ d.innerHTML=disc?'<div class="mk-disc"><span>'+T.disc+' ('+code+')</span><b>−'+rm(disc)+'</b></div>':''; var t=root.querySelector('.t'); t.textContent=rm(sub+ship-disc); bump(t); }
    function apply(){ var c=inp.value.trim().toUpperCase(); var k=codes[c]; if(k){ code=c; disc=k[0]==='percent'?Math.round(sub*k[1]/100):k[1]; msg.textContent=T.cok+' −'+rm(disc); msg.style.color='var(--green-2)'; } else { code=''; disc=0; msg.textContent=c?T.cbad:''; msg.style.color='var(--danger)'; } render(); }
    root.querySelector('button').addEventListener('click', apply); inp.addEventListener('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); apply(); } });
    render(); setTimeout(apply, 700);
  }
  // 5. open / closed
  function mockOpen(root){
    var open=true;
    root.innerHTML='<label class="mk-switch on"><i></i><span class="lbl"></span></label><div style="margin-top:12px;background:var(--paper);border-radius:12px;padding:12px"><div style="font-weight:800">The Golden Bakery</div><span class="sstatus st" style="margin-top:6px"></span><div class="mk-btn b" style="margin-top:12px"></div></div>';
    var sw=root.querySelector('.mk-switch'), st=root.querySelector('.st'), b=root.querySelector('.b');
    function render(){ sw.classList.toggle('on',open); sw.querySelector('.lbl').textContent=open?T.open:T.closed; st.textContent=open?T.open:T.closed; st.classList.toggle('off',!open); b.textContent=open?T.send:T.closedNote; b.classList.toggle('off',!open); }
    sw.addEventListener('click', function(){ open=!open; render(); }); render();
  }
  // 6. orders
  function mockOrders(root){
    var orders=[['#9XJ8C6','Marta Ruiz',2450,'pending'],['#9XJ81C','Paula Díaz',3300,'pending'],['#9XJ8F6','Leo Martínez',3400,'confirmed'],['#9XJ8A1','Ana López',6200,'completed']]; var next={pending:'confirmed',confirmed:'completed',completed:'pending'};
    root.innerHTML='<div class="counts" style="display:flex;gap:8px;margin-bottom:8px"></div><div class="list"></div><p class="hint" style="margin-top:8px"><?= e(t('ps2_mock_next')) ?></p>';
    var list=root.querySelector('.list'), counts=root.querySelector('.counts');
    function render(){ list.innerHTML=''; var c={pending:0,confirmed:0,completed:0}; orders.forEach(function(o,i){ c[o[3]]++; list.appendChild(el('<div class="mk-order"><span><b>'+o[0]+'</b><br><small>'+o[1]+'</small></span><b>'+rm(o[2])+'</b><span class="badge st-'+o[3]+'" data-i="'+i+'" title="click">'+T.st[o[3]]+'</span></div>')); }); counts.innerHTML=Object.keys(c).map(function(k){ return '<span class="badge st-'+k+'">'+c[k]+' '+T.st[k]+'</span>'; }).join(''); }
    list.addEventListener('click', function(e){ var b=e.target.closest('[data-i]'); if(!b) return; var o=orders[+b.dataset.i]; o[3]=next[o[3]]; render(); });
    render();
  }
  var builders={options:mockOptions, fields:mockFields, delivery:mockDelivery, coupon:mockCoupon, open:mockOpen, orders:mockOrders};
  document.querySelectorAll('.mock[data-mock]').forEach(function(m){ builders[m.dataset.mock](m); });
  // modals
  document.querySelectorAll('[data-open]').forEach(function(b){ b.addEventListener('click', function(){ document.getElementById(b.dataset.open).classList.add('show'); }); });
  document.querySelectorAll('[data-close]').forEach(function(b){ b.addEventListener('click', function(){ document.getElementById(b.dataset.close).classList.remove('show'); }); });
  document.querySelectorAll('.fmodal').forEach(function(m){ m.addEventListener('click', function(e){ if(e.target===m) m.classList.remove('show'); }); });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') document.querySelectorAll('.fmodal.show').forEach(function(m){ m.classList.remove('show'); }); });
  // options modal tabs
  var m1=document.getElementById('m1'); if(m1){ m1.querySelectorAll('[data-mode]').forEach(function(a){ a.addEventListener('click', function(){ m1.querySelectorAll('[data-mode]').forEach(function(x){x.classList.remove('on')}); a.classList.add('on'); mockOptions(m1.querySelector('.mock'), +a.dataset.mode); }); }); }
  // dashboard tour
  document.querySelectorAll('.tour-tabs button').forEach(function(b){ b.addEventListener('click', function(){ document.querySelectorAll('.tour-tabs button').forEach(function(x){x.classList.remove('on')}); b.classList.add('on'); document.querySelectorAll('.tour-panel').forEach(function(p){ p.classList.toggle('on', p.dataset.panel===b.dataset.tab); }); }); });
})();
</script>
<?php page_end();
