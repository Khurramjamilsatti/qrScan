# QRScan.digital — MVP SaaS Platform

Dynamic QR codes, branded short links, and digital business cards with analytics.

## Tech Stack

- **Frontend:** Vue 3 + Vite + Tailwind CSS v4 + Pinia + Vue Router
- **Backend:** Laravel 11 API + Sanctum
- **Database:** PostgreSQL (SQLite for quick local dev)
- **QR Generation:** simplesoftwareio/simple-qrcode
- **Payments:** Stripe Billing (ready to wire up)

## Quick Start

### 1. Backend

```bash
cd backend
cp .env.example .env   # sdf already configured for SQLite by default
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

API runs at `http://localhost:8000`

### 2. Frontend

```bash
cd frontend
npm install
npm run dev
```

App runs at `http://localhost:5173`

### 3. PostgreSQL (optional)

```bash
docker compose up -d
```

Then update `backend/.env`:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=qrscan
DB_USERNAME=qrscan
DB_PASSWORD=secret
```

Run `php artisan migrate --seed` again.

## Demo Accounts


| Role  | Email                                               | Password |
| ----- | --------------------------------------------------- | -------- |
| Admin | [admin@qrscan.digital](mailto:admin@qrscan.digital) | password |
| User  | [demo@qrscan.digital](mailto:demo@qrscan.digital)   | password |




## Features



### Landing Page (CMS-driven)

- Hero, stats, features, pricing, testimonials, and CTA sections
- All content editable from **Admin → Landing Page**
- Modern interactive design with animations



### Application

- **QR Codes** — dynamic, color-customizable, PNG/SVG download
- **Short Links** — custom slugs, UTM parameters, click tracking
- **Digital Business Cards** — shareable profiles with theme colors
- **Billing** — plan comparison with Stripe integration placeholder
- Plan-based limits enforced on the API



### Admin Panel (`/admin`)

- Dashboard with platform stats
- Landing page content editor (hero, stats, features, pricing, CTA)
- User management with plan assignment



## Pricing Tiers


| Plan     | Price | QR Codes  | Links     | Cards | Scans/mo  |
| -------- | ----- | --------- | --------- | ----- | --------- |
| Free     | $0    | 1         | 3         | 1     | 100       |
| Starter  | $6    | 10        | Unlimited | 5     | 5,000     |
| Pro      | $20   | Unlimited | Unlimited | ∞     | 50,000    |
| Business | $50   | Unlimited | Unlimited | ∞     | Unlimited |




## API Endpoints


| Method | Endpoint            | Description           |
| ------ | ------------------- | --------------------- |
| GET    | /api/landing        | Public landing CMS    |
| POST   | /api/register       | Register              |
| POST   | /api/login          | Login                 |
| GET    | /api/dashboard      | User dashboard stats  |
| CRUD   | /api/qr-codes       | QR code management    |
| CRUD   | /api/short-links    | Short link management |
| CRUD   | /api/business-cards | Business cards        |
| GET    | /api/r/{slug}       | Short link redirect   |
| GET    | /api/qr/{code}      | QR code redirect      |
| GET    | /api/card/{slug}    | Business card JSON    |
| *      | /api/admin/*        | Admin CMS & users     |




## Stripe Setup

Add to `backend/.env`:

```
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```



### Local webhook testing

```bash
stripe listen --forward-to localhost:8000/api/stripe/webhook
```

Copy the `whsec_...` signing secret into `STRIPE_WEBHOOK_SECRET`.

Users upgrade from **App → Billing** via Stripe Checkout. Plan changes are applied via the `/api/stripe/webhook` endpoint (`checkout.session.completed`, `customer.subscription.updated`, `customer.subscription.deleted`).

## Project Structure

```
qrscan.digital/
├── backend/          # Laravel API
├── frontend/         # Vue 3 SPA
├── docker-compose.yml
└── README.md
```

