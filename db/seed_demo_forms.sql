-- Demo forms for six industries, owned by the FIRST user (admin), published for 10 years, demo = preview popup (nothing sent).
-- Run once:  mysql -u kuantan1_wsap -p kuantan1_wsap < db/seed_demo_forms.sql   (safe to re-run: skips existing slugs)
SET NAMES utf8mb4;
SET @uid := (SELECT id FROM users ORDER BY id LIMIT 1);
SET @until := DATE_ADD(NOW(), INTERVAL 10 YEAR);
SET @theme := '{"bg":"#f7f6f1","text":"#10201a","title":"#0b3d2e","bar":"#25d366","button":"#0b3d2e"}';

INSERT INTO forms (user_id, slug, domain, title, intro, phone, opening_line, mode, theme, logo, questions, save_responses, privacy_url, active, demo, premium_until, views, starts, submits, created_at, updated_at)
SELECT @uid, 'demo-homestay', 'wsap.me', 'Tempah Homestay Seri Damai', 'Isi butiran anda, kami sahkan kekosongan dalam WhatsApp.', '60145866666', 'Hai Seri Damai! Saya nak tempah homestay:', 'steps',
  REPLACE(@theme, '#0b3d2e', '#0f5a43'), '/assets/img/demo/logo-homestay.svg',
  '[{"id":"qname","type":"short","label":"Nama penuh","help":"","required":true,"options":[],"max":0},
    {"id":"qphone","type":"phone","label":"Nombor WhatsApp","help":"","required":true,"options":[],"max":0},
    {"id":"qdates","type":"daterange","label":"Tarikh check-in dan check-out","help":"","required":true,"options":[],"max":0},
    {"id":"qpax","type":"number","label":"Bilangan tetamu","help":"Dewasa + kanak-kanak","required":true,"options":[],"max":0},
    {"id":"qunit","type":"single","label":"Unit","help":"","required":true,"options":["Villa 3 bilik (RM450/malam)","Studio 1 bilik (RM180/malam)","Dorm 8 katil (RM35/orang)"],"max":0},
    {"id":"qextra","type":"multiple","label":"Tambahan","help":"","required":false,"options":["BBQ set (RM80)","Early check-in","Late check-out","Sarapan (RM15/orang)"],"max":0},
    {"id":"qnote","type":"paragraph","label":"Permintaan khas","help":"","required":false,"options":[],"max":0}]',
  1, '', 1, 1, @until, 0, 0, 0, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM forms WHERE slug = 'demo-homestay');

INSERT INTO forms (user_id, slug, domain, title, intro, phone, opening_line, mode, theme, logo, questions, save_responses, privacy_url, active, demo, premium_until, views, starts, submits, created_at, updated_at)
SELECT @uid, 'demo-clinic', 'wsap.me', 'Book an appointment · Klinik Dr. Aina', 'Tell us when you can come and what it is about. We confirm the slot on WhatsApp.', '60145866666', 'Hi Klinik Dr. Aina, I would like to book an appointment:', 'steps',
  REPLACE(REPLACE(@theme, '#0b3d2e', '#1d4ed8'), '#25d366', '#60a5fa'), '/assets/img/demo/logo-clinic.svg',
  '[{"id":"qname","type":"short","label":"Patient name","help":"","required":true,"options":[],"max":0},
    {"id":"qic","type":"short","label":"IC / passport number","help":"For registration","required":true,"options":[],"max":0},
    {"id":"qphone","type":"phone","label":"WhatsApp number","help":"","required":true,"options":[],"max":0},
    {"id":"qservice","type":"dropdown","label":"Service","help":"","required":true,"options":["General consultation","Dental check-up","Vaccination","Blood test","Follow-up visit"],"max":0},
    {"id":"qdate","type":"date","label":"Preferred date","help":"","required":true,"options":[],"max":0},
    {"id":"qtime","type":"single","label":"Preferred time","help":"","required":true,"options":["Morning (9–12)","Afternoon (2–5)","Evening (6–8)"],"max":0},
    {"id":"qfirst","type":"single","label":"First visit?","help":"","required":true,"options":["Yes","No"],"max":0},
    {"id":"qsym","type":"paragraph","label":"Symptoms or notes","help":"","required":false,"options":[],"max":0}]',
  1, '', 1, 1, @until, 0, 0, 0, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM forms WHERE slug = 'demo-clinic');

