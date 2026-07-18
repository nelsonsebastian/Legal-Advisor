# Legal Advisor

A full-stack **Legal Advisory and Appointment Management System** designed to connect clients with legal professionals through a secure, role-based web application. The platform streamlines the process of lawyer discovery, appointment scheduling, case management, and administrative oversight, providing an efficient digital solution for legal consultation services.

Developed using **PHP, MySQL, HTML5, CSS3, JavaScript, AJAX, and Bootstrap**, the application follows modular software engineering principles to deliver a secure, maintainable, and scalable web-based system.

---

## Overview

Legal Advisor is a role-based web application that enables clients to search for lawyers based on their legal specialization, schedule appointments, and manage consultations online. Lawyers can manage appointments, review client cases, and maintain their professional profiles, while administrators oversee platform activities, user management, and appointment records.

The project demonstrates practical implementation of full-stack web development concepts, including authentication, role-based authorization, CRUD operations, database management, asynchronous communication using AJAX, and Software Development Life Cycle (SDLC) practices.

---

## Features

### Client

- Secure registration and authentication
- Browse lawyers by legal specialization
- View lawyer profiles
- Book appointments
- View appointment history
- Track appointment status
- Manage personal profile

### Lawyer

- Secure lawyer registration and login
- Manage professional profile
- View appointment requests
- Approve or reject appointments
- Manage client cases
- View consultation history
- Update availability

### Administrator

- Secure administrator dashboard
- Manage lawyers
- Manage clients
- Monitor appointments
- Manage active cases
- Approve lawyer registrations
- View system statistics
- Maintain platform records

---

## Technology Stack

| Category | Technologies |
|----------|--------------|
| Frontend | HTML5, CSS3, JavaScript, AJAX |
| Backend | PHP |
| Database | MySQL |
| Development Tools | Visual Studio Code, XAMPP, Git, GitHub |

---

## System Workflow

```text
                     +----------------------+
                     |       Client         |
                     +----------------------+
                               │
                    Search & Book Appointment
                               │
                               ▼
               +----------------------------------+
               |      Legal Advisor Platform      |
               +----------------------------------+
                 │                          │
                 ▼                          ▼
          Lawyer Management        Administrator Module
                 │                          │
                 ▼                          ▼
       Appointment & Case Handling   User & System Management
                 │                          │
                 └──────────────┬───────────┘
                                ▼
                         MySQL Database
```

---

## Core Functionalities

### Authentication & Authorization

- Role-based login system
- Secure registration
- Session management
- Access control

### Appointment Management

- Schedule legal consultations
- Approve or reject appointments
- Appointment tracking
- Case assignment

### Lawyer Management

- Lawyer registration
- Profile management
- Legal specialization management
- Availability management

### Client Management

- Client registration
- Appointment history
- Profile management
- Consultation records

### Administrator Module

- Dashboard overview
- User management
- Lawyer approval
- Appointment monitoring
- Case administration

---

## Project Structure

```text
Legal-Advisor/
│
├── admin/
├── ajax/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│
├── config/
│   └── database.php
│
├── includes/
│   └── session.php
│
├── client/
├── lawyer/
│
├── login.php
├── register.php
├── logout.php
├── index.php
│
├── database/
└── README.md
```

---

## Installation

### Prerequisites

- PHP 8.x
- MySQL
- Apache Server
- XAMPP (Recommended)
- Git

---

### Clone the Repository

```bash
git clone https://github.com/nelsonsebastian/Legal-Advisor.git
```

---

### Configure the Project

Move the project into your XAMPP **htdocs** directory.

```text
C:\xampp\htdocs\Legal-Advisor
```

---

### Database Setup

1. Start **Apache** and **MySQL** using XAMPP.

2. Open **phpMyAdmin**.

3. Create a new database.

```text
legal_advisor
```

4. Import the provided SQL file.

5. Configure the database connection in:

```text
config/database.php
```

Example:

```php
$host = "localhost";
$username = "root";
$password = "";
$database = "legal_advisor";
```

---

### Run the Application

Open your browser and navigate to:

```text
http://localhost/Legal-Advisor
```

---

## Software Engineering Practices

This project follows modern software engineering principles, including:

- Modular Application Architecture
- Object-Oriented Programming (OOP) Concepts
- Software Development Life Cycle (SDLC)
- Role-Based Access Control (RBAC)
- CRUD Operations
- Database Normalization
- Session Management
- AJAX-Based Asynchronous Communication
- Authentication and Authorization
- Code Reusability
- Error Handling
- Debugging and Testing
- Maintainable Project Structure

---

## Learning Outcomes

This project provided practical experience in:

- Full-Stack Web Development
- PHP Application Development
- MySQL Database Design
- Role-Based Authentication
- Appointment Management Systems
- Client–Server Architecture
- CRUD Application Development
- AJAX Integration
- Session Handling
- Database Connectivity
- Software Testing
- Debugging
- Version Control using Git and GitHub

---

## Future Enhancements

- Email notifications
- SMS/OTP verification
- Video consultation integration
- Online payment gateway
- Real-time chat system
- REST API development
- AI-powered legal assistance
- Document upload and management
- Cloud deployment
- Docker containerization
- Multi-language support

---

## Contributing

Contributions are welcome.

1. Fork the repository.

2. Create a feature branch.

```bash
git checkout -b feature/your-feature
```

3. Commit your changes.

```bash
git commit -m "Add new feature"
```

4. Push the branch.

```bash
git push origin feature/your-feature
```

5. Open a Pull Request.

---

## Author

**Nelson Sebastian**

Software Developer

- **GitHub:** https://github.com/nelsonsebastian
- **Repository:** https://github.com/nelsonsebastian/Legal-Advisor
- **LinkedIn:** https://linkedin.com/in/nelson-sebastian
- **Email:** nelsonsebastian2002@gmail.com

---

## License

© 2026 Nelson Sebastian. All rights reserved.

This repository is shared for portfolio and educational purposes. No part of this project may be copied, modified, distributed, or used commercially without prior written permission from the author.
