# Archon Publishing House

A framework-free PHP 8.2+ website for authors seeking eBook writing and publishing services. The application uses PDO/MySQL, HTML/CSS/vanilla JavaScript and an Apache front controller; no Node.js or paid dependency is required.

## Included

- Responsive client-services site, authors, services, quote requests, contact form, newsletter and journal.
- A keyboard/swipe-friendly, reduced-motion-aware interactive services book.
- The public navigation is intentionally services-only: no client sign-in, bag, cart, checkout, or store route is exposed.
- Responsive admin dashboard and protected management for authors, services, posts, enquiries, contact messages, and supporting content.
- MySQL schema, original demo content, security headers, CSRF, password hashing, sessions, basic session rate limits and private storage. Dormant marketplace tables and code are retained but disabled by default.

## Requirements

- PHP 8.2+ with `pdo_mysql`, `fileinfo`, `openssl`.
- MySQL 8+ or MariaDB 10.5+.
- Apache with `mod_rewrite` and `AllowOverride All`.

## Install locally

1. Copy `.env.example` to `.env` and set `APP_URL` and your database credentials.
2. Create the database and import the schema:

   ```sh
   mysql -u root -p < database/schema.sql
   ```

3. Seed demo data. Supply your own local-only administrator password; it is deliberately not stored in source control.

   ```sh
   set SEED_ADMIN_EMAIL=admin@example.test
   set SEED_ADMIN_PASSWORD=Use-a-long-local-password
   php database/seed.php
   ```

   PowerShell: `$env:SEED_ADMIN_PASSWORD='Use-a-long-local-password'; php database/seed.php`.

4. Point the Apache virtual host document root to `public/`, or browse the project using your local PHP/Apache stack. In development you can use `php -S localhost:8000 -t public public/index.php`.
5. Open `http://localhost:8000`. Administrators sign in at `/admin/login`; customer sign-in and marketplace routes are intentionally unavailable.

## Shared hosting / cPanel

- Set the domain document root to `public/`. If this is not possible, retain the root `.htaccess` forwarding rules, but keep `app`, `config`, `database`, `storage` and `private` outside `public_html` when your host permits it.
- Copy `.env.example` to `.env`, set `APP_ENV=production`, `APP_DEBUG=false`, a long `APP_KEY`, production DB details, `APP_URL`, `SESSION_SECURE=true`, and HTTPS.
- Ensure PHP can write to `storage/logs` and `public/uploads`; keep `private/books`, `private/samples` and `private/quote-attachments` non-public.
- Import `database/schema.sql`, seed only for a demonstration installation, then replace/delete demo content before launch.

## Dormant marketplace and mail

The retained store, customer-account, checkout, payment and download code is disabled while `MARKETPLACE_ENABLED=false`. Do not enable it without a separate security, payment and fulfilment review.

`MAIL_DRIVER=log` writes development reset tokens to `storage/logs/mail.log`. Add SMTP transport credentials and production email templates before launch.

## Logo and final client inputs

The supplied parchment logo is retained at `public/assets/images/brand/archon-logo-source.jpg` and optimized for the site at `public/assets/images/brand/archon-logo-parchment.webp`. The newer transparent company logo is retained at `public/assets/images/brand/archon-logo-transparent.png` and is used on the interactive front cover. The scanner overlay in the parchment source has deliberately not been altered. Supply an official vector version when available, along with the final domain, email/phone/WhatsApp, address, SMTP, social profiles and final legal policy text.

## Security / operations checklist

- Use HTTPS, secure cookies and `APP_DEBUG=false` in production.
- Back up database and private storage routinely.
- Replace all demo users/content and rotate passwords.
- Validate and malware-scan any future uploads; add the quote attachment controller only when server-side scanning/storage policy is available.
- Review CSP when adding external maps, analytics, payment providers or fonts.

## Verification

Run `php -l` over all PHP files. Import the schema and seed it before testing public service routes, enquiry forms and admin screens in a MySQL-enabled environment.
