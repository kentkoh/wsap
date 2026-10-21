-- Demo mode: forms/shops flagged demo never send to WhatsApp; the last step shows a preview popup instead.
-- Run once on existing installs:  mysql -u kuantan1_wsap -p kuantan1_wsap < db/upgrade_008_demo.sql
ALTER TABLE forms ADD COLUMN demo TINYINT(1) NOT NULL DEFAULT 0 AFTER active;
ALTER TABLE shops ADD COLUMN demo TINYINT(1) NOT NULL DEFAULT 0 AFTER active;
UPDATE shops SET demo = 1 WHERE slug = 'bakery';
