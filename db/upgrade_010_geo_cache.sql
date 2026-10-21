-- Server-side country lookup cache for Smart Link country rules (used when no CDN country header exists).
-- Run once on existing installs:  mysql -u kuantan1_wsap -p kuantan1_wsap < db/upgrade_010_geo_cache.sql
CREATE TABLE IF NOT EXISTS geo_cache (
  ip_hash    CHAR(32) NOT NULL PRIMARY KEY,
  country    CHAR(2)  NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
