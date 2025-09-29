# Group Allocation System

## Prerequisites

This Laravel/Inertia.js platform requires Composer, PHP, Node.js, and Python. Please ensure you have the following installed:

- Composer
- PHP
- Node.js and npm
- Python

You can verify you have the required installations by running:

- 'composer --version'
- 'php -v'
- 'node -v'
- 'npm -v'
- 'python --version'

**Tip:** Please use PHP 8.2 to ensure compatibility. *(My install versions are: composer 2.5.1, php 8.2.0, node 20.9.0, npm 10.1.0, python 3.11.9)*

## Project Setup
1. Clone this repository 
2. Open the folder in VSCode and open a terminal in project root

**Install Composer dependencies:**
```
composer install
```
**Install Node.js dependencies:**
```
npm install
```
**Ensure you have the following python packages:**
- ortools
- numpy
- pandas
- matplotlib
- json
- ast
- sys

**Or install using 'requirements.txt':**
```
pip install -r requirements.txt
```
**Setup .env file:**

Create a copy of .env using the command below
```
cp .env.example .env
```
Then open .env and update the following Database and SMTP fields with your own configuration:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
```
**Generate application key:**
```
php artisan key:generate
```

**Run database migrations and database seeder:**
```
php artisan migrate:fresh --seed
```
**Run development localhost server:** (both required)
```
php artisan serve
```
```
npm run dev
```

## Preloaded Users:

- Admin
    - Email: admin
    - Password: password
- Student
    - Email: student
    - Password: password
- Supervisor
    - Email: supervisor
    - Password: password

### Use scheduler (for deadline):
*Required for automated allocation and status update after deadline is passed*
```
php artisan schedule:work
```
### Use queues (for allocation emails):
*Required for queued emails to be dispatched automatically*
```
php artisan queue:work
```

## Testing:

### Run Unit and Feature tests:

Create a copy of your **'.env file'**, and name it **'.env.testing'**. Update the following fields:
- DB_DATABASE=your_test_database
- QUEUE_CONNECTION=sync

Also update the following value in **'phpunit.xml'**:
- env name="DB_DATABASE" value="your_test_database"

**Migrate test environment database:**
```
php artisan migrate --env=testing
```
**Run all tests:**
```
php artisan test
```
**Or run tests indivdually:**
```
php artisan test --filter [TestName]
```

### Algorithm simulations:

**View algorithm simulations:**
- Run 'app\Scripts\student_assignment_test_main.py'.
- Test parameters can be changed (after line 60). More information provided in comments. (*Test data will then be automatically generated each time the script is run*)

### General Testing:

- Sample CSVs are provided at **'public\Users.csv'** and **'public\Topics.csv'**, which can be used to import multiple users and topics at once.
- Accessing the route **'/generate-preferences'**, from an admin user, will automatically generate preferences and availability for all students and supervisors in the database, respectively.
- Testing a single allocation dispatch email:
    - Open the **'test_send_allocation'** method located in **'app\Http\Controllers\TestController.php'**.<br> Update the **'$testEmail'** variable to your preferred email address.
    - Create a group (or multiple for a supervisor, if desired), that includes the user registered to the preferred email address.<br>*Add as many other users as desired. This will only affect the content of the dispatched email.*
    - Access the route **'/test-send-allocation'**. Upon receiving a successful feedback message, check the inbox of your preferred email address to view the allocation dispatch email.

## Disclaimer:

- All allocation dispatch emails by default are sent to **'yourname@example.com'**, which is set in the **'send_allocation'** method in **'app\Http\Controllers\GroupController.php'**.
- Change this to a personal email to view outputs, or use the commented line for full functionality (send to registered emails in the system).

## Author
Darren Wu
