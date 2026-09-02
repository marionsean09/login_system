<?php

require_once 'config/database.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate full name
    if (empty($full_name)) {

        $message = 'Full name is required.';
        $message_type = 'error';

    // Validate phone number
    } elseif (
        empty($phone_number) ||
        !preg_match('/^\+?[1-9]\d{1,14}$/', $phone_number)
    ) {

        $message = 'Please enter a valid phone number.';
        $message_type = 'error';

    // Validate email - optional
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = 'Please enter a valid email address.';
        $message_type = 'error';

    // Validate password
    } elseif (strlen($password) < 8) {

        $message = 'Password must be at least 8 characters.';
        $message_type = 'error';

    // Confirm password
    } elseif ($password !== $confirm_password) {

        $message = 'Passwords do not match.';
        $message_type = 'error';

    } else {

        // Check if phone number already exists
        $stmt = $pdo->prepare(
            "SELECT id FROM users WHERE phone_number = ?"
        );

        $stmt->execute([$phone_number]);

        if ($stmt->fetch()) {

            $message = 'Phone number is already registered.';
            $message_type = 'error';

        } else {

            // Hash password
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Insert user
            $stmt = $pdo->prepare(
                "INSERT INTO users
                (phone_number, email, password, full_name)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->execute([
                $phone_number,
                $email,
                $hashed_password,
                $full_name
            ]);

            $message = 'Registration successful! You can now log in.';
            $message_type = 'success';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Secure Login System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="register-wrapper">
    <div class="register-container">

        <!-- Brand / Header -->
        <div class="register-header">
            <div class="brand-icon">📝</div>
            <h1>Create <span class="highlight">Account</span></h1>
            <p class="subtitle">Join our secure platform</p>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= htmlspecialchars($message_type) ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" action="" class="register-form">

            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    placeholder="Enter your full name"
                    value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input
                    type="tel"
                    id="phone_number"
                    name="phone_number"
                    placeholder="+639123456789"
                    value="<?= htmlspecialchars($_POST['phone_number'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Email Address <span class="optional">(Optional)</span></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Minimum 8 characters"
                    required
                    minlength="8"
                >
                <div class="password-hint">Must be at least 8 characters</div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Re-enter your password"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary">Create Account</button>

        </form>

        <!-- Login Link -->
        <div class="register-footer">
            <p>Already have an account? <a href="login.php">Sign in</a></p>
        </div>

        <!-- Terms -->
        <div class="terms">
            By creating an account, you agree to our <a href="#">Terms</a> and <a href="#">Privacy Policy</a>
        </div>

    </div>
</div>

</body>

</html>