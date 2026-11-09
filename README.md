# Chrono Sales System

## How to Set Up

1. **Start XAMPP Services**
   - Open XAMPP and start both **Apache** and **MySQL**.

2. **Create the Database**
   - Open phpMyAdmin (usually at http://localhost/phpmyadmin).
   - Create a new database named `chrono_sales_db`.
   - Import the `chrono_sales_db.sql` file from this folder into the database.

3. **Set Up Python Environment**
   - Open a terminal in the project folder.
   - For Windows:
     - Install virtual environment: `python -m venv .venv`
     - Activate: `.venv\Scripts\activate`
   - For macOS/Linux:
     - Install virtual environment: `python3 -m venv .venv`
     - Activate: `source .venv/bin/activate`
   - Install requirements: `pip install -r requirements.txt`

4. **Run the Backend**
   - Start the Python backend: `python app.py`

5. **Access the Application**
   - Open your browser and go to `http://localhost/index.php` (or the correct path if not in web root).
   - Log in with:
     - **Username:** admin@chrono.sales.com
     - **Password:** admin123

---


## System Overview & Page Functionality

### Main Files & Folders
- **app.py**: Python backend server (API, ML/SHAP logic, analytics endpoints)
- **backend/**: PHP backend scripts for database and authentication
- **public/**: Main PHP pages for the web interface
- **assets/**: CSS, JS, and images
- **chrono_sales_db.sql**: Database schema and sample data

---

## Page Features & Core Modules

### Dashboard (`dashboard.php`)
- Real-time sales summary: revenue today, this week, this month
- 30-day sales trend sparkline
- Top-performing branches (by revenue)
- Payment method breakdown (pie/donut chart)
- Transaction trend (weekly)
- **SHAP-powered Forecast Alert:**
   - Uses machine learning (GradientBoostingRegressor + SHAP) to predict tomorrow’s revenue
   - Explains which factors (recent sales, day of week, etc.) drive the forecast up or down
   - Shows alert if a surge or dip is likely
   - Visualizes SHAP feature importance (why the forecast changed)

### Sales Analytics (`sales-analytics.php`)
- Deep-dive analytics with flexible filters:
   - Date range (daily, weekly, monthly, custom)
   - Branch, payment method, discount type, transaction status
- Revenue by branch (bar chart)
- Daily revenue trend (line chart)
- Sales heatmap (day-of-week × hour)
- Discount analysis (by type)
- Top 10 customers (table)
- Payment method breakdown
- Quick insights (top branch, customer, payment)
- Export analytics to CSV or PDF

### Data Management (`data-management.php`)
- Admin interface for managing all sales data
- Tabs for Transactions, Customers, Branches
- Add, edit, delete, and bulk import/export records (CSV)
- Search, filter, and sort records
- Bulk actions (delete, import)
- Data validation and error reporting
- Audit log and integrity checks

### Other Core Pages
- **Forecast**: Advanced time-series forecasting (future revenue, model selection, what-if analysis)
- **Branch Performance**: Compare branches, growth, and risk
- **Payment Insights**: Analyze payment trends and breakdowns
- **Customer Insights**: Track top/repeat customers, spending patterns
- **Reports**: Generate and export formal business reports (monthly, quarterly, annual)
- **Settings**: Manage branches, users, forecasting preferences, and alert rules

### Backend (backend/)
- **db.php**: Database connection logic
- **login.php**: Handles user login authentication
- **logout.php**: Handles user logout
- **api_proxy.php**: Bridges frontend requests to backend APIs

### Assets
- **assets/css/**: Stylesheets for different pages
- **assets/js/**: JavaScript for page interactivity
- **assets/imgs/**: Images used in the UI

---

## Machine Learning & SHAP Core
- The dashboard uses a machine learning model (GradientBoostingRegressor) to forecast next-day revenue.
- **SHAP (SHapley Additive exPlanations)** explains which features (recent sales, day of week, etc.) most influenced the forecast.
- If ML libraries are not installed, the system falls back to a simple trend-based forecast.
- SHAP feature importance is visualized for transparency and actionable insights.

---

## Local Development Account

The local SQL dump may contain a demo administrator account. Do not rely on
demo credentials for a public deployment; set a unique administrator password
before opening the site to the internet.

---

## Notes
- Make sure Apache and MySQL are running before accessing the site.
- The Python backend must be running for analytics and ML features to work.
- `create_dummy.php` is for local development only and must not be exposed on a
  public site.

---

## Deploying on Railway with GitHub

This project runs as two Railway services (PHP/Apache and Flask) and a Railway
MySQL database. Vercel cannot run the PHP application directly. Do not deploy
`create_dummy.php` as a public script; the included Apache configuration blocks
it, as well as direct web access to Python, SQL, spreadsheet, and text files.

### 1. Prepare and push the source to GitHub

Create a **private** GitHub repository, then from PowerShell in this project:

```powershell
git init
git add .
git status
```

Check that `Sales Report.xlsx`, `chrono_sales_db.sql`, `.env` files, and any
real customer or sales data are not staged. The repository ignores spreadsheets,
SQL dumps, and environment files by default. If the staged files are safe:

```powershell
git commit -m "Prepare Chrono Sales for Railway"
git branch -M main
git remote add origin https://github.com/YOUR-USERNAME/YOUR-REPOSITORY.git
git push -u origin main
```

Replace the URL with your private repository URL.

### 2. Create the Railway services

1. In Railway, create a project from the GitHub repository.
2. Add a MySQL database service to the project.
3. Add a PHP web service using the same GitHub repository. In its **Settings**,
   set the Dockerfile path to `Dockerfile.php`.
4. Add a second service using the same repository and set its Dockerfile path to
   `Dockerfile.python`.
5. Keep the Flask service private (do not generate a public domain). Generate a
   public domain only for the PHP web service.

### 3. Set service variables

Add these variables to **both** application services, using Railway's variable
reference picker to reference the MySQL service's values:

| Variable | MySQL service value |
| --- | --- |
| `DB_HOST` | `MYSQLHOST` |
| `DB_PORT` | `MYSQLPORT` |
| `DB_NAME` | `MYSQLDATABASE` |
| `DB_USER` | `MYSQLUSER` |
| `DB_PASSWORD` | `MYSQLPASSWORD` |

On the PHP service, set `FLASK_BASE` to the Flask service's private hostname and
port, in this format: `http://<flask-private-host>:<flask-port>`. Use the
Railway-provided private domain and port references; do not use localhost or a
public API URL.

### 4. Import the database

Import `chrono_sales_db.sql` from your local copy into the Railway MySQL
database using a MySQL client. The dump is intentionally excluded from GitHub
and the Docker image because it contains application data and a seeded admin
account. Before making the site public, replace the seeded admin password with
a unique, strong password and remove any data you do not intend to publish.

Generate a password hash in a trusted PHP environment with
`php -r "echo password_hash('YOUR-UNIQUE-PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"`,
then update the `users.password_hash` value for the administrator in the
Railway database. Keep the chosen password and generated hash out of GitHub.

### 5. Deploy and verify

Deploy both application services. Open the public PHP domain and verify login,
the dashboard, analytics, data management, and exports. If a database-backed
page fails, check the service variables and Railway logs for both the PHP and
Flask services. To publish later changes, push them to the connected GitHub
branch; Railway will redeploy the services.

Never commit production passwords or real customer data to GitHub. The local
login documented above is for development only.
