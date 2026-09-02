<?php

session_start();

require_once 'config/database.php';
require_once 'includes/functions.php';


/*
|--------------------------------------------------------------------------
| CHECK PENDING LOGIN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['pending_user_id']) ||
    !isset($_SESSION['pending_device_id'])
) {

    header('Location: login.php');
    exit();
}


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$error = '';
$success = '';

$user_id = $_SESSION['pending_user_id'];
$device_id = $_SESSION['pending_device_id'];


/*
|--------------------------------------------------------------------------
| GET PHONE NUMBER
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    "SELECT full_name, phone_number
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$stmt->execute([$user_id]);

$pending_user = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$pending_user) {

    session_destroy();
    header('Location: login.php');
    exit();
}


/*
|--------------------------------------------------------------------------
| HANDLE OTP SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $otp_input = trim($_POST['otp'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDATE OTP
    |--------------------------------------------------------------------------
    */

    if ($otp_input === '') {

        $error = 'Please enter the OTP.';

    } elseif (!preg_match('/^\d{6}$/', $otp_input)) {

        $error = 'Please enter a valid 6-digit OTP.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | VERIFY OTP
        |--------------------------------------------------------------------------
        */

        $verified = verifyOTP(
            $user_id,
            $otp_input,
            $device_id,
            $pdo
        );


        if ($verified) {

            /*
            |--------------------------------------------------------------------------
            | OTP SUCCESSFUL
            |--------------------------------------------------------------------------
            */

            session_regenerate_id(true);


            /*
            |--------------------------------------------------------------------------
            | CREATE AUTHENTICATED SESSION
            |--------------------------------------------------------------------------
            */

            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $pending_user['full_name'];
            $_SESSION['user_phone'] = $pending_user['phone_number'];


            /*
            |--------------------------------------------------------------------------
            | REMOVE PENDING LOGIN DATA
            |--------------------------------------------------------------------------
            */

            unset(
                $_SESSION['pending_user_id'],
                $_SESSION['pending_user_name'],
                $_SESSION['pending_user_phone'],
                $_SESSION['pending_device_id'],
                $_SESSION['pending_session_id']
            );


            /*
            |--------------------------------------------------------------------------
            | GO TO DASHBOARD
            |--------------------------------------------------------------------------
            */

            header('Location: dashboard.php');
            exit();

        } else {

            $error = 'Invalid or expired OTP! Please try again.';
        }
    }
}


/*
|--------------------------------------------------------------------------
| RESEND MESSAGES
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['otp_message'])) {

    $success = $_SESSION['otp_message'];
    unset($_SESSION['otp_message']);
}

if (isset($_SESSION['otp_error'])) {

    $error = $_SESSION['otp_error'];
    unset($_SESSION['otp_error']);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Secure Login System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="otp-wrapper">
    <div class="otp-container">

        <!-- Brand / Header -->
        <div class="otp-header">
            <div class="brand-icon">🔑</div>
            <h1>Verify <span class="highlight">OTP</span></h1>
            <p class="subtitle">Enter the verification code sent to your phone</p>
        </div>

        <!-- SMS Info -->
        <div class="sms-info">
            📱 OTP sent to: <strong><?= htmlspecialchars($pending_user['phone_number']) ?></strong>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- OTP Form -->
        <form method="POST" action="" class="otp-form">

            <div class="form-group">
                <label for="otp">Enter OTP</label>
                <input
                    type="text"
                    id="otp"
                    name="otp"
                    class="otp-input"
                    maxlength="6"
                    minlength="6"
                    pattern="[0-9]{6}"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    placeholder="000000"
                    required
                    autofocus
                >
                <div class="otp-hint">Enter the 6-digit code sent to your phone</div>
            </div>

            <button type="submit" class="btn btn-primary">Verify OTP</button>

        </form>

        <!-- Resend -->
        <div class="otp-resend">
            <p>Didn't receive the code?</p>
            <a href="resend-otp.php">Resend OTP</a>
        </div>

        <!-- Back to Login -->
        <div class="otp-footer">
            <a href="login.php">← Back to Login</a>
        </div>

        <!-- Cancel & Logout -->
        <div class="otp-cancel">
            <a href="logout.php" class="logout-small">✕ Cancel & Logout</a>
        </div>

    </div>
</div>

</body>

</html>