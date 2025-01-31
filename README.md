# 🗓️ Online Appointment System

## 📝 About the Project
This project is a modern, user-friendly online appointment system designed for businesses. Customers can easily book appointments, administrators can manage them, and the system sends automatic email notifications.

## 🚀 Features
- 📅 Online appointment booking
- ⏰ Automatic availability check
- 📧 Automatic email notifications (confirmation and cancellation)
- 👨‍💼 Admin panel
- 🔒 Secure login system
- 📱 Mobile-friendly design
- 🚫 Automatic blocking for Sundays
- ⌛ Automatic blocking for past dates
- 🔍 Appointment search and filtering
- 📊 Detailed logging system

## ⚙️ Technologies
- PHP 7.4+
- MySQL
- HTML5
- CSS3
- JavaScript
- PDO Database Connection
- AJAX

## 💻 Installation
1. Clone the repository
```bash
git clone https://github.com/tolgaderman/online-appointment.git
```

2. Veritabanını oluşturun
- Create a MySQL database
Import the `database.sql` file

3. Configuration
- Update database connection details in the `db.php` file
```php
$host = "localhost";
$dbname = "database_name";
$username = "your_username";
$password = "your_password";
```

4. E-mail Settings
- Update email settings in the send-mail.php file
```php
$admin_email = "your-email@domain.com";
```

## 📁 File Structure
```
├── admin-login.html # Admin login page
├── admin-login.php # Admin login functionality
├── admin-panel.php # Admin control panel
├── admin-style.css # Admin panel styles
├── style.css # Main site styles
├── script.js # JavaScript functions
├── db.php # Database connection
├── send-mail.php # Email functions
├── access_log.php # Log system
├── block-time.php # Time slot blocking
├── blocked_ips.log # Blocked IP addresses
├── cancel-appointment.php # Appointment cancellation functionality
├── check-available-times.php # Check availability functionality
├── delete-appointment.php # Appointment deletion functionality
├── get-available-times.php # Fetch available time slots
├── index.html # Homepage
├── logo.png # Site logo
├── save-appointment.php # Save appointment functionality
├── update-status.php # Update appointment status
└── view-logs.php # View logs page
```

## 🔧  Usage
1. Booking an Appointment
- Select a date from the homepage
- Choose an available time slot
- Fill in personal details
- A confirmation email will be sent automatically

2. Admin Panel
- Log in through `/admin-login.html` (username:admin , password= admin)
- View and manage appointments
- Block time slots
- Monitor logs

## 🔒 Security Features
- CSRF protection
- SQL Injection protection
- Brute Force protection
- IP blocking system
- Secure session management

## 📱 Mobile Compatibility
- Responsive design
- Mobile-optimized date picker
- Touchscreen optimization

## 🤝 Contributing
1. Fork the project
2. Create a new branch (git checkout -b feature/newFeature)
3. Commit your changes (git commit -m 'Added new feature')
4. Push to the branch (git push origin feature/newFeature)
5. Create a Pull Request

## 🙏 Acknowledgments
- Font Awesome for icons
- PDO for secure database connections
```
