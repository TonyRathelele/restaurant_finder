# Restaurant Finder Web Application

A comprehensive restaurant discovery platform built with PHP and MySQL, featuring both user-facing frontend and admin management system.

## Features

### Frontend (User Side)
- **Landing Page**: Video background with hero section and call-to-action
- **Restaurant Directory**: Browse restaurants with filtering by location and search functionality
- **Restaurant Details**: Detailed restaurant pages with menus, location, and contact information
- **Responsive Design**: Mobile-friendly interface with modern UI/UX

### Backend (Admin Side)
- **Authentication System**: Secure admin login with session management
- **Dashboard**: Overview of restaurants, menu items, and statistics
- **Restaurant Management**: Add, edit, delete restaurants with full details
- **Menu Management**: Manage menu items with categories and pricing
- **Category Management**: Organize menu items into categories

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Icons**: Font Awesome 6
- **Fonts**: Google Fonts (Playfair Display, Inter)

## Installation & Setup

### Prerequisites
- XAMPP/WAMP/LAMP server
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

### Step 1: Database Setup
1. Start your XAMPP/WAMP server
2. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
3. Create a new database called `restaurant_finder`
4. Import the database schema:
   - Go to the `Import` tab
   - Choose the file `database/schema.sql`
   - Click `Go` to import

### Step 2: File Setup
1. Copy the project files to your web server directory:
   - For XAMPP: `C:\xampp\htdocs\restaurant_finder\`
   - For WAMP: `C:\wamp64\www\restaurant_finder\`
   - For LAMP: `/var/www/html/restaurant_finder/`

2. Set proper permissions for upload directories:
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/restaurants/
   chmod 755 uploads/menu/
   ```

### Step 3: Configuration
1. Update database credentials in `config/database.php` if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'restaurant_finder');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

2. Update base URL in `config/config.php` if needed:
   ```php
   define('BASE_URL', 'http://localhost/restaurant_finder/');
   ```

### Step 4: Access the Application
1. **Frontend**: Visit `http://localhost/restaurant_finder/`
2. **Admin Panel**: Visit `http://localhost/restaurant_finder/admin/`

### Default Admin Credentials
- **Username**: `admin`
- **Password**: `admin123`

## Project Structure

```
restaurant_finder/
├── admin/                  # Admin panel files
│   ├── login.php          # Admin login page
│   ├── logout.php         # Logout functionality
│   ├── dashboard.php      # Admin dashboard
│   ├── restaurants.php    # Restaurant management
│   ├── menus.php          # Menu management
│   └── categories.php     # Category management
├── config/                # Configuration files
│   ├── config.php         # Main configuration
│   └── database.php       # Database connection
├── database/              # Database files
│   └── schema.sql         # Database schema
├── uploads/               # File uploads directory
│   ├── restaurants/       # Restaurant images
│   └── menu/             # Menu item images
├── index.php             # Landing page
├── restaurants.php       # Restaurant directory
├── restaurant.php        # Restaurant detail page
└── README.md            # This file
```

## Usage Guide

### For Users
1. **Browse Restaurants**: Visit the homepage to see featured restaurants
2. **Search & Filter**: Use the search functionality to find restaurants by name, cuisine, or location
3. **View Details**: Click on any restaurant to see detailed information, menu, and location
4. **Contact Information**: Find phone numbers, addresses, and opening hours

### For Admins
1. **Login**: Access the admin panel with the provided credentials
2. **Dashboard**: View statistics and recent activity
3. **Manage Restaurants**: Add new restaurants or edit existing ones
4. **Manage Menus**: Add menu items and organize them by categories
5. **Manage Categories**: Create and organize menu categories

## Features in Detail

### Restaurant Management
- Add/edit restaurant information (name, description, location, contact details)
- Set ratings and coordinates for mapping
- Manage restaurant status (active/inactive)
- Upload restaurant images and videos

### Menu Management
- Create menu items with descriptions and pricing
- Organize items by categories (Starters, Main Course, Desserts, Drinks)
- Set availability status for menu items
- Sort items within categories

### Search & Filtering
- Search restaurants by name or cuisine type
- Filter by location (Rosebank, Sandton, etc.)
- Sort by rating, name, or location
- Responsive search interface

### Responsive Design
- Mobile-first approach
- Bootstrap 5 framework
- Custom CSS with modern design
- Smooth animations and transitions

## Customization

### Adding New Locations
1. Add new locations to the database in the `restaurants` table
2. The system will automatically include them in filter dropdowns

### Styling Customization
- Modify CSS variables in the `:root` section of each page
- Update color scheme by changing the CSS custom properties
- Add custom fonts by updating the Google Fonts import

### Adding Features
- The modular structure makes it easy to add new features
- Database schema can be extended for additional functionality
- Admin panel can be expanded with new management sections

## Security Features

- Password hashing using PHP's `password_hash()` function
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()` and `sanitize()` function
- Session management for admin authentication
- Input validation and sanitization

## Browser Support

- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check if MySQL is running
   - Verify database credentials in `config/database.php`
   - Ensure database `restaurant_finder` exists

2. **File Upload Issues**
   - Check directory permissions for `uploads/` folder
   - Ensure PHP file uploads are enabled
   - Check `upload_max_filesize` in PHP configuration

3. **Admin Login Issues**
   - Verify default credentials: admin/admin123
   - Check if sessions are working properly
   - Clear browser cache and cookies

4. **Page Not Loading**
   - Check if web server is running
   - Verify file paths and permissions
   - Check PHP error logs

## Future Enhancements

- User registration and authentication
- Online reservation system
- Restaurant reviews and ratings
- Food ordering and delivery
- Payment integration
- Mobile app development
- Advanced search with filters
- Restaurant analytics dashboard

## Support

For support and questions, please check the troubleshooting section or review the code comments for implementation details.

## License

This project is open source and available under the MIT License.
