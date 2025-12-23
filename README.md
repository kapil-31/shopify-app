# Shopify Embedded App – Dashboard Overview

This project is a Shopify Embedded App that connects to a merchant’s store, fetches Products and Collections using the Shopify Admin GraphQL API, stores them locally in a database, and displays key summary metrics in a Shopify Polaris-powered dashboard.

The app is built using Laravel (backend), React with Shopify Polaris (frontend), and Shopify App Bridge for authentication.

---

## Features Implemented (Assessment Scope)

- Shopify Embedded App with OAuth authentication
- Fetch Products and Collections via Shopify Admin **GraphQL API**
- Webhook for sync products
- Store Products, Collections, and their relationships locally
- Dashboard summary cards:
  - Total Products
  - Total Collections
  - Total Products inside Collections
  - Last Sync Time
- Collections table showing:
  - Collection title
  - Number of products in each collection
- Secure API access using Shopify session JWT
- UI built with Shopify Polaris components

---

## Tech Stack

**Backend**
- Laravel
- MySQL
- Shopify Admin GraphQL API

**Frontend**
- React 18
- Shopify Polaris
- Shopify App Bridge

---

## Shopify API Scopes Used

The following Shopify API scopes are required:

- read_products
- read_orders,

> Note: After adding or changing scopes, the app must be reinstalled for the new permissions to take effect.


## Environment Configuration (Necessary)
```env
APP_NAME=ShopifyDashboard
APP_ENV=local
APP_KEY=base64:GENERATED_KEY
APP_DEBUG=true
APP_URL=https://your-ngrok-url.ngrok-free.dev

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shopify_app
DB_USERNAME=root
DB_PASSWORD=

SHOPIFY_API_KEY=your_shopify_api_key
SHOPIFY_API_SECRET=your_shopify_api_secret
SHOPIFY_SCOPES=read_products
SHOPIFY_APP_URL=https://your-ngrok-url.ngrok-free.dev

VITE_SHOPIFY_API_KEY=your_shopify_api_key

```



## Setup & Installation Steps

Follow the steps below to set up and run the Shopify Embedded App locally.

### 1. Clone the Repository

```bash
git clone <repository-url>
cd shopify-app
composer install
composer install

cp .env.example .env
```

### 2. Generate Application Key
```bash
 php artisan key:generate
```

### 3. RUN Migration
```bash 
php artisan migrate
```

### 4. Expose the Application Using ngrok
Shopify requires a publicly accessible HTTPS URL for embedded apps.
```bash   
ngrok http 8000
  ```
  Copy the generated ngrok HTTPS URL and update it in:

 - Shopify Partner Dashboard (App URL  & Redirect URL look into env)

- .env file


### 5. Start the Backend Server
```bash
php artisan serve

```

### 6. Start the Frontend Development Server

```bash 
npm run dev

```

### 10. Install the App on a Shopify Store

 - Open Shopify Partner Dashboard

- Create or select a custom app

-  Set the following URLs:

-  App URL: https://<ngrok-url>

-  Redirect URL: https://<ngrok-url>/auth/callback

- Install the app on a development store


After installation, open the app from the Shopify Admin panel to access the dashboard.




## Architectural Explanation (Short)

The application follows a client–server architecture:

Shopify handles merchant authentication and embeds the app inside the Shopify Admin interface.

Shopify App Bridge provides short-lived session JWTs for secure API communication.

The backend validates session tokens and fetches data from the Shopify Admin GraphQL API.

Products, collections, and their relationships are normalized and stored locally in a relational database.

Dashboard metrics are computed from the local database to ensure fast rendering and reduced API usage.

The frontend uses Shopify Polaris to provide a native Shopify Admin user experience.



## Notes

This project intentionally limits scope to dashboard-level functionality for assessment purposes.

No background jobs or webhooks are used.

Data synchronization is triggered manually through API calls and  Webhook.