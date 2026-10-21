-- Smart Links becomes a paid add-on per link (monthly price, billed yearly with the link).
-- Run once on existing installs:  mysql -u kuantan1_wsap -p kuantan1_wsap < db/upgrade_009_smart_addon.sql
ALTER TABLE links ADD COLUMN smart_until DATETIME NULL AFTER premium_until;
ALTER TABLE payments ADD COLUMN addons VARCHAR(60) NOT NULL DEFAULT '' AFTER handle;
-- links that already use routing keep it until their Premium ends
UPDATE links SET smart_until = premium_until WHERE type = 'smart' AND premium_until IS NOT NULL;
