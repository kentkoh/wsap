<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
page_start(['title' => 'Terms of Service']);
?>
<section class="section"><div class="wrap" style="max-width:760px">
<span class="kicker">Legal</span>
<h1 style="font-size:40px;margin-bottom:24px">Terms of Service</h1>
<div style="font-size:15.5px;color:var(--ink-2)">
<p><b>1. The service.</b> WSAP (wsap.me and wsap.my) turns a WhatsApp number into a short link and QR code. We redirect visitors to WhatsApp; we are not affiliated with WhatsApp LLC or Meta Platforms, Inc.</p>
<p><b>2. Free links.</b> Free links are provided at no cost, never expire while the service operates, and cannot be edited after creation. We may disable links that are used for spam, fraud, scams, illegal content or impersonation.</p>
<p><b>3. Premium links.</b> Premium is billed per link per year. A Premium link can be edited at any time. If the subscription lapses the link keeps working but Premium features (editing, analytics, styled QR) are paused until renewed. Custom handles are first come, first served and may be reclaimed if they infringe a trademark.</p>
<p><b>4. Your responsibilities.</b> You must own or have permission to use the WhatsApp number you link. You are responsible for the messages and content you send through WhatsApp. Do not use WSAP for unsolicited bulk messaging, phishing, gambling promotion where prohibited, or anything illegal in Malaysia or your country.</p>
<p><b>5. Availability.</b> We aim for high availability but provide the service "as is" without warranty. We are not liable for lost sales or indirect damages arising from downtime or redirect failures.</p>
<p><b>6. Payments and refunds.</b> Payments are processed by Billplz or our card processor; we never store card details. Yearly Premium fees are non-refundable once a link is published, except where required by law.</p>
<p><b>7. Changes.</b> We may update these terms. Material changes will be announced on the site. Continued use means acceptance.</p>
<p><b>8. Contact.</b> Questions: <?php $wa = (string) cfg('support_whatsapp'); echo $wa !== '' ? '<a href="https://wa.me/' . e($wa) . '">WhatsApp us</a>' : 'via the WhatsApp link in the footer'; ?>.</p>
<p class="muted small">Last updated <?= date('F Y') ?>.</p>
</div></div></section>
<?php page_end();
