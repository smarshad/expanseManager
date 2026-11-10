# Laravel Authentication & Security Enhancement Project

This repository showcases the process of converting an existing HTML theme into a Laravel application, setting up authentication using Laravel Breeze, and enhancing it with modern security and architectural improvements such as lock screens, two-factor authentication, and a service-layer refactor.

---

## 📑 Table of Contents
- [Project Overview](#-project-overview)
- [Tech Stack](#-tech-stack)
- [Setup Instructions](#-setup-instructions)
- [Daily Progress](#-daily-progress)
  - [Day 1-2](#day-1-2)
  - [Day 3](#day-3)
  - [Day 4](#day-4)
  - [Day 5](#day-5)
  - [Day 6](#day-6)
  - [Day 7](#day-7)
- [Key Features Implemented](#-key-features-implemented)
- [Folder Structure](#-folder-structure)
- [Future Improvements](#-future-improvements)
- [Author](#-author)

---

## 🧩 Project Overview
This project demonstrates how to:

- Integrate an existing front-end theme into a fresh Laravel installation.  
- Implement and customize Laravel Breeze authentication views.  
- Extend authentication with:
  - Session-based and database-based **lock screens**  
  - **Two-factor authentication (2FA)** using email OTP  
  - **Service-layer architecture** for cleaner separation of concerns  
- Improve maintainability, scalability, and code readability.

---

## ⚙️ Tech Stack
| Layer | Technology |
|-------|-------------|
| Framework | **Laravel 11** |
| Frontend | **Blade Templates** (custom theme integration) |
| Authentication | **Laravel Breeze** |
| Database | **MySQL / SQLite** |
| Mailing | **Laravel Mailables** (`SendOtpMail`) |
| Architecture | **MVC + Service Layer** |

---

## 🧭 Setup Instructions
```bash
# Clone the repository
git clone https://github.com/your-username/laravel-auth-enhanced.git
cd laravel-auth-enhanced

# Install dependencies
composer install

# Copy environment file and generate key
cp .env.example .env
php artisan key:generate

# Run database migrations
php artisan migrate

# Start the development server
php artisan serve

Day 1-2

    Converted existing HTML theme into a Laravel application.
    Verified layout integration and pushed initial setup to GitHub.

Day 3

    Installed Laravel Breeze authentication scaffolding
    composer require laravel/breeze --dev
    php artisan breeze:install blade
    php artisan migrate
    php artisan serve
    Tested default Breeze routes and views (login, register, password reset, etc.).

Day 4

    Customized Breeze auth pages with the existing theme design.
    Implemented Lock Screen Feature:
    Added is_locked column in users table.
    Session-based Lock: user manually locks screen; session key is_locked=true.
    Database-based Lock: admin can permanently lock a user.
    Forgot/Reset Password screen pending.

Day 5

    Enabled Two-Factor Authentication (2FA).
    Added option in profile for users to enable/disable two_factor_auth.
    Implemented OTP generation and email delivery.

Day 6

    Completed Forgot Password functionality and tested end-to-end.
    Updated LockMiddleware to restrict access when user is locked.
    Applied middleware to protected routes.

Day 7
    
    1 Authentication Refactor & Service Layer Integration
        Refactored authentication flow to follow clean architecture.
        Grouped routes under Auth namespace:
        Route::middleware('guest')->prefix('auth')->group(function () {
            Route::get('login', [LoginController::class, 'showLogin'])->name('login');
            Route::post('login', [LoginController::class, 'login'])->name('auth.login');
        });

    2 AuthController → LoginController
        Moved logic from AuthController into dedicated LoginController.
        Controller now only handles HTTP requests, delegating business logic to LoginService.
        public function login(LoginRequest $request){
            return $this->loginService->handleLogin($request);
        }

        Here, Laravel automatically injects the validated form request (LoginRequest) using Dependency Injection.
        When this method is called, Laravel:
        Creates a new LoginRequest instance.
        Automatically runs its rules() and authorize() methods.
        If validation fails → it redirects back automatically.
        If validation passes → $request->validated() can be safely used.
        So your controller’s role is to receive the validated input and delegate logic to the service

    3 loginService
        public function handleLogin(LoginRequest $request)
        {
            $validated = $request->validated();
            ------------------------
            ------------------------
            ------------------------
        }
        simply returns the array of safe input.
        The service layer doesn’t have to validate again; it just uses the already validated data.

        Path: app/Services/Auth/LoginService.php
        Handles all login-related business logic:
        Generates and manages OTPs for users with 2FA.
        Sends OTP via SendOtpMail.
        Redirects user to OTP verification screen.
    
    4  Dependency Injection (DI) Usage

        The `LoginService` uses **constructor-based dependency injection** to receive required classes and services instead of creating them manually.  
        This improves testability, flexibility, and adherence to Clean Architecture principles.

        **Example:**
        ```php
        namespace App\Services\Auth;

        use App\Mail\SendOtpMail;
        use App\Models\User;
        use App\Models\EmailOtp;
        use Illuminate\Support\Facades\Mail;
        use Illuminate\Support\Facades\Log;
        use Illuminate\Support\Carbon;

    5 Configuration Enhancements

        Added OTP expiry setting:
        config/auth.php
        'otp_expiry' => env('OTP_EXPIRY_MINUTES', 10),
        .env -- OTP_EXPIRY_MINUTES=10

    Key Features Implemented\
        Laravel Breeze setup & customization
        Lock Screen (session + admin controlled)
        Email-based 2FA (OTP verification)
        Controller + Service architecture
        Clean, testable, and maintainable codebase

    Folder Structure

        app/
        ├── Http/
        │   ├── Controllers/
        │   │   └── Auth/
        │   │       └── LoginController.php
        │   ├── Middleware/
        │   │   └── LockMiddleware.php
        │   └── Requests/
        │       └── Auth/
        │           └── LoginRequest.php
        ├── Mail/
        │   └── SendOtpMail.php
        ├── Models/
        │   └── User.php
        └── Services/
            └── Auth/
                └── LoginService.php

Day 8
    Add one var in .env AUTH_WITH_OTP=true/false so we can use registeration method with or without otp
    create helper for otp generation
        mkdir app/Helpers
        touch app/Helpers/helpers.php

        Edit your composer.json and add under "autoload":
        "autoload": {
            "files": [
                "app/Helpers/helpers.php"
            ]
        }
        composer dump-autoload
        then use $otp = generate_otp();


    Create Register Controller
    Create Register Service
        +-------------------------+
        |  User submits Register  |
        +-----------+-------------+
                    |
                    v
        +-------------------------+
        | RegisterRequest validates|
        +-----------+-------------+
                    |
                    v
        +-------------------------+
        | RegisterService::register|
        +-----------+-------------+
                    |
                    +-- if AUTH_WITH_OTP = true
                    |       |
                    |       v
                    |  Create OTP + send email
                    |  Save user data to session
                    |  Redirect -> verify.otp
                    |
                    +-- else
                            |
                            v
                    Create user directly
                    Login + redirect -> dashboard

        User submits /register form
                │
                ▼
        [ Controller ] → calls RegisterService
                │
                ▼
        RegisterService returns redirect()->route('verify.otp')
                │
                ▼
        Browser receives HTTP 302 redirect
                │
                ▼
        Browser navigates to /verify/otp page

        Create New service RegisterService/RegisterOtpService
        Create New controller RegisterController
        Create New request RegisterRequest
        Remove register code from AuthController
        Remove routes related to register from web to authroute

        //////Improvement////////

        Create BaseOtpService class
