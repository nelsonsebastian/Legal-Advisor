# Legal Advisor Website

A comprehensive PHP-based legal advisor platform that connects clients with lawyers, featuring appointment booking, case management, real-time chat, and administrative controls.

## Features

### For Clients
- User registration and authentication
- Browse and search lawyers by specialization
- Book appointments with calendar and time slot selection
- Track case status with reference numbers
- Real-time chat with assigned lawyers
- Rate and review lawyers after case completion

### For Lawyers
- Professional profile creation with specializations
- Appointment management and approval
- Case handling and status updates
- Real-time client communication
- Rating and review system

### For Administrators
- Lawyer approval system
- Appointment oversight and management
- Case monitoring and assignment
- User management and system analytics
- Complete administrative dashboard

## Technical Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Server**: Apache (XAMPP recommended)
- **Architecture**: MVC-inspired structure with separation of concerns

## Installation Instructions

### Prerequisites
- XAMPP (or similar LAMP/WAMP stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Setup Steps

1. **Download and Install XAMPP**
   - Download from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Install and start Apache and MySQL services

2. **Clone/Download Project**
   - Place all project files in your XAMPP `htdocs` directory
   - Example: `C:\xampp\htdocs\legal-advisor\`

3. **Database Setup**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `legal_advisor`
   - Import the SQL file: `database/legal_advisor.sql`
   - This will create all necessary tables and insert sample data

4. **Configuration**
   - Open `config/database.php`
   - Verify database connection settings:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', ''); // Default XAMPP password is empty
     define('DB_NAME', 'legal_advisor');
     ```

5. **Access the Website**
   - Open your browser and go to: `http://localhost/legal-advisor/`
   - The website should load with the homepage

## Default Login Credentials

### Administrator
- **Username**: admin
- **Password**: admin123

### Demo Usage
- Register as a Client or Lawyer to test full functionality
- Lawyer accounts require admin approval before login
- Clients can immediately book appointments after registration

## File Structure

```
legal-advisor/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   └── js/
│       └── main.js            # JavaScript functionality
├── config/
│   └── database.php           # Database configuration
├── includes/
│   └── session.php            # Session management
├── database/
│   └── legal_advisor.sql      # Database schema and data
├── ajax/                      # AJAX endpoints (to be created)
├── index.php                  # Homepage
├── login.php                  # User authentication
├── register.php               # User registration
├── dashboard.php              # User dashboard (to be created)
├── lawyers.php                # Lawyer directory (to be created)
├── book-appointment.php       # Appointment booking (to be created)
└── README.md                  # This file
```

## Key Features Explained

### User Authentication System
- Multi-role authentication (Client, Lawyer, Admin)
- Secure password hashing using PHP's `password_hash()`
- Session-based user management
- Role-based access control

### Appointment Booking System
- Interactive calendar for date selection
- Time slot management
- Approval workflow (Admin/Lawyer approval required)
- Automatic case creation upon approval

### Real-time Chat System
- JavaScript polling for real-time updates
- Case-based chat rooms
- Message history and read status
- Multi-user support (Client, Lawyer, Admin)

### Case Management
- Automatic case reference number generation
- Status tracking (New, Under Review, In Progress, Resolved, Closed)
- Case assignment to lawyers
- Progress monitoring

### Rating and Review System
- 5-star rating system for lawyers
- Written reviews and feedback
- Average rating calculation
- Review-based lawyer ranking

## Database Schema

### Main Tables
- `admin` - System administrators
- `clients` - Client users
- `lawyers` - Lawyer profiles with specializations
- `appointments` - Appointment bookings
- `cases` - Legal cases with reference numbers
- `chat_messages` - Real-time messaging
- `lawyer_ratings` - Rating and review system
- `legal_issue_types` - Predefined legal categories
- `time_slots` - Available appointment times

## Security Features

- SQL injection prevention using prepared statements
- XSS protection with input sanitization
- Password hashing and verification
- Session-based authentication
- Role-based access control
- Input validation and error handling

## Customization

### Adding New Legal Specializations
1. Access phpMyAdmin
2. Go to `legal_issue_types` table
3. Insert new specialization with description

### Modifying Time Slots
1. Edit `time_slots` table in database
2. Add/remove available appointment times
3. Set `is_active` to control availability

### Styling Customization
- Edit `assets/css/style.css` for visual changes
- Responsive design with mobile-first approach
- Professional color scheme with CSS custom properties

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify XAMPP MySQL is running
   - Check database credentials in `config/database.php`
   - Ensure database `legal_advisor` exists

2. **Login Issues**
   - Verify user exists in appropriate table
   - Check password hash in database
   - Ensure lawyer accounts are approved by admin

3. **Chat Not Working**
   - Check JavaScript console for errors
   - Verify AJAX endpoints are accessible
   - Ensure proper case ID is set

4. **Appointment Booking Issues**
   - Verify time slots exist in database
   - Check calendar JavaScript initialization
   - Ensure proper date format handling

## Future Enhancements

- Email notification system
- Document upload and management
- Payment integration
- Advanced search and filtering
- Mobile app development
- Video consultation integration
- Multi-language support

## Support

For technical support or questions:
1. Check the troubleshooting section
2. Verify all installation steps were completed
3. Check browser console for JavaScript errors
4. Ensure all database tables were created properly

## License

This project is created for educational and demonstration purposes. Feel free to modify and use according to your needs.

---

**Note**: This is a demonstration project. For production use, additional security measures, error handling, and testing should be implemented.