INSERT INTO forms (user_id, slug, domain, title, intro, phone, opening_line, mode, theme, logo, questions, save_responses, privacy_url, active, demo, premium_until, views, starts, submits, created_at, updated_at)
SELECT @uid, 'demo-pickleball', 'wsap.me', 'Book a court · Smash Pickleball', 'Pick your court and time. We confirm availability on WhatsApp within minutes.', '60145866666', 'Hi Smash Pickleball! Court booking:', 'steps',
  REPLACE(REPLACE(@theme, '#0b3d2e', '#c2410c'), '#25d366', '#fb923c'), '/assets/img/demo/logo-pickleball.svg',
  '[{"id":"qname","type":"short","label":"Your name","help":"","required":true,"options":[],"max":0},
    {"id":"qphone","type":"phone","label":"WhatsApp number","help":"","required":true,"options":[],"max":0},
    {"id":"qdate","type":"date","label":"Date","help":"","required":true,"options":[],"max":0},
    {"id":"qtime","type":"time","label":"Start time","help":"Courts open 7am–11pm","required":true,"options":[],"max":0},
    {"id":"qdur","type":"single","label":"Duration","help":"","required":true,"options":["1 hour (RM40)","2 hours (RM70)","3 hours (RM100)"],"max":0},
    {"id":"qcourt","type":"single","label":"Court","help":"","required":true,"options":["Indoor A","Indoor B","Outdoor 1","Outdoor 2","Any"],"max":0},
    {"id":"qplayers","type":"number","label":"Number of players","help":"","required":true,"options":[],"max":0},
    {"id":"qrent","type":"multiple","label":"Rental","help":"","required":false,"options":["Paddles (RM5 each)","Balls (RM3)","Coach for 1 hour (RM80)"],"max":0}]',
  1, '', 1, 1, @until, 0, 0, 0, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM forms WHERE slug = 'demo-pickleball');

INSERT INTO forms (user_id, slug, domain, title, intro, phone, opening_line, mode, theme, logo, questions, save_responses, privacy_url, active, demo, premium_until, views, starts, submits, created_at, updated_at)
SELECT @uid, 'demo-workshop', 'wsap.me', 'Minta sebut harga · Bengkel Kereta Hafiz', 'Beritahu masalah kereta anda. Kami bagi anggaran harga melalui WhatsApp.', '60145866666', 'Hai Bengkel Hafiz, saya nak minta sebut harga:', 'single',
  REPLACE(REPLACE(@theme, '#0b3d2e', '#111827'), '#25d366', '#f59e0b'), '/assets/img/demo/logo-workshop.svg',
  '[{"id":"qname","type":"short","label":"Nama","help":"","required":true,"options":[],"max":0},
    {"id":"qphone","type":"phone","label":"Nombor WhatsApp","help":"","required":true,"options":[],"max":0},
    {"id":"qcar","type":"short","label":"Model kereta dan tahun","help":"cth. Myvi 2019","required":true,"options":[],"max":0},
    {"id":"qplate","type":"short","label":"Nombor plat","help":"","required":false,"options":[],"max":0},
    {"id":"qissue","type":"multiple","label":"Masalah","help":"","required":true,"options":["Servis berkala","Brek","Aircond","Tayar / alignment","Bateri","Enjin / bunyi pelik","Lain-lain"],"max":0},
    {"id":"qdesc","type":"paragraph","label":"Terangkan masalah","help":"","required":false,"options":[],"max":0},
    {"id":"qwhen","type":"date","label":"Bila boleh hantar kereta?","help":"","required":false,"options":[],"max":0},
    {"id":"qtow","type":"single","label":"Perlu tunda?","help":"","required":true,"options":["Tidak","Ya, kereta tak boleh bergerak"],"max":0}]',
  1, '', 1, 1, @until, 0, 0, 0, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM forms WHERE slug = 'demo-workshop');

