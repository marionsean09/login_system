<?php

session_start();

// For logout success message
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $message = 'You have been logged out successfully.';
    $message_type = 'success';
}

require_once 'config/database.php';
require_once 'includes/functions.php';

$message = $message ?? '';
$message_type = $message_type ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $phone_number = trim($_POST['phone_number'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($phone_number) || empty($password)) {

        $message = 'Please enter your phone number and password.';
        $message_type = 'error';

    } else {

        // Find user
        $stmt = $pdo->prepare(
            "SELECT *
             FROM users
             WHERE phone_number = ?
             LIMIT 1"
        );

        $stmt->execute([
            $phone_number
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check credentials
        if (
            !$user ||
            !password_verify(
                $password,
                $user['password']
            )
        ) {

            $message = 'Invalid phone number or password.';
            $message_type = 'error';

        } else {

            // Get device information
            $device_info = getDeviceInfo();
            $device_id = $device_info['id'];

            // Check trusted device
            $trusted = isTrustedDevice(
                $user['id'],
                $device_id,
                $pdo
            );

            if ($trusted) {

                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_phone'] = $user['phone_number'];

                header('Location: dashboard.php');
                exit();

            }

            session_regenerate_id(true);

            // Generate OTP
            $otp = generateOTP();

            // Save OTP
            saveOTP(
                $user['id'],
                $otp,
                $device_id,
                $pdo
            );

            // Save login session
            $session_id = saveLoginSession(
                $user['id'],
                $device_info,
                $pdo
            );

            $_SESSION['pending_user_id'] = $user['id'];
            $_SESSION['pending_user_name'] = $user['full_name'];
            $_SESSION['pending_user_phone'] = $user['phone_number'];
            $_SESSION['pending_device_id'] = $device_id;
            $_SESSION['pending_session_id'] = $session_id;

            $sms_sent = sendOTPSMS(
                $user['phone_number'],
                $otp,
                $user['full_name']
            );

            if ($sms_sent) {

                header('Location: verify-otp.php');
                exit();

            } else {

                unset(
                    $_SESSION['pending_user_id'],
                    $_SESSION['pending_user_name'],
                    $_SESSION['pending_user_phone'],
                    $_SESSION['pending_device_id'],
                    $_SESSION['pending_session_id']
                );

                $message = 'Unable to send OTP. Please check your Telerivet configuration and try again.';
                $message_type = 'error';
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Secure Login System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="login-wrapper">
    <div class="login-container">

        <!-- Brand / Header -->
        <div class="login-header">
            <div class="brand-icon">🔐</div>
            <h1>Welcome <span class="highlight">Back</span></h1>
            <p class="subtitle">Sign in to your secure account</p>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= htmlspecialchars($message_type) ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="" class="login-form">

            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input
                    type="tel"
                    id="phone_number"
                    name="phone_number"
                    placeholder="Enter your phone number"
                    value="<?= htmlspecialchars($_POST['phone_number'] ?? '') ?>"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary">Sign In</button>

        </form>

        <!-- Register Link -->
        <div class="login-footer">
            <p>Don't have an account? <a href="register.php">Create one</a></p>
        </div>

        <!-- Security Badge -->
        <div class="security-badge">
            <span>🔒</span>
            <span>Secure • Protected • 2FA</span>
        </div>

    </div>
</div>

</body>

</html>