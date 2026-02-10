# PizzaPlanet 🍕

A full-stack pizza ordering web application built with Laravel 12, featuring a customer ordering site and an admin management panel.

## Tech Stack

- **Framework:** Laravel 12
- **Language:** PHP 8.4
- **Database:** MySQL 8.0
- **Containerization:** Docker + Docker Compose
- **Admin Panel:** Livewire 3
- **Customer Site:** Blade + Controllers (no Livewire)
- **Styling:** Tailwind CSS (via CDN for simplicity)

## Quick Start

### Run with Docker (Recommended)

```bash
# Clone the repository
cd pizzaPlanet

# Start the application (single command)
docker-compose up --build -d
```

The application will:
1. Build the PHP container with all dependencies
2. Start MySQL and wait for it to be healthy
3. Run migrations and seeders automatically
4. Start the Laravel dev server on port 8000

**Access the app:**
- Customer Site: http://localhost:8000
- Admin Panel: http://localhost:8000/admin/login

### Default Admin Credentials

| Field    | Value                  |
|----------|------------------------|
| Email    | admin@pizzaplanet.com  |
| Password | password               |

## Features

### Customer Side (Blade + Controllers)
- Browse pizza menu with predefined options
- View pizza details with customization options
- Build your own custom pizza (size, crust, toppings)
- Dynamic topping selection
- Full cart system (add, update quantity, remove items)
- Session-based cart persistence
- Checkout with customer information
- Mock payment system:
  - **Credit Card:** Any 16-digit number works. Numbers ending in `0000` simulate failure.
  - **PayPal:** Any email works. Emails containing `fail` simulate failure.
- Order confirmation page

### Admin Panel (Livewire)
- Admin authentication with middleware protection
- **Dashboard:** Stats overview (pizzas, toppings, orders, revenue)
- **Pizza Management:** Full CRUD, toggle active/inactive, assign toppings
- **Topping Management:** Full CRUD, toggle active/inactive
- **Order Management:** View all orders, filter by status, view order details, update order status

## Architecture

### Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/AuthController.php    # Admin login/logout
│   │   ├── MenuController.php          # Pizza menu & detail pages
│   │   ├── CartController.php          # Cart CRUD operations
│   │   └── CheckoutController.php      # Checkout & payment flow
│   ├── Middleware/
│   │   └── AdminMiddleware.php         # Admin route protection
│   └── Requests/
│       ├── AddToCartRequest.php        # Cart validation
│       ├── CheckoutRequest.php         # Checkout validation
│       └── PaymentRequest.php          # Payment validation
├── Livewire/Admin/
│   ├── Dashboard.php                   # Admin dashboard
│   ├── PizzaManager.php                # Pizza CRUD
│   ├── ToppingManager.php              # Topping CRUD
│   └── OrderManager.php                # Order management
├── Models/
│   ├── User.php
│   ├── Pizza.php
│   ├── Topping.php
│   ├── CartItem.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Payment.php
│   └── ActivityLog.php
└── Services/
    ├── ActivityLogService.php          # Logging & tracking
    ├── CartService.php                 # Cart business logic
    ├── OrderService.php                # Order creation
    └── PaymentService.php              # Mock payment processing
```

### Key Design Decisions

- **MVC Pattern:** Controllers handle HTTP, Services handle business logic, Models handle data
- **Form Requests:** All input validation uses dedicated FormRequest classes
- **Service Layer:** CartService, OrderService, PaymentService encapsulate business logic
- **Activity Logging:** Every important action is logged to both database and file
- **Session-based Cart:** Cart persists via session ID for guests, user ID for authenticated users

### Database Schema

- `users` - Admin and customer accounts
- `pizzas` - Pizza definitions with base price, size, crust
- `toppings` - Available toppings with prices
- `pizza_toppings` - Many-to-many: default toppings per pizza
- `cart_items` - Shopping cart items
- `cart_item_toppings` - Toppings selected for cart items
- `orders` - Customer orders
- `order_items` - Individual items within an order
- `order_item_toppings` - Snapshot of toppings at time of order
- `payments` - Payment records (mock)
- `activity_logs` - Audit trail for all actions

## Logging

All important actions are logged including:
- Pizza CRUD operations
- Topping CRUD operations
- Cart add/update/remove
- Order creation
- Payment attempts (success/failure)

Logs include: action name, user ID, session ID, timestamp, entity IDs, and relevant details.

- **File logs:** `storage/logs/activity.log`
- **Database logs:** `activity_logs` table

## Docker Configuration

| Service | Container Name      | Port  |
|---------|---------------------|-------|
| App     | pizzaplanet-app     | 8000  |
| MySQL   | pizzaplanet-mysql   | 3307  |

## License

MIT
