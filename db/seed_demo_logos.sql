-- Logos for the six demo forms (SVGs shipped in assets/img/demo). Safe to re-run.
-- mysql -u kuantan1_wsap -p kuantan1_wsap < db/seed_demo_logos.sql
UPDATE forms SET logo = '/assets/img/demo/logo-homestay.svg'   WHERE slug = 'demo-homestay';
UPDATE forms SET logo = '/assets/img/demo/logo-clinic.svg'     WHERE slug = 'demo-clinic';
UPDATE forms SET logo = '/assets/img/demo/logo-pickleball.svg' WHERE slug = 'demo-pickleball';
UPDATE forms SET logo = '/assets/img/demo/logo-workshop.svg'   WHERE slug = 'demo-workshop';
UPDATE forms SET logo = '/assets/img/demo/logo-tuition.svg'    WHERE slug = 'demo-tuition';
UPDATE forms SET logo = '/assets/img/demo/logo-catering.svg'   WHERE slug = 'demo-catering';
SELECT CONCAT(COUNT(*), ' demo forms now have logos') AS result FROM forms WHERE slug LIKE 'demo-%' AND logo <> '';
