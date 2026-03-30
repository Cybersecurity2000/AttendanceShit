# QRCodex - QR Code Attendance System

A modern QR code-based attendance management system built with PHP and MySQL.

## Features

- **Admin Dashboard**: Monitor attendance statistics and recent activity
- **Student Management**: Add, view, edit, and delete students
- **QR Code Generation**: Generate unique QR codes for each student
- **QR Scanner**: Scan QR codes using device camera to record attendance
- **Attendance Monitoring**: View all attendance records with date filtering
- **Export Functionality**: Export attendance data to CSV/Excel
- **Responsive Design**: Works on desktop and mobile devices

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP, WAMP, or any local web server
- Modern web browser (Chrome, Firefox, Safari, Edge)
- Camera access (for QR scanning)

## Installation

### 1. Setup Database

1. Open phpMyAdmin (usually http://localhost/phpmyadmin)
2. Create a new database named `qrcodex_db`
3. Click on the database, then go to "Import" tab
4. Import the file `database/schema.sql`

Or run this SQL command:
```sql
CREATE DATABASE IF NOT EXISTS qrcodex_db;
USE qrcodex_db;
-- Then import the schema.sql file
```

### 2. Configure Database Connection

Edit `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'qrcodex_db');
define('DB_USER', 'root');
define('DB_PASS', '');  // Your MySQL password if any
```

### 3. Access the Application

1. Place all files in your web server's htdocs folder
2. For XAMPP: `C:\xampp\htdocs\QRCODEX\`
3. Open browser and go to: `http://localhost/QRCODEX/`

## Default Admin Login

- **Username**: `admin`
- **Password**: `admin123`

⚠️ **Important**: Change the default password after first login!

## Project Structure

```
QRCODEX/
├── admin/
│   ├── dashboard.php      # Admin dashboard
│   ├── login.php          # Admin login page
│   ├── logout.php         # Logout handler
│   ├── students.php       # Student list
│   ├── add-student.php    # Add new student
│   ├── view-student.php   # View student details
│   ├── attendance.php     # Attendance records
│   └── qr-generator.php   # QR code generator
├── assets/
│   └── css/
│       └── style.css      # Custom styles
├── config/
│   └── config.php         # Database configuration
├── database/
│   └── schema.sql         # Database schema
├── includes/
│   ├── header.php         # Header template
│   ├── footer.php         # Footer template
│   └── functions.php      # Helper functions
├── index.php              # Home page
└── scan.php               # QR scanner page
```

## Usage Guide

### For Administrators

1. **Login**: Go to `/admin/login.php` and enter credentials
2. **Add Students**: Navigate to "Add Student" and fill in details
3. **Generate QR Codes**: Go to "QR Generator" and select a student
4. **Print/Download QR**: Use the print or download buttons
5. **Monitor Attendance**: View real-time attendance in the dashboard
6. **Export Data**: Use "Export Excel" button on attendance page

### For Students/Users

1. **Scan QR**: Go to the main site and click "Scan QR Code"
2. **Point Camera**: Use your device camera to scan the student's QR code
3. **Manual Entry**: If camera doesn't work, manually enter the QR token

## How Attendance Works

1. Student scans their unique QR code using the scanning page
2. The system verifies the QR token against the database
3. If valid, attendance is recorded with:
   - Student ID
   - QR Token
   - Timestamp
   - Status (IN/OUT - toggles based on last record)
   - IP Address

## Customization

### Change Site Name
Edit `config/config.php`:
```php
define('SITE_NAME', 'Your School Name');
```

### Change Base URL
Edit `config/config.php`:
```php
define('BASE_URL', 'http://localhost/QRCODEX/');
```

## Security Notes

- Change default admin password regularly
- Use HTTPS in production
- Restrict access to admin pages via .htaccess
- Backup database regularly

## Troubleshooting

### Database Connection Error
- Check MySQL service is running
- Verify credentials in `config/config.php`
- Ensure database exists

### QR Scanner Not Working
- Allow camera permissions in browser
- Use HTTPS in production (required for camera access)
- Try Chrome or Edge browser

### QR Code Not Generating
- Check internet connection (uses external API)
- Verify PHP has curl enabled

## License

This project is open source and available for educational purposes.

## Support

For issues or questions, please check the code and ensure:
1. Database is properly set up
2. All files are in correct locations
3. PHP extensions are enabled (pdo_mysql)