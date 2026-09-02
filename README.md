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




Step 3: Install Packages
bash
composer install
Step 4: Set Up Environment
bash
# Copy the example file
cp .env.example .env

# Edit .env with your own details
# Use any text editor (Notepad, VS Code, etc.)
Your .env should look like this:

env
DB_HOST=localhost
DB_NAME=login_system
DB_USER=root
DB_PASS=your_password

TELERIVET_API_KEY=sk_your_api_key_here
TELERIVET_PROJECT_ID=p-your_project_id_here

APP_NAME=Secure Login System
OTP_EXPIRY_MINUTES=5
Step 5: Create the Database
Open phpMyAdmin (http://localhost/phpmyadmin)

Click "New" and create a database called login_system

Go to the "Import" tab

Select the database.sql file and run it

Step 6: Test It
Start Apache and MySQL in XAMPP

Go to http://localhost/login_system/register.php

Create an account

Try logging in!

📱 Telerivet Setup (For SMS)
Step 1: Create an Account
Go to https://telerivet.com

Sign up (it's free)

Step 2: Create a Project
In your dashboard, click "Create New Project"

Name it something like "Login System"

Step 3: Get Your Keys
Go to Settings → API Keys

Click "Generate API Key"

Copy the key

Copy your Project ID from the URL

Step 4: For SMS to Actually Send
Option A: Use a Phone as Gateway (FREE)

Install Telerivet Gateway app on an Android phone

Put a SIM card with load in it

Scan the QR code in your Telerivet dashboard

Your phone becomes the SMS sender

Option B: Buy a Virtual Number (PAID)

In Telerivet dashboard, go to Phone Numbers

Buy a virtual number for your country

SMS will be sent through that number

👤 How to Use the System
Register
Go to register.php

Fill in your details

Click "Create Account"

Login
Go to login.php

Enter your phone and password

Click "Login"

If It's a New Device:
You'll get an SMS with a 6-digit code

Enter the code on the verify page

You're in!

Dashboard
See your profile

View your login history

Each login session shows the device, IP, and time

Logout
Click "Logout" button

Session ends

🔒 Security Tips
Don't share your .env file - It has your passwords!

Use strong passwords - At least 8 characters

Only access via HTTPS in production

OTP expires after 5 minutes

OTP works only once

❓ Common Problems & Solutions
"Composer not found"
Add Composer to PATH

Or use php composer.phar instead

"Database connection failed"
Double-check your .env file

Make sure XAMPP is running

"SMS not sending"
Check your Telerivet API key

Check your Telerivet Project ID

Do you have SMS credits?

Do you have a sending number?

"SMS not received"
Check Telerivet dashboard (Messages tab)

Check your phone number format: +639xxxxxxxxx

"OTP expired"
It's set to 5 minutes

You can change it in .env

📸 Screenshots
Here's what the system looks like:

Page	What you see

Register	Create your account
Login	Sign in with phone and password
OTP SMS	Code sent to your phone
Verify OTP	Enter the 6-digit code
Dashboard	Your profile and login history

Link - https://drive.google.com/drive/folders/1SdYb47kNaAlnoPGe3PXHDNE0ILsdh9zW?usp=sharing

🎥 Demonstration Video
This video shows the system in action:

Registration - Creating a new account

Login - Signing in

OTP SMS - Receiving the code on phone

Verification - Entering the code

Dashboard - Viewing login history

Video Link - https://drive.google.com/drive/folders/1OwVi3-Hu9_KkEW8OEWLsXNshx3NQ3Oml?usp=sharing

🙋 Who Made This
Sean Marion Gillego
BS IT STUDENT
ITELEC07 - Multi-Factor Authentication with SMS

💭 Final Thoughts
Building this system taught me a lot about:

How authentication works

Why 2FA is important

How SMS APIs work

Secure coding practices

Session management

It's amazing to see how a simple OTP can make a login system so much more secure!


