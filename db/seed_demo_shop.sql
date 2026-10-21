-- Demo shop "The Golden Bakery" for the FIRST user account (the admin), published for 1 year.
-- Run once:  mysql -u kuantan1_wsap -p kuantan1_wsap < db/seed_demo_shop.sql
-- Safe to re-run: it skips if a shop with slug 'bakery' already exists.
SET NAMES utf8mb4;
SET @uid := (SELECT id FROM users ORDER BY id LIMIT 1);
SET @exists := (SELECT COUNT(*) FROM shops WHERE slug = 'bakery');

INSERT INTO shops (user_id, slug, domain, name, tagline, about, logo, cover, accent, instagram, facebook, tiktok, hours_text, phone, currency, fields, shipping, coupons, payment_note, is_open, active, premium_until, views, created_at, updated_at)
SELECT @uid, 'bakery', 'wsap.me', 'The Golden Bakery',
  'Artisan breads and pastries baked fresh every day. Order via WhatsApp!',
  'Small-batch sourdough, French pastries and good coffee from our bakery in Kuantan. Order before 10am for same-day pickup.',
  'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&q=80&auto=format&fit=crop',
  'https://images.unsplash.com/photo-1517433670267-08bbd4be890f?w=1600&q=80&auto=format&fit=crop',
  '#b45309', 'https://instagram.com/wsap.me', 'https://facebook.com/wsap.me', '', 'Mon–Sat 8am–6pm',
  '60145866666', 'MYR',
  '{"name":"req","phone":"req","address":"on","city":"off","notes":"on"}',
  '[{"name":"Store pickup","price":0,"note":"Ready in 30 min"},{"name":"Delivery (Kuantan)","price":500,"note":"Same day, order before 2pm"}]',
  '[{"code":"WELCOME10","type":"percent","value":10}]',
  'Pay by bank transfer, Touch ''n Go or cash on pickup. We confirm your order in WhatsApp.',
  1, 1, DATE_ADD(NOW(), INTERVAL 1 YEAR), 0, NOW(), NOW()
WHERE @exists = 0;

SET @sid := (SELECT id FROM shops WHERE slug = 'bakery');

INSERT INTO categories (shop_id, name, description, image, sort, created_at)
SELECT @sid, 'Breads', 'Freshly baked every morning', 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=600&q=80&auto=format&fit=crop', 0, NOW() WHERE @exists = 0;
INSERT INTO categories (shop_id, name, description, image, sort, created_at)
SELECT @sid, 'Pastries', 'Sweet treats for any occasion', 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=600&q=80&auto=format&fit=crop', 1, NOW() WHERE @exists = 0;
INSERT INTO categories (shop_id, name, description, image, sort, created_at)
SELECT @sid, 'Drinks', 'Hot and cold beverages', 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=600&q=80&auto=format&fit=crop', 2, NOW() WHERE @exists = 0;

SET @c_bread := (SELECT id FROM categories WHERE shop_id = @sid AND name = 'Breads');
SET @c_pastry := (SELECT id FROM categories WHERE shop_id = @sid AND name = 'Pastries');
SET @c_drink := (SELECT id FROM categories WHERE shop_id = @sid AND name = 'Drinks');

INSERT INTO products (shop_id, name, description, price, image, category, category_id, variants, active, sort, created_at)
SELECT @sid, 'Sourdough Loaf', 'Naturally leavened, 24-hour fermented. Crackly crust, open crumb.', 1250, 'https://images.unsplash.com/photo-1585478259715-876acc5be8eb?w=800&q=80&auto=format&fit=crop', '', @c_bread, '[]', 1, 0, NOW() WHERE @exists = 0;
INSERT INTO products (shop_id, name, description, price, image, category, category_id, variants, active, sort, created_at)
SELECT @sid, 'French Baguette', 'Crisp, golden and baked twice a day.', 650, 'https://images.unsplash.com/photo-1597079910443-60c43fc4f729?w=800&q=80&auto=format&fit=crop', '', @c_bread, '[]', 1, 1, NOW() WHERE @exists = 0;
INSERT INTO products (shop_id, name, description, price, image, category, category_id, variants, active, sort, created_at)
SELECT @sid, 'Ciabatta', 'Light, airy Italian bread. Perfect for sandwiches.', 750, 'https://images.unsplash.com/photo-1586444248902-2f64eddc13df?w=800&q=80&auto=format&fit=crop', '', @c_bread, '[]', 1, 2, NOW() WHERE @exists = 0;
INSERT INTO products (shop_id, name, description, price, image, category, category_id, variants, active, sort, created_at)
SELECT @sid, 'Butter Croissant', 'Flaky, buttery layers of perfection.', 550, 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=800&q=80&auto=format&fit=crop', '', @c_pastry,
  '[{"name":"Filling","multi":false,"options":[{"label":"Plain","extra":0},{"label":"Chocolate","extra":150},{"label":"Almond cream","extra":200}]}]', 1, 3, NOW() WHERE @exists = 0;
INSERT INTO products (shop_id, name, description, price, image, category, category_id, variants, active, sort, created_at)
SELECT @sid, 'Cinnamon Roll', 'Soft, gooey and glazed while warm.', 650, 'https://images.unsplash.com/photo-1509365465985-25d11c17e812?w=800&q=80&auto=format&fit=crop', '', @c_pastry, '[]', 1, 4, NOW() WHERE @exists = 0;
INSERT INTO products (shop_id, name, description, price, image, category, category_id, variants, active, sort, created_at)
SELECT @sid, 'Blueberry Muffin', 'Bursting with fresh blueberries.', 600, 'https://images.unsplash.com/photo-1607958996333-41aef7caefaa?w=800&q=80&auto=format&fit=crop', '', @c_pastry,
  '[{"name":"Size","multi":false,"options":[{"label":"Regular","extra":0},{"label":"Jumbo","extra":250}]},{"name":"Extras","multi":true,"options":[{"label":"Streusel top","extra":100},{"label":"Extra berries","extra":150}]}]', 1, 5, NOW() WHERE @exists = 0;
INSERT INTO products (shop_id, name, description, price, image, category, category_id, variants, active, sort, created_at)
SELECT @sid, 'Caffè Latte', 'Double shot with silky steamed milk.', 900, 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=800&q=80&auto=format&fit=crop', '', @c_drink,
  '[{"name":"Size","multi":false,"options":[{"label":"Regular","extra":0},{"label":"Large","extra":200}]},{"name":"Milk","multi":false,"options":[{"label":"Fresh milk","extra":0},{"label":"Oat milk","extra":150}]}]', 1, 6, NOW() WHERE @exists = 0;
INSERT INTO products (shop_id, name, description, price, image, category, category_id, variants, active, sort, created_at)
SELECT @sid, 'Fresh Orange Juice', 'Squeezed to order. No sugar added.', 850, 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=800&q=80&auto=format&fit=crop', '', @c_drink, '[]', 1, 7, NOW() WHERE @exists = 0;

-- the demo uses the premium word 'bakery': assign it to the owner so it stays editable
UPDATE premium_slugs SET assigned_user_id = @uid WHERE slug = 'bakery';

SELECT CONCAT('Demo shop ready: https://wsap.me/bakery (owner user #', @uid, ')') AS result;
