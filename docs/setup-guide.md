# KaffeePinoy Rentals — Local Installation Guide

This guide explains how to install and run KaffeePinoy Rentals on a Windows computer using XAMPP, PHP, Apache, and MySQL.

# 1. Requirements

Install the following:

XAMPP with Apache, PHP, and MySQL/MariaDB

A web browser

The KaffeePinoy Rentals source code downloaded from GitHub

The database backup file kaffeepinoy_dev.sql provided separately by the project owner

# 2. Install the Project

Install XAMPP.

Open the XAMPP Control Panel.

Start Apache and MySQL.

Download or clone the KaffeePinoy Rentals repository.

Place the project folder here:

C:\xampp\htdocs\Kaffepinoy-project

Confirm that the project contains the backend and frontend folders.

# 3. Create the Database

Open phpMyAdmin:

http://localhost/phpmyadmin

Click New.

Create a database named:

kaffeepinoy_dev

Select the newly created database.

# 4. Import the Database Backup

Obtain kaffeepinoy_dev.sql from the project owner.

In phpMyAdmin, select the kaffeepinoy_dev database.

Open the Import tab.

Click Choose File and select kaffeepinoy_dev.sql.

Keep the format set to SQL.

Click Import or Go.

Confirm that the import completes successfully and that the required tables appear.

Important: The backup should contain the database structure and data needed by the current version of the application. Do not run the same database migrations again if they are already included in the imported database.

If the import reports duplicate tables, existing columns, or other conflicts, stop and check the database before retrying. Do not import over a database containing data you need to preserve.

# 5. Create the Application's MySQL User

The database backup does not automatically create the MySQL account used by the application. Create that account on your own computer.

Open phpMyAdmin.

Select the SQL tab.

Run the following commands, replacing the password placeholder with the agreed local database password:

CREATE USER 'kpr_sys'@'localhost'
IDENTIFIED BY 'REPLACE_WITH_YOUR_DATABASE_PASSWORD';

GRANT SELECT, INSERT, UPDATE, DELETE
ON kaffeepinoy_dev.\*
TO 'kpr_sys'@'localhost';

Keep the username and password available for the next step.

These permissions are intended for normal application data operations. If the application later needs additional privileges for a documented feature, review that requirement separately instead of granting unrestricted database access.

If the MySQL account already exists, do not blindly run CREATE USER again. Check the existing account and its permissions first.

# 6. Configure Apache Environment Variables

The application's backend/config/database.php reads these variables using PHP's getenv() function:

KPR_DB_HOST

KPR_DB_NAME

KPR_DB_USER

KPR_DB_PASSWORD

Open this Apache configuration file:

C:\xampp\apache\conf\httpd.conf

Add the following configuration using the actual local database credentials:

SetEnv KPR_DB_HOST "localhost"
SetEnv KPR_DB_NAME "kaffeepinoy_dev"
SetEnv KPR_DB_USER "kpr_sys"
SetEnv KPR_DB_PASSWORD "REPLACE_WITH_YOUR_DATABASE_PASSWORD"

Save the file and restart Apache through the XAMPP Control Panel.

Important: The password above is a placeholder. Replace it with the password configured for your own MySQL account. Do not publish real credentials in GitHub.

Apache's SetEnv configuration must be visible to the PHP process running the application. If PHP reports that the database configuration is unavailable, verify that Apache loaded the configuration and that getenv() returns the expected variables.

# 7. Open the Application

Open the following URL in your local browser:

http://localhost/Kaffepinoy-project/frontend/login.html

If the project redirects to a login page, sign in using a valid administrator account.

The MySQL database username and password are not the same as the application's administrator login credentials.

# NOTE

This is for local hosting, this setup doesn't ready for Deployment.
