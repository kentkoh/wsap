-- Monthly email report opt-out. Run once on existing installs:
--   mysql -u kuantan1_wsap -p kuantan1_wsap < db/upgrade_011_reports.sql
ALTER TABLE users ADD COLUMN notify_report TINYINT(1) NOT NULL DEFAULT 1;