INSERT INTO forms (user_id, slug, domain, title, intro, phone, opening_line, mode, theme, logo, questions, save_responses, privacy_url, active, demo, premium_until, views, starts, submits, created_at, updated_at)
SELECT @uid, 'demo-tuition', 'wsap.me', 'Enquiry · Bright Minds Tuition', 'Tell us about your child and we will suggest the right class and fee.', '60145866666', 'Hi Bright Minds, tuition enquiry:', 'steps',
  REPLACE(REPLACE(@theme, '#0b3d2e', '#7c3aed'), '#25d366', '#c4b5fd'), '/assets/img/demo/logo-tuition.svg',
  '[{"id":"qparent","type":"short","label":"Parent name","help":"","required":true,"options":[],"max":0},
    {"id":"qphone","type":"phone","label":"WhatsApp number","help":"","required":true,"options":[],"max":0},
    {"id":"qemail","type":"email","label":"Email","help":"For the timetable","required":false,"options":[],"max":0},
    {"id":"qstudent","type":"short","label":"Student name","help":"","required":true,"options":[],"max":0},
    {"id":"qlevel","type":"dropdown","label":"Level","help":"","required":true,"options":["Standard 1–3","Standard 4–6","Form 1–3","Form 4–5 (SPM)","Pre-U / IGCSE"],"max":0},
    {"id":"qsub","type":"multiple","label":"Subjects","help":"","required":true,"options":["Maths","Add Maths","Science","Physics","Chemistry","English","Bahasa Melayu","Sejarah"],"max":0},
    {"id":"qmode","type":"single","label":"Mode","help":"","required":true,"options":["At centre","Online","Home tuition"],"max":0},
    {"id":"qdays","type":"multiple","label":"Preferred days","help":"","required":false,"options":["Mon","Tue","Wed","Thu","Fri","Sat","Sun"],"max":0},
    {"id":"qstart","type":"date","label":"Start from","help":"","required":false,"options":[],"max":0}]',
  1, '', 1, 1, @until, 0, 0, 0, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM forms WHERE slug = 'demo-tuition');

INSERT INTO forms (user_id, slug, domain, title, intro, phone, opening_line, mode, theme, logo, questions, save_responses, privacy_url, active, demo, premium_until, views, starts, submits, created_at, updated_at)
SELECT @uid, 'demo-catering', 'wsap.me', 'Tempahan katering · Dapur Mak Teh', 'Untuk majlis, kenduri atau meeting pejabat. Kami balas dengan menu dan harga.', '60145866666', 'Hai Dapur Mak Teh, saya nak tempah katering:', 'steps',
  REPLACE(REPLACE(@theme, '#0b3d2e', '#b4232c'), '#25d366', '#f87171'), '/assets/img/demo/logo-catering.svg',
  '[{"id":"qname","type":"short","label":"Nama","help":"","required":true,"options":[],"max":0},
    {"id":"qphone","type":"phone","label":"Nombor WhatsApp","help":"","required":true,"options":[],"max":0},
    {"id":"qevent","type":"dropdown","label":"Jenis majlis","help":"","required":true,"options":["Kenduri kahwin","Majlis pejabat / meeting","Hari lahir","Rumah terbuka","Lain-lain"],"max":0},
    {"id":"qdate","type":"date","label":"Tarikh majlis","help":"","required":true,"options":[],"max":0},
    {"id":"qtime","type":"time","label":"Masa hidang","help":"","required":true,"options":[],"max":0},
    {"id":"qpax","type":"number","label":"Bilangan tetamu","help":"","required":true,"options":[],"max":0},
    {"id":"qmenu","type":"single","label":"Pakej","help":"","required":true,"options":["Pakej A · RM12/pax (4 lauk)","Pakej B · RM18/pax (6 lauk + pencuci mulut)","Pakej VIP · RM28/pax","Custom"],"max":0},
    {"id":"qaddr","type":"paragraph","label":"Alamat majlis","help":"","required":true,"options":[],"max":0},
    {"id":"qnote","type":"paragraph","label":"Nota (alahan, permintaan khas)","help":"","required":false,"options":[],"max":0}]',
  1, '', 1, 1, @until, 0, 0, 0, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM forms WHERE slug = 'demo-catering');

SELECT CONCAT('Demo forms ready: ', (SELECT COUNT(*) FROM forms WHERE slug LIKE 'demo-%'), ' forms (demo-homestay, demo-clinic, demo-pickleball, demo-workshop, demo-tuition, demo-catering)') AS result;
