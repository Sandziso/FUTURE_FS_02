markdown
<div align="center">
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap">
  <img src="https://img.shields.io/badge/Chart.js-F5788D?style=for-the-badge&logo=chart.js&logoColor=white" alt="Chart.js">
  <img src="https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge" alt="License">
  <img src="https://img.shields.io/badge/Status-Production-2ea44f?style=for-the-badge" alt="Status">
</div>

<br>

<div align="center">
  <img src="screenshots/leadflow-dashboard.png" alt="LeadFlow CRM Dashboard" width="800">
  <p><em>Smart lead management for growing businesses</em></p>
</div>

<h1 align="center">📊 LeadFlow CRM</h1>

<p align="center">
  <strong>A lightweight, feature-rich Customer Relationship Management system for small businesses to manage leads, clients, projects, and invoices.</strong>
  <br>
  <a href="https://leadflowcrm.freedev.app">🌐 Live Demo</a> ·
  <a href="#-features">📋 Features</a> ·
  <a href="#-installation">⚙️ Installation</a> ·
  <a href="#-tech-stack">🛠️ Tech Stack</a>
</p>

---

## 📌 Overview

**LeadFlow CRM** is a complete lead management solution built for the Future Interns internship program. It demonstrates professional CRM functionality with a clean, intuitive interface. Small businesses can track leads from first contact to conversion, manage clients and projects, generate invoices, and gain insights through analytics.

> 📈 From lead to deal — track every step of your sales pipeline.

---

## ✨ Features

### 📋 Lead Management

| Feature | Description |
|---------|-------------|
| ➕ **Add Leads** | Name, email, phone, source, estimated value |
| 📊 **Lead Dashboard** | Overview with real-time stats |
| 🔄 **Status Tracking** | New → Contacted → Converted |
| 📝 **Follow-up Notes** | Add notes with timestamps |
| 🔍 **Search & Filter** | Find leads quickly |
| 📈 **Lead Score** | Automatic scoring based on engagement |

### 👥 Client & Project Management

| Feature | Description |
|---------|-------------|
| 🔄 **Convert Leads** | Convert leads to clients |
| 📋 **Client Profiles** | Company name, contact person, address |
| 📊 **Project Management** | Track project status, deadlines, budgets |
| 📅 **Project Calendar** | View project timelines |

### 💰 Invoicing

| Feature | Description |
|---------|-------------|
| 📄 **Create Invoices** | Link to clients or projects |
| 📊 **Payment Status** | Draft, sent, paid, overdue |
| 💰 **Revenue Tracking** | Monitor income and outstanding amounts |

### 📈 Analytics & Reports

| Feature | Description |
|---------|-------------|
| 📊 **Dashboard Charts** | Lead trends, source breakdown |
| 🔄 **Conversion Funnel** | Track lead progression |
| 📅 **Date Filtering** | Reports by date range |
| 📤 **Export** | CSV export of data |

### 🔒 User Management

| Feature | Description |
|---------|-------------|
| 🔐 **Secure Authentication** | Password hashing (bcrypt) |
| 👥 **Role-Based Access** | Admin (full), Staff (limited) |
| 🔄 **Remember Me** | Secure token-based login |
| 📋 **Activity Logging** | Audit trail of user actions |

---

## 📸 Screenshots

<div align="center">
  <table>
    <tr>
      <td><img src="screenshots/leadflow-leads.png" alt="Leads" width="400"></td>
      <td><img src="screenshots/leadflow-analytics.png" alt="Analytics" width="400"></td>
    </tr>
    <tr>
      <td><img src="screenshots/leadflow-invoices.png" alt="Invoices" width="400"></td>
      <td><img src="screenshots/leadflow-settings.png" alt="Settings" width="400"></td>
    </tr>
  </table>
</div>

---

## 🛠️ Tech Stack

| Component | Technology |
|-----------|------------|
| **Backend** | PHP 8.2+ (PDO MySQL) |
| **Database** | MySQL 5.7+ / MariaDB 10.4+ |
| **Frontend** | HTML5, CSS3, JavaScript (ES6+) |
| **CSS Framework** | Bootstrap 5.3 |
| **Charts** | Chart.js |
| **DataTables** | jQuery DataTables |
| **Icons** | Bootstrap Icons |
| **Security** | Prepared statements, CSRF tokens, password_hash() |
| **Deployment** | InfinityFree / Hostinger |

---

## 📁 Project Structure
leadflow-crm/
├── admin/ # Admin area
│ └── views/ # Detailed views
├── api/ # AJAX endpoints
├── config/ # Configuration
├── includes/ # Core includes
│ ├── header.php
│ ├── footer.php
│ └── functions.php
├── logs/ # File logs
├── models/ # Data models
├── controllers/ # Controllers
├── assets/ # CSS, JS, images
├── index.php # Landing page
├── login.php # User login
├── register.php # User registration
├── logout.php # Logout
└── leadflow.sql # Database dump

text

---

## 🚀 Installation

### Prerequisites

- Web server with PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.4+

### Step 1: Clone the Repository

```bash
git clone https://github.com/Sandziso/FUTURE_FS_02.git
cd FUTURE_FS_02
Step 2: Database Setup
bash
mysql -u root -p leadflow < leadflow.sql
Step 3: Configuration
Create config/config.php:

php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'leadflow');
define('DB_USER', 'root');
define('DB_PASS', '');
define('APP_NAME', 'LeadFlow CRM');
define('BASE_URL', 'https://leadflowcrm.freedev.app');
define('CURRENCY', 'ZAR');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
Step 4: Default Admin Account
Role	Username	Password
Admin	admin	password
⚠️ Important: Change this password immediately!

🔒 Security
Feature	Implementation
SQL Injection	PDO prepared statements
XSS Prevention	htmlspecialchars()
CSRF Protection	Tokens on all forms
Password Security	password_hash() (bcrypt)
Session Security	HTTP-only cookies, regeneration
Login Protection	5 attempts → 15-minute lockout
📄 License
This project is open-source software licensed under the MIT License.

📬 Contact
Sandziso Mamba
📧 mlungisimamba01@gmail.com
🔗 GitHub · LinkedIn

<div align="center"> <p> <a href="https://leadflowcrm.freedev.app">🌐 Live Demo</a> · <a href="https://github.com/Sandziso/FUTURE_FS_02/issues">🐛 Report Bug</a> </p> </div> ```
