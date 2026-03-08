# LeadFlow CRM

**LeadFlow CRM** is a lightweight, feature-rich Customer Relationship Management system built for small businesses to manage leads, clients, projects, and invoices. It provides an intuitive dashboard, lead tracking with status updates, follow-up notes, and comprehensive reporting – all secured with role‑based access.

## Features

- **Lead Management**  
  - Add, view, edit, and delete leads  
  - Track lead status: `new` → `contacted` → `converted`  
  - Add follow‑up notes for each lead  
  - Source tracking and estimated value

- **Client & Project Management**  
  - Convert leads into clients  
  - Manage client details and associated projects  
  - Track project status, deadlines, and budgets

- **Invoicing**  
  - Create and manage invoices linked to clients or projects  
  - Monitor payment status: `draft`, `sent`, `paid`, `overdue`  
  - View revenue and outstanding amounts

- **Email Templates**  
  - Create reusable email templates for campaigns or follow‑ups  
  - Placeholder for future email integration

- **Reporting & Analytics**  
  - Dashboard with real‑time stats (total leads, conversion rate, pipeline value)  
  - Lead trends (last 7 days) and source breakdown  
  - Conversion funnel and source performance  
  - Date‑filterable reports page with charts

- **User Management**  
  - Secure authentication with password hashing  
  - Role‑based access: `admin` (full access) and `staff` (limited views)  
  - “Remember me” functionality  
  - Activity logging for audit trails

- **Security**  
  - CSRF protection on all forms  
  - XSS prevention via output escaping  
  - Session fixation protection  
  - Prepared statements for database queries

## Tech Stack

- **Backend**: PHP 8.2 (with PDO)  
- **Database**: MySQL / MariaDB  
- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript  
- **Libraries**: Chart.js (charts), DataTables (sorting/searching), Bootstrap Icons  
- **Server**: Apache / Nginx (LAMP/LEMP stack)

## Installation

### Requirements

- PHP 8.0 or higher  
- MySQL 5.7 or higher  
- Web server (Apache / Nginx)  
- Composer (optional, for dependency management – not strictly required)

### Steps

1. **Clone the repository**  
   ```bash
   git clone https://github.com/yourusername/leadflow-crm.git
   cd leadflow-crm
   ```

2. **Set up the database**  
   - Create a MySQL database (e.g., `leadflow`).  
   - Import the provided SQL dump:  
     ```bash
     mysql -u yourusername -p leadflow < leadflow.sql
     ```  
     (The file `leadflow (3).sql` is included; rename it to `leadflow.sql` if needed.)

3. **Configure the application**  
   - Copy `config/config.example.php` to `config/config.php` (if not present, create from the sample below).  
   - Edit `config/config.php` with your database credentials and application settings.  
   - Ensure the `BASE_URL` constant points to your project’s root URL.

   **Sample `config/config.php`:**
   ```php
   <?php
   // Database configuration
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'leadflow');
   define('DB_USER', 'root');
   define('DB_PASS', '');

   // Application settings
   define('APP_NAME', 'LeadFlow CRM');
   define('BASE_URL', 'http://localhost/leadflow-crm');
   define('CURRENCY', 'ZAR'); // or 'SZL'

   // Establish PDO connection (used throughout the app)
   try {
       $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
       $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   } catch (PDOException $e) {
       die("Database connection failed: " . $e->getMessage());
   }
   ?>
   ```

4. **Set file permissions**  
   - Ensure the `logs/` directory is writable by the web server (if you use file logging).  
   - By default, logging is done to the database; you can enable file logging in `functions.php`.

5. **Run the application**  
   - Place the project folder under your web server’s document root.  
   - Open `http://localhost/leadflow-crm` in your browser.  
   - Log in using the default admin credentials:  
     - **Username**: `admin`  
     - **Password**: `password` (this is a placeholder; change it after first login)

   > **Note**: The default password (`password`) is the hashed version of "password". For security, you should change it immediately via the database or create a new admin user.

## Usage

- **Login**: Access the system via `login.php`.  
- **Dashboard**: View key metrics, recent leads, activity logs, project and invoice summaries.  
- **Leads**: Navigate to “Leads” to see all leads, update status inline, add new leads, or delete them.  
- **Clients**: View and manage clients (converted leads).  
- **Projects**: Create and track projects linked to clients.  
- **Invoices**: Manage invoices, update payment status.  
- **Reports**: Filter data by date range and view lead trends, source performance, and conversion funnel.  
- **Settings**: Manage email templates (more settings can be added).

## Project Structure

```
leadflow-crm/
├── admin/                  # Admin area (dashboards, views)
│   ├── dashboard.php
│   ├── users.php
│   └── views/              # Detailed views (leads, clients, projects, invoices, reports, settings)
├── api/                     # AJAX endpoints (e.g., dashboard_stats.php)
├── config/                  # Configuration files
│   └── config.php
├── includes/                # Core includes
│   ├── header.php
│   ├── footer.php
│   └── functions.php        # Helper functions
├── logs/                    # File logs (optional)
├── index.php                # Landing page
├── login.php                # User login
├── register.php             # User registration
├── logout.php               # Logout
└── leadflow.sql             # Database dump
```

## Customisation

- **Styling**: Modify the CSS in `style.css` or the inline styles in each file.  
- **Language**: Translation stubs are available in `functions.php` (__() function). Add more translations as needed.  
- **Email Templates**: Use the email templates section to create templates; integrate with an email library (PHPMailer) for actual sending.

## Contributing

Contributions are welcome! Please fork the repository and submit a pull request for any improvements or bug fixes.

## License

This project is open-source and available under the [MIT License](LICENSE).

---

**Built with ❤️ for small businesses to streamline lead management.**