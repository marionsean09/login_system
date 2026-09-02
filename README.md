# 🔐 Secure Login System with SMS OTP

A simple and secure login system with Two-Factor Authentication (2FA) via SMS. 
Built for the ITELEC07 - Multi-Factor Authentication with SMS activity.

---

## 📌 About This Project

This is a complete login system that adds an extra layer of security. 
When you log in from a new device, the system sends a One-Time Password (OTP) 
to your phone via SMS. You need to enter that code to complete the login.

Think of it like this:
> Password = Something you know  
> OTP = Something you have (your phone)

---

## ✨ Features

- **Register** - Create your account with phone number and password
- **Login** - Enter your credentials
- **OTP via SMS** - Receive a code on your phone for verification
- **Trusted Devices** - Once verified, device is remembered
- **Dashboard** - View your profile and login history
- **Resend OTP** - If you didn't receive the code
- **Logout** - Securely end your session

---

## 🛠️ What I Used

| Technology | What it does |
|------------|--------------|
| PHP | Handles all the logic behind the scenes |
| MySQL | Stores user data, sessions, and OTPs |
| Telerivet | Sends SMS messages to your phone |
| Composer | Manages PHP packages |
| HTML/CSS/JS | Makes the pages look good |

---

## 📂 How It's Organized


login-system/
│
├── config/
│ └── database.php # Connects to your database
│
├── includes/
│ └── functions.php # All the important functions
│
├── css/
│ └── style.css # Makes everything look nice
│
├── vendor/ # PHP packages (from Composer)
│
├── .env # Your secret credentials (DO NOT SHARE!)
├── .env.example # Template for .env
├── .gitignore # Files to ignore in GitHub
├── composer.json # List of packages needed
├── database.sql # Database structure
│
├── register.php # Create an account
├── login.php # Sign in
├── verify-otp.php # Enter the code from your phone
├── resend-otp.php # Get a new code
├── dashboard.php # Your profile and login history
├── logout.php # Sign out
│
└── screenshots/ # Pictures of the working system



---

## 🚀 How to Set It Up

### Step 1: What You Need
- XAMPP (or WAMP) installed
- Composer installed
- A Telerivet account (free)

### Step 2: Download or Clone
```bash
git clone https://github.com/yourusername/login-system.git
cd login-system
