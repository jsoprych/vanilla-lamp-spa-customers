# Vanilla SPA Customer Management System (Master-Detail Example)

A lightweight customer management application built with core web technologies.

## Features
- Master-detail customer view
- Customer contacts, addresses, and activity tracking
- Search functionality
- Responsive design using native CSS

## Core Technologies
- **Frontend**: Vanilla JavaScript, HTML5, CSS3
- **Backend**: PHP 8.3 (no frameworks)
- **Database**: SQLite (file-based)
- **Server**: Apache HTTP Server

## Installation

1. Ensure you have Apache and PHP installed:
```bash
# On Ubuntu/Debian
sudo apt install apache2 php sqlite3
```

2. Clone the repository to your web directory:
```bash
git clone https://github.com/yourusername/customer-system.git /var/www/html/customer-system
```

3. Set permissions:
```bash
chmod -R 755 /var/www/html/customer-system
chown -R www-data:www-data /var/www/html/customer-system
```

4. Access the application at:
`http://localhost/customer-system`

## Project Structure
```
customer-system/
├── assets/            # Static files
│   ├── js/            # JavaScript
│   ├── css/           # Stylesheets
│   └── images/        # Static images
├── api/               # PHP endpoints
├── database/          # SQLite database file
├── index.php          # Main entry point
└── .htaccess          # Apache configuration
```

## API Endpoints
All endpoints return JSON:

- `GET /api/customers.php` - List all customers
- `GET /api/customers.php?id={id}` - Get single customer
- `POST /api/customers.php` - Create new customer
- `PUT /api/customers.php?id={id}` - Update customer
- `DELETE /api/customers.php?id={id}` - Delete customer

## Database
The system uses a single SQLite file (`database/customers.db`) with the following schema:

```sql
CREATE TABLE customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE,
    phone TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Additional tables for contacts, addresses, etc.
```

## Key Design Principles
1. **No JavaScript frameworks** - Uses native DOM APIs
2. **No PHP frameworks** - Pure procedural PHP
3. **No build tools** - Zero compilation step
4. **No package managers** - All dependencies included in repo

## Browser Support
Works in all modern browsers (Chrome, Firefox, Safari, Edge) with no polyfills needed.
```

### .gitignore
```
# System files
.DS_Store
Thumbs.db

# Database
database/customers.db

# Server logs
error.log
access.log

# Development files
*.swp
*.bak
```

### Key Changes Made:
1. Removed all references to npm/TypeScript
2. Replaced build steps with simple Apache setup
3. Emphasized vanilla technologies in the stack
4. Simplified project structure to reflect LAMP conventions
5. Removed all third-party dependency management
6. Added clear design principles section
7. Simplified .gitignore to match the simpler stack
