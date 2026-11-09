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

## Deploying on Render with GitHub

The application needs a PHP/Apache web service, a Flask API service, and a
MySQL database. Render can host the two Docker-based application services, but
does not provide managed MySQL. Use a separate managed MySQL provider. The
included PHP Apache configuration blocks direct access to Python, SQL,
spreadsheet, and text files, as well as `create_dummy.php`.

The deployment code is already in the GitHub repository. For later changes,
push to the connected `main` branch; Render can automatically redeploy from it.
Before sharing the repository, verify that it is private and does not contain
real sales/customer data or credentials. `.gitignore` excludes SQL dumps,
spreadsheets, and environment files.

### 1. Create the PHP web service

1. In Render, choose **New → Web Service** and connect the GitHub repository.
2. Select **Docker** as the runtime.
3. Set the Dockerfile path to `Dockerfile.php` and the Docker build context to
   the repository root (`.`).
4. Choose a region and plan, then create the service. This container listens on
   port `10000`.

### 2. Create the Flask API service

1. Create a second **Web Service** from the same GitHub repository, select
   Docker, and set the Dockerfile
   path to `Dockerfile.python` with build context `.`.
2. Use the same region as the PHP service so the services can communicate over
   Render's network.
3. Generate a long random secret in Render, then set the same
   `FLASK_API_SECRET` value on both the PHP and Flask services. The PHP proxy
   sends it as a server-to-server header; the Flask API rejects requests
   without the matching secret. Never put this value in frontend code or Git.
4. Copy the Flask service's public `https://...onrender.com` URL and set the
   PHP service's `FLASK_BASE` to that URL. This public-web-service option can
   use Render's free web-service plan, but free services may spin down when idle.
   Keep the shared secret set on both services even though Flask has a public URL.

### 3. Configure MySQL

Create a managed MySQL-compatible database with a provider that allows
connections from your Render services. Restrict its network access as much as
your provider allows; do not expose the database to the entire internet.
Create a dedicated application database (for example, `chrono_sales_db`); do
not use a system schema such as `sys`. Configure the following environment
variables on **both** Render application services:

| Variable | MySQL service value |
| --- | --- |
| `DB_HOST` | Database hostname from your provider |
| `DB_PORT` | Database port from your provider |
| `DB_NAME` | Database name |
| `DB_USER` | Database username |
| `DB_PASSWORD` | Database password |
| `DB_SSL_CA` | Path to the provider's CA certificate file, when TLS verification is required |
| `FLASK_API_SECRET` | Long random shared secret; use the exact same value on both app services |

If the database provider requires a CA certificate, add it as a Render **Secret
File** on both services and set `DB_SSL_CA` to the mounted file path. For
example, if the secret file is mounted at `/etc/secrets/ca.pem`, use that path
as the variable value. This enables CA and server-identity verification for
both the PHP and Python database connections.

Set `FLASK_BASE` on the PHP service to the Flask service's public HTTPS URL.
Use Render's environment-variable dashboard for credentials. Do not put
passwords, shared secrets, connection URLs, or certificate contents in GitHub
or this README.

### 4. Import and secure the database

Import `chrono_sales_db.sql` from your local copy into the dedicated application
database using a MySQL client. The dump is intentionally excluded from GitHub
and the Docker image because it contains application data and a seeded admin
account. Remove any data you do not intend to publish.

Generate a password hash in a trusted PHP environment with
`php -r "echo password_hash('YOUR-UNIQUE-PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"`,
then update the `users.password_hash` value for the administrator in the
database. Keep the chosen password and generated hash out of GitHub.

### 5. Deploy and verify

Deploy both application services and generate a public domain for the PHP
service only. Verify login, the dashboard, analytics, data management, and
exports. If a database-backed page fails, check the environment variables and
Render logs for both application services. For future updates, push to the
connected GitHub branch; Render will redeploy the services.

Never commit production passwords or real customer data to GitHub. The local
login documented above is for development only.
