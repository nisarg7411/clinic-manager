# Mini Clinic Manager

A digital health record & appointment booking system built with HTML, CSS, JavaScript, PHP, and MySQL.

## Features
- Role-based login: Patient / Doctor / Admin
- Patient: register, book appointments via live AJAX time-slot picker, view prescriptions
- Doctor: view appointments, add prescriptions, mark appointments complete
- Admin: add doctors, set weekly availability, view appointment analytics (Chart.js)
- Double-layer slot-conflict validation (JavaScript UX + PHP server-side enforcement + MySQL UNIQUE constraint)

## Local Setup (XAMPP)
1. Install [XAMPP](https://www.apachefriends.org/) and start Apache + MySQL.
2. Copy this `clinic-manager` folder into `htdocs/` (e.g. `C:/xampp/htdocs/clinic-manager`).
3. Open **phpMyAdmin** (http://localhost/phpmyadmin), create nothing manually — instead import `sql/schema.sql` directly (it creates the database itself).
4. Open `includes/config.php` and confirm DB credentials match your XAMPP setup (default root/no password works out of the box).
5. Visit `http://localhost/clinic-manager/` in your browser.

## Demo Logins (from seed data)
- Admin: `admin@clinic.com` / `admin123`
- Doctor: `ansari@clinic.com` / `doctor123`
- Doctor: `priya@clinic.com` / `doctor123`
- Patient: register your own via the Register page

## Deployment (Live Link)
Vercel does NOT support PHP + MySQL. Use one of these free PHP-friendly hosts instead:
- **InfinityFree** (free MySQL + PHP hosting) — https://infinityfree.net
- **000webhost** — https://www.000webhost.com
- **Railway** (PHP buildpack + MySQL plugin) — https://railway.app

Steps (general, for InfinityFree/000webhost):
1. Create a free account and a new hosting instance.
2. Create a MySQL database via their control panel (note host, DB name, username, password given to you).
3. Update `includes/config.php` with those new credentials.
4. Import `sql/schema.sql` into their phpMyAdmin.
5. Upload all project files via their File Manager or FTP (e.g. FileZilla).
6. Visit your assigned live URL (e.g. `https://yourproject.infinityfreeapp.com`).

## Folder Structure
```
clinic-manager/
├── index.php
├── login.php
├── register.php
├── logout.php
├── includes/config.php
├── css/style.css
├── sql/schema.sql
├── patient/ (dashboard, book_appointment, get_slots, view_prescription)
├── doctor/ (dashboard, add_prescription)
└── admin/ (dashboard, manage_doctors, manage_availability)
```
