# User Management API with Laravel 12 & Sanctum

This project is a simple RESTful API for managing users using **Laravel 12** and **Laravel Sanctum**. It includes user authentication (register, login, logout), token expiration, and full CRUD operations for users with request validation and unit tests.

## Features

* ✅ Auth: Register, Login, Logout (with token expiration)
* 🔐 Sanctum token-based authentication
* 🔄 User CRUD with validation
* 🔍 Pagination and search
* 🧪 Unit tests

## Requirements

* PHP >= 8.3
* Laravel 12
* Laravel Sanctum
* MySQL
* Composer

## Setup

### Clone repo
```bash
git clone https://github.com/aditiasaputra/user-app-api.git
```

### Change directory
```bash
cd user-app-api
```

### Install dependencies
```bash
composer install
```

### Copy .env and generate key
```bash
cp .env.example .env
php artisan key:generate
```

### Set database driver to MySQL in your .env
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Run migrations
```bash
php artisan migrate
```

### Serve the app
```bash
php artisan serve
```

## Sanctum Token Expiration

Configure `config/sanctum.php`:

```php
'expiration' => 60, // Token valid for 60 minutes
```

## API Routes

| Method | Endpoint        | Description             |
| ------ | --------------- | ----------------------- |
| POST   | /api/register   | Register user           |
| POST   | /api/login      | Login user              |
| POST   | /api/logout     | Logout (token required) |
| GET    | /api/users      | List users (paginated)  |
| POST   | /api/users      | Create user             |
| GET    | /api/users/{id} | Get user details        |
| PUT    | /api/users/{id} | Update user             |
| DELETE | /api/users/{id} | Delete user             |

## Testing

### Enable Xdebug for Code Coverage

Make sure Xdebug is installed and properly configured in `php.ini`.
```bash
php -v
```
Make sure `php.ini` has:
```ini
zend_extension=php_xdebug.dll

[xdebug]
xdebug.mode=coverage
xdebug.start_with_request=yes
```

## Unit Test
### Run Tests
```bash
php artisan test
```

### With coverage
```bash
php artisan test --coverage
```

### Or with code coverage
```bash
vendor/bin/phpunit --coverage-html coverage/
```

### Or with code coverage (for Windows)
```bash
vendor\bin\phpunit --coverage-html coverage\
```

## Author

Created by \[Muhamad Aditia Saputra], 2025.
