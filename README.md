# Project Manager (Commented Copy)

This package contains a cleaner, commented version of the uploaded PHP project.

## Structure
- `config/` - application configuration
- `database/` - schema and seed data
- `includes/` - shared helpers and layout partials
- `public/` - page controllers and front-end assets

## Notes
- Core business logic is mostly procedural PHP.
- Authentication uses sessions and password hashing.
- CSRF protection is implemented for POST forms.
- Database access uses PDO with prepared statements.
- `includes/logging.php` appears redundant because logging logic also exists in `includes/functions.php`.
