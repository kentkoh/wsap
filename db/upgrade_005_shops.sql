-- Shops: a WhatsApp catalogue with cart; orders arrive itemised in WhatsApp.
-- Run once on existing installs:  mysql -u kuantan1_wsap -p kuantan1_wsap < db/upgrade_005_shops.sql
CREATE TABLE IF NOT EXISTS shops (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED NOT NULL,
  slug          VARCHAR(30)  NOT NULL UNIQUE,
  domain        VARCHAR(40)  NOT NULL DEFAULT 'wsap.me',
  name          VARCHAR(80)  NOT NULL,
  tagline       VARCHAR(160) NOT NULL DEFAULT '',
  about         TEXT         NOT NULL,
  logo          VARCHAR(255) NOT NULL DEFAULT '',
  cover         VARCHAR(255) NOT NULL DEFAULT '',
  accent        CHAR(7)      NOT NULL DEFAULT '#0b3d2e',
  phone         VARCHAR(16)  NOT NULL,
  currency      CHAR(3)      NOT NULL DEFAULT 'MYR',
  fields        TEXT         NOT NULL,          -- JSON {name:"req", phone:"req", address:"on", city:"off", notes:"on"}
  shipping      TEXT         NOT NULL,          -- JSON [{name, price(sen), note}]
  coupons       TEXT         NOT NULL,          -- JSON [{code, type:percent|fixed, value}]
  payment_note  VARCHAR(300) NOT NULL DEFAULT '',
  is_open       TINYINT(1)   NOT NULL DEFAULT 1,
  active        TINYINT(1)   NOT NULL DEFAULT 1,
  premium_until DATETIME     NULL,
  views         INT UNSIGNED NOT NULL DEFAULT 0,
  created_at    DATETIME     NOT NULL,
  updated_at    DATETIME     NOT NULL,
  INDEX idx_shops_user (user_id),
  CONSTRAINT fk_shops_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  shop_id     INT UNSIGNED NOT NULL,
  name        VARCHAR(100) NOT NULL,
  description TEXT         NOT NULL,
  price       INT UNSIGNED NOT NULL DEFAULT 0,  -- sen
  image       VARCHAR(255) NOT NULL DEFAULT '',
  category    VARCHAR(60)  NOT NULL DEFAULT '',
  variants    TEXT         NOT NULL,            -- JSON [{name:"Size", required:true, multi:false, options:[{label, extra(sen)}]}]
  active      TINYINT(1)   NOT NULL DEFAULT 1,
  sort        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  created_at  DATETIME     NOT NULL,
  INDEX idx_products_shop (shop_id),
  CONSTRAINT fk_products_shop FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  shop_id     INT UNSIGNED NOT NULL,
  number      VARCHAR(12)  NOT NULL,            -- short public order number
  customer    TEXT         NOT NULL,            -- JSON {name, phone, address, city, notes}
  items       TEXT         NOT NULL,            -- JSON [{name, options, qty, unit, total}]
  subtotal    INT UNSIGNED NOT NULL,
  shipping_name VARCHAR(60) NOT NULL DEFAULT '',
  shipping    INT UNSIGNED NOT NULL DEFAULT 0,
  coupon      VARCHAR(30)  NOT NULL DEFAULT '',
  discount    INT UNSIGNED NOT NULL DEFAULT 0,
  total       INT UNSIGNED NOT NULL,
  status      VARCHAR(10)  NOT NULL DEFAULT 'pending',  -- pending | confirmed | completed | cancelled
  created_at  DATETIME     NOT NULL,
  INDEX idx_orders_shop (shop_id, created_at),
  CONSTRAINT fk_orders_shop FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
