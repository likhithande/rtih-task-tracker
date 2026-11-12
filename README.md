# RTIH Task Tracker

A beginner-friendly, secure **Task Tracker Web Application** built for the **Ratan Tata Innovation Hub Amaravati Foundation (RTIH) IT Internship Technical Assignment**.

This project implements all requirements across **Level 1 (CRUD)**, **Level 2 (Search, Filter & Validation)**, and **Level 3 (Security & Authorization)** using core PHP 8, MySQL, PDO, and Bootstrap 5 without any external frameworks.

---

## Table of Contents
- [Project Description](#project-description)
- [Technologies](#technologies)
- [Project Structure](#project-structure)
- [Features](#features)
- [Database Setup](#database-setup)
- [Test Login](#test-login)
- [User Roles and Permissions](#user-roles-and-permissions)
- [Security](#security)
- [Backup and Restore](#backup-and-restore)
- [Testing](#testing)
- [Bugs Found and Fixed](#bugs-found-and-fixed)
- [AI Usage Note](#ai-usage-note)
- [Completion Note](#completion-note)

---

## Project Description

The **RTIH Task Tracker** is a clean web application designed to help users track and organize their daily work tasks. Each user can register an account, log in securely, and manage their personal to-do list with task titles, descriptions, and status updates (Pending, In Progress, Completed).

The system enforces strict user data isolation: **a user can only view, search, edit, or delete their own tasks**. All sensitive operations are guarded by PDO prepared statements, session-based authentication, Cross-Site Request Forgery (CSRF) protection, and Cross-Site Scripting (XSS) prevention.

---

## Technologies

- **Backend**: Core PHP 8+
- **Database**: MySQL 8+
- **Database Connection**: PHP Data Objects (PDO)
- **Frontend**: HTML5, CSS3, Bootstrap 5.3 (via CDN), Bootstrap Icons
- **Scripting**: Minimal vanilla JavaScript for confirmation handling
- **No Complex Frameworks**: Built intentionally without Laravel, React, or heavy third-party packages to ensure code explainability and fundamental understanding.

---

## Project Structure

```text
RTIH/
│
├── config/
│   └── database.php       # PDO database connection & error handling
│
├── includes/
│   ├── auth_check.php     # Session check protecting private pages
│   ├── csrf.php           # CSRF token generation and validation helpers
│   ├── header.php         # Reusable HTML head & Bootstrap navigation bar
│   └── footer.php         # Reusable page footer & script imports
│
├── auth/
│   ├── register.php       # Account registration with password hashing
│   ├── login.php          # Login with password verification & session setup
│   └── logout.php         # Session termination & cookie cleanup
│
├── tasks/
│   ├── index.php          # Dashboard: Read tasks, search by title, filter by status
│   ├── create.php         # Create new task form & backend validation
│   ├── edit.php           # Edit task details with ownership verification
│   └── delete.php         # Confirmation view & secure deletion logic
│
├── assets/
│   └── css/
│       └── style.css      # Clean custom styling and status badge themes
│
├── index.php              # Root entry redirecting to dashboard or login
├── database.sql           # Database schema and table definitions
└── README.md              # Project documentation and interview guide
```

---

## Features

- **User Authentication**: Secure account registration, login, and logout.
- **Task Management (CRUD)**:
  - **Create**: Add tasks with title, optional description, and status.
  - **Read**: View personal tasks in a responsive Bootstrap table.
  - **Update**: Edit existing task title, description, or change status.
  - **Delete**: Remove tasks with an explicit confirmation step.
- **Status Management**: Track progress with `Pending`, `In Progress`, and `Completed` statuses.
- **Search**: Search tasks in real-time by task title.
- **Status Filter**: Filter tasks by status dropdown (e.g., view only Pending or Completed).
- **Combined Search & Filter**: Search keywords and status filters work simultaneously.
- **Dual Validation**:
  - Frontend: HTML5 validation (`required`, `maxlength`).
  - Backend: Server-side validation ensuring title presence, length limits, and enum matching.
- **Data Isolation**: Strict user authorization ensuring users never see or modify other users' tasks.
- **Security Features**:
  - Password hashing with `password_hash()`.
  - Password verification with `password_verify()`.
  - PDO Prepared Statements for 100% of SQL queries (SQL Injection defense).
  - Output escaping with `htmlspecialchars()` (XSS defense).
  - Anti-CSRF session token generation and verification.
  - Session regeneration on login to prevent session fixation.

---

## Database Setup

Follow these simple steps using XAMPP:

1. **Install XAMPP**: Download and install XAMPP with PHP 8+ and MySQL from [apachefriends.org](https://www.apachefriends.org).
2. **Start Services**: Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. **Open phpMyAdmin**: Navigate to `http://localhost/phpmyadmin` in your web browser.
4. **Import Database**:
   - Click on the **Import** tab at the top.
   - Choose the `database.sql` file located in this project directory.
   - Click **Import** (or **Go**).
   - This automatically creates the `rtih_task_tracker` database and the `users` and `tasks` tables with foreign key constraints.
5. **Configure Database Connection**:
   - Open `config/database.php`.
   - Verify the database connection settings match your local environment:
     ```php
     $db_host = 'localhost';
     $db_name = 'rtih_task_tracker';
     $db_user = 'root';
     $db_pass = ''; // Default XAMPP MySQL password is blank
     ```
6. **Deploy to htdocs**:
   - Copy or move the project folder into your XAMPP web root directory:
     `C:\xampp\htdocs\rtih-task-tracker\`
7. **Run the Application**:
   - Open your browser and navigate to:
     `http://localhost/rtih-task-tracker/`
   - You will automatically be routed to the Login page.

---

## Test Login

Evaluators are encouraged to use the **Register** page to create fresh test accounts.

To test multi-user isolation:
1. Register Account A: `alice@example.com` / `Password123`
2. Create tasks under Alice's account.
3. Log out and register Account B: `bob@example.com` / `Password123`
4. Notice that Bob's dashboard is completely empty and Bob cannot see, edit, or delete Alice's tasks.

---

## User Roles and Permissions

This project implements a single, clear role: **`User`**.

### Permissions:
- A user can create their own tasks.
- A user can view only their own tasks.
- A user can edit only their own tasks.
- A user can delete only their own tasks.
- A user cannot view, edit, or delete another user's tasks.

### How Authorization is Enforced:
Authorization is strictly enforced in the backend database queries using session identity (`$_SESSION['user_id']`). Even if a user attempts to manually manipulate URL parameters (e.g., requesting `edit.php?id=99` or sending a POST request to delete task ID 99), the query includes:
```sql
WHERE id = :id AND user_id = :user_id
```
If the task does not belong to the currently logged-in user, the database returns no matching rows, and the application denies the action.

---

## Security

| Security Layer | Implementation | Purpose |
|---|---|---|
| **Password Security** | `password_hash($password, PASSWORD_DEFAULT)` | Securely hashes passwords using BCrypt with salt before saving to the database. |
| **Authentication Verification** | `password_verify($password, $user['password'])` | Validates entered passwords against the stored cryptographic hash. |
| **Session Fixation Defense** | `session_regenerate_id(true)` | Generates a new session ID immediately upon successful login. |
| **SQL Injection Defense** | PDO Prepared Statements (`$pdo->prepare()`) | Parameterizes all queries so user input is never executed as SQL code. |
| **Cross-Site Scripting (XSS)** | `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')` | Encodes user-provided HTML characters before rendering in the browser. |
| **CSRF Defense** | Session token with `hash_equals()` | Validates random tokens on all state-altering POST requests. |
| **Route Protection** | `includes/auth_check.php` | Redirects unauthenticated visitors attempting to access tasks pages. |
| **Task Ownership** | `WHERE id = ? AND user_id = ?` | Enforces authorization at the database level on every read, update, and delete. |

---

## Backup and Restore

### Backup Database:
- **Using phpMyAdmin**:
  1. Open `http://localhost/phpmyadmin`.
  2. Select the `rtih_task_tracker` database from the left sidebar.
  3. Click the **Export** tab.
  4. Choose **Quick** export format and click **Export**. A `.sql` file will be downloaded.
- **Using CLI (mysqldump)**:
  ```bash
  mysqldump -u root -p rtih_task_tracker > backup_rtih_task_tracker.sql
  ```

### Restore Database:
- **Using phpMyAdmin**:
  1. Open `http://localhost/phpmyadmin`.
  2. Create an empty database named `rtih_task_tracker` (if not already present).
  3. Click the **Import** tab.
  4. Select your `.sql` backup file and click **Import**.
- **Using CLI (mysql)**:
  ```bash
  mysql -u root -p rtih_task_tracker < backup_rtih_task_tracker.sql
  ```

---

## Testing

Every feature was tested against the project requirements:

| Test Case | Steps / Input | Expected Result | Status |
|---|---|---|:---:|
| **Register user** | Submit valid name, unique email, matching passwords | User registered, success message displayed, link to login shown | **Pass** |
| **Register duplicate email** | Register with an already registered email | Friendly error displayed: "An account with this email address already exists." | **Pass** |
| **Register mismatched passwords** | Submit differing password and confirm password | Error displayed: "Password and confirmation password do not match." | **Pass** |
| **Login with valid credentials** | Submit correct registered email and password | Session started, redirected to `tasks/index.php` showing user name | **Pass** |
| **Login with wrong password** | Submit correct email with wrong password | Generic error shown: "Invalid email or password." | **Pass** |
| **Login with non-existent email** | Submit unregistered email | Generic error shown: "Invalid email or password." | **Pass** |
| **Add task with valid data** | Title: "Complete Report", Status: "In Progress" | Task saved, redirected to dashboard with "Task added successfully." | **Pass** |
| **Empty title validation** | Submit Add Task form with empty title | Form blocked; backend error: "Task title is required." | **Pass** |
| **Edit task** | Modify title and change status to "Completed" | Database updated, redirected with "Task updated successfully." | **Pass** |
| **Delete task with confirmation** | Click delete, view confirmation card, click "Yes, Delete" | Task removed, redirected with "Task deleted successfully." | **Pass** |
| **Search tasks by title** | Enter search keyword in search box | Only tasks with matching title belonging to current user are shown | **Pass** |
| **Filter tasks by status** | Select "In Progress" from dropdown | Only "In Progress" tasks belonging to current user are displayed | **Pass** |
| **Combined search & filter** | Enter title keyword AND select status | Tasks matching BOTH criteria are displayed | **Pass** |
| **Direct URL access without login** | Open `http://localhost/rtih-task-tracker/tasks/index.php` in incognito | Blocked and redirected to `auth/login.php?error=unauthorized` | **Pass** |
| **IDOR / Cross-User Access Attempt** | User A tries to edit User B's task via `edit.php?id=XX` | Access denied, redirected to index with unauthorized error | **Pass** |
| **CSRF tampering test** | Submit task update form with altered/missing CSRF token | Request rejected with "Invalid request." | **Pass** |
| **XSS injection test** | Enter `<script>alert('XSS')</script>` as title | Script is escaped by `htmlspecialchars()` and rendered safely as plain text | **Pass** |

---

## Bugs Found and Fixed

During the review and testing phase:
1. **Direct ID URL Tampering (IDOR)**:
   - *Issue*: Initially, editing a task loaded it simply by `SELECT * FROM tasks WHERE id = ?`. If a logged-in user changed the ID in the URL to another user's task ID, the form loaded another user's task details.
   - *Fix*: Changed the query to `SELECT * FROM tasks WHERE id = ? AND user_id = ?` and added an authorization check that redirects unauthorized attempts.
2. **CSRF Verification on Deletion**:
   - *Issue*: A GET link to `delete.php?id=XX` could have allowed malicious sites to trigger deletions via image tags or links.
   - *Fix*: Added a two-step confirmation page with a POST form containing a valid CSRF token.
3. **Session Fixation**:
   - *Issue*: The session ID was unchanged between pre-login and post-login.
   - *Fix*: Added `session_regenerate_id(true)` immediately upon successful credential validation.

---

## AI Usage Note

AI coding assistance was used during development. I reviewed and tested the generated code and made changes after understanding the implementation.

**Example of a change made after review**:
> "I reviewed the task update and delete logic and updated the SQL queries to explicitly include `AND user_id = ?` alongside `WHERE id = ?`. This guarantees that even if a malicious user guesses another user's task ID, the database operation affects zero rows and the user is prevented from modifying or deleting anyone else's tasks."

---

## Completion Note

### Levels Completed:
- **Level 1 — Completed** (Full CRUD: Create, Read, Update, Delete with database persistence).
- **Level 2 — Completed** (Search by title, Filter by status, Combined search/filter, Frontend & Backend validation).
- **Level 3 — Completed** (Password hashing, PDO prepared statements, XSS defense, CSRF defense, and strict user-level authorization).

