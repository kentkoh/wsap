<?php
declare(strict_types=1);
require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/layout.php';
page_start(['title' => 'Privacy Policy']);
?>
<section class="section"><div class="wrap" style="max-width:760px">
<span class="kicker">Legal</span>
<h1 style="font-size:40px;margin-bottom:24px">Privacy Policy</h1>
<div style="font-size:15.5px;color:var(--ink-2)">
<p><b>What we collect from link owners.</b> Your email, name, password hash (or Google account ID if you sign in with Google), the WhatsApp numbers and messages you put into links, and payment records from our payment processor.</p>
<p><b>What we collect from visitors who tap a link.</b> When someone opens a WSAP link we record the time, a one-way hash of their IP address that rotates daily (we never store the IP), country when provided by our hosting network, the referring site, and the device, operating system and browser family. We do not use tracking cookies on short links and we never see the WhatsApp conversation.</p>
<p><b>Why.</b> To show link owners how their links perform, to detect abuse, and to keep the service running.</p>
<p><b>Cookies.</b> A session cookie for logged-in users and the homepage generator, and a language preference cookie. Google Analytics may be enabled on the marketing pages.</p>
<p><b>Email.</b> We send transactional email (verification, password reset, receipts) through Brevo. We do not sell or share your email.</p>
<p><b>Retention.</b> Click records are kept for 13 months, then aggregated. Account data is kept until you delete your account; ask us and we remove it within 14 days.</p>
<p><b>Your rights.</b> You can export your click data as CSV from the dashboard (Premium) and request access, correction or deletion at any time. This policy follows Malaysia's Personal Data Protection Act 2010.</p>
<p><b>Contact.</b> <?php $wa = (string) cfg('support_whatsapp'); echo $wa !== '' ? '<a href="https://wa.me/' . e($wa) . '">WhatsApp us</a>' : 'via the WhatsApp link in the footer'; ?>.</p>
<p class="muted small">Last updated <?= date('F Y') ?>.</p>
</div></div></section>
<?php page_end();
