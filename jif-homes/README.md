# JIF HOMES - Daily Apartment Rental Web Application

A bilingual (Arabic/English) web application for daily apartment rentals built with PHP, MySQL, HTML5, CSS3, and JavaScript.

## Features

- **Bilingual Support**: Full Arabic (RTL) and English (LTR) language support
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **User Authentication**: Registration, login, and protected pages
- **Apartment Listings**: Browse, search, and filter apartments
- **Photo Galleries**: Multiple images per apartment with lightbox
- **Interactive Maps**: Location markers using Leaflet.js
- **Feedback System**: Customer feedback form with rating
- **Admin Dashboard**: Manage apartments, locations, users, and feedback
- **Security**: CSRF protection, XSS prevention, SQL injection prevention

## Directory Structure

```
jif-homes/
├── css/
│   ├── style.css          # Main stylesheet
│   └── admin.css          # Admin panel styles
├── database/
│   └── schema.sql         # Database schema and sample data
├── image/
│   └── apartments/        # Apartment images
├── includes/
│   ├── config.php         # Database config and helper functions
│   ├── translations.php   # Arabic/English translations
│   ├── header.php         # Site header
│   ├── footer.php         # Site footer
│   ├── admin_header.php   # Admin header
│   └── admin_footer.php   # Admin footer
├── pages/
│   ├── index.php          # Home page
│   ├── apartments.php     # Apartments listing
│   ├── apartment.php      # Apartment detail (protected)
│   ├── feedback.php       # Feedback form
│   ├── contact.php        # Contact page
│   ├── login.php          # Login page
│   ├── register.php       # Registration page
│   ├── logout.php         # Logout handler
│   ├── account.php        # User account (protected)
│   ├── admin/
│   │   ├── dashboard.php      # Admin dashboard
│   │   ├── apartments.php     # Manage apartments
│   │   ├── apartment-form.php # Add/Edit apartment
│   │   ├── locations.php      # Manage map locations
│   │   ├── bookings.php       # Manage bookings
│   │   ├── users.php          # Manage users
│   │   └── feedback.php       # Manage feedback
│   └── api/
│       └── favorites.php  # Favorites API
├── script/
│   ├── main.js            # Main JavaScript
│   └── validation.js      # Form validation
└── videos/                # Video files (if any)
```

## Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.3+)
- Apache/Nginx web server

### Setup Steps

1. **Clone/Upload Files**
   Upload all files to your web server's document root.

2. **Create Database**
   ```sql
   CREATE DATABASE jif_homes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import Schema**
   ```bash
   mysql -u root -p jif_homes < database/schema.sql
   ```

4. **Configure Database Connection**
   Edit `includes/config.php` and update:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'jif_homes');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

5. **Set Permissions**
   ```bash
   chmod 755 -R /path/to/jif-homes
   chmod 777 -R /path/to/jif-homes/image/apartments
   ```

6. **Configure Web Server**
   Point your domain to the project root and ensure `.php` files are processed.

## Default Admin Login

- **Email**: admin@jifhomes.com
- **Password**: Admin@123

⚠️ **Important**: Change the admin password immediately after first login!

## Pages Description

### Public Pages
- **Home**: Hero section, search, featured apartments, map
- **Apartments**: Filterable apartment listings
- **Feedback**: Customer feedback form with validation
- **Contact**: Contact information and form
- **Login/Register**: User authentication

### Protected Pages (Requires Login)
- **Apartment Detail**: Full apartment info, gallery, booking form
- **Account**: User profile, bookings, favorites

### Admin Pages (Requires Admin Role)
- **Dashboard**: Statistics and recent activity
- **Apartments**: CRUD operations for apartments
- **Locations**: Manage map markers
- **Bookings**: View and manage bookings
- **Users**: View registered users
- **Feedback**: View and manage customer feedback

## Security Features

1. **CSRF Protection**: All forms include CSRF tokens
2. **XSS Prevention**: Output is escaped with `htmlspecialchars()`
3. **SQL Injection Prevention**: PDO prepared statements
4. **Password Security**: Argon2ID hashing
5. **Session Security**: HTTP-only cookies, secure configuration

## Validation

- **Client-side**: JavaScript validation in `validation.js`
- **Server-side**: PHP validation mirrors client-side rules
- **Email Uniqueness**: Feedback form allows one entry per email

## Customization

### Adding Translations
Edit `includes/translations.php` to add/modify translations:
```php
$translations = [
    'ar' => ['key' => 'القيمة العربية'],
    'en' => ['key' => 'English Value']
];
```

### Changing Colors
Edit CSS variables in `css/style.css`:
```css
:root {
    --primary-gold: #C9A227;
    --primary-navy: #1B2838;
    /* ... */
}
```

## Browser Support

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## License

This project is created for educational purposes.

## Credits

- Maps: [Leaflet.js](https://leafletjs.com/)
- Fonts: [Google Fonts](https://fonts.google.com/) (Cairo, Tajawal, Playfair Display)
- Icons: Unicode Emoji
