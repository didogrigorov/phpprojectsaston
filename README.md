# 📁 Project Manager Web Application

## 🧭 Overview

This Project Manager is a lightweight, PHP-based web application designed to help users manage projects efficiently. It provides essential features such as project creation, editing, deletion, user authentication, and dashboard visualization.

The system follows a modular structure with reusable components, making it easy to maintain, extend, and deploy.

---

## Features

### Authentication & User Management

* User registration and login system
* Secure password handling
* Session-based authentication
* Profile editing functionality

### Project Management

* Create, edit, and delete projects
* View detailed project information
* Search and filter projects
* Export project data

### Admin & Logs

* Admin dashboard
* System activity logging
* Log viewer interface

### Security

* CSRF protection
* Input validation
* Secure database interactions (PDO/MySQLi)
* Authentication guards for protected pages

### Frontend

* Clean and minimal UI
* Custom CSS styling
* JavaScript validation

---

## Project Structure

```
project-manager/
│
├── config/
│   └── config.php          # Application configuration (DB, constants)
│
├── database/
│   └── aproject.sql        # Database schema
│
├── includes/
│   ├── auth.php            # Authentication helpers
│   ├── csrf.php            # CSRF protection utilities
│   ├── db.php              # Database connection
│   ├── functions.php       # Common helper functions
│   ├── logging.php         # Logging system
│   ├── header.php          # Shared header template
│   └── footer.php          # Shared footer template
│
├── public/
│   ├── index.php           # Entry point / landing page
│   ├── login.php
│   ├── register.php
│   ├── dashboard.php
│   ├── project.php
│   ├── add_project.php
│   ├── edit_project.php
│   ├── delete_project.php
│   ├── profile.php
│   ├── edit_profile.php
│   ├── admin.php
│   ├── logs.php
│   ├── search.php
│   ├── export_projects.php
│   ├── logout.php
│   │
│   └── assets/
│       ├── css/style.css
│       └── js/validation.js
│
└── README.md
```

---

## ⚙️ Installation Guide

### 1. Clone or Download

```bash
git clone <your-repo-url>
```

Or extract the ZIP file into your web server directory (e.g., `htdocs` or `www`).

---

### 2. Setup Database

1. Create a MySQL database:

```sql
CREATE DATABASE project_manager;
```

2. Import the schema:

```bash
mysql -u root -p project_manager < database/aproject.sql
```

---

### 3. Configure Environment

Edit:

```
config/config.php
```

Update:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'project_manager');
define('DB_USER', 'root');
define('DB_PASS', '');
```

---

### 4. Run the Application

Place the project in your server root:

* XAMPP → `htdocs/`
* WAMP → `www/`

Then open:

```
http://localhost/project-manager/public/
```

---

## Architecture Overview

### 🔹 Backend

* PHP (procedural + modular structure)
* Separation of concerns using `includes/`
* Centralized database connection

### 🔹 Frontend

* HTML + CSS + Vanilla JavaScript
* Minimal dependencies for simplicity

### 🔹 Security Layer

* CSRF tokens for form protection
* Authentication middleware (`auth.php`)
* Input sanitization and validation

---

## Application Flow

1. User visits `index.php`
2. Authenticated users are redirected to `dashboard.php`
3. Users can:

   * Manage projects
   * Update profile
   * Search/export data
4. Admin users access `admin.php` and logs

---

## Validation & Error Handling

* Client-side validation via `validation.js`
* Server-side validation in PHP
* Graceful error handling with messages

---

## Key Modules Explained

### `auth.php`

Handles:

* Login checks
* Session validation
* Access restriction

### `csrf.php`

* Generates tokens
* Validates form submissions

### `db.php`

* Establishes database connection
* Reusable across all modules

### `functions.php`

* Utility helpers (sanitization, formatting, etc.)

### `logging.php`

* Logs system events
* Useful for debugging and auditing

---

## Export Functionality

Users can export project data for reporting or backup purposes via:

```
export_projects.php
```

---

## 🔍 Search System

* Keyword-based project search
* Lightweight filtering logic
* Accessible via `search.php`

---

## Development Notes

### Coding Style

* Modular PHP structure
* Reusable includes
* Clear separation of logic and layout

### Suggested Improvements

* Add MVC framework (e.g., Laravel)
* Introduce REST API
* Add role-based permissions
* Implement AJAX for smoother UX
* Add unit tests

---

## Troubleshooting

### Database Connection Error

* Check credentials in `config.php`
* Ensure MySQL is running

### Page Not Found

* Verify `.htaccess` configuration
* Ensure correct base URL

### Session Issues

* Check PHP session settings
* Ensure cookies are enabled

---

## Security Considerations

* Never expose `config.php`
* Use HTTPS in production
* Escape all user inputs
* Regularly update dependencies

---

## Future Enhancements

* Task-level project management
* File uploads
* Notifications system
* API integration
* Responsive UI improvements

---

## Contributing

1. Fork the project
2. Create a feature branch
3. Commit your changes
4. Submit a pull request

---

## License

This project is open-source and available under the MIT License.

---

## Author

Developed as a structured PHP project for learning and practical implementation of:

* Authentication systems
* CRUD operations
* Secure web development

---

## Final Notes

This project is intentionally kept lightweight and framework-free to:

* Help beginners understand core PHP concepts
* Provide a clean base for expansion
* Demonstrate best practices in a simple environment

---

**Happy Coding**
