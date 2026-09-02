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


$user_id =
    $_SESSION['pending_user_id'];

$device_id =
    $_SESSION['pending_device_id'];


/*
|--------------------------------------------------------------------------
| GET USER
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    "SELECT *
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$stmt->execute([
    $user_id
]);

$user =
    $stmt->fetch(PDO::FETCH_ASSOC);


if (!$user) {

    session_destroy();

    header('Location: login.php');
    exit();
}


/*
|--------------------------------------------------------------------------
| GENERATE NEW OTP
|--------------------------------------------------------------------------
*/

$otp = generateOTP();


/*
|--------------------------------------------------------------------------
| SAVE NEW OTP
|--------------------------------------------------------------------------
*/

saveOTP(
    $user_id,
    $otp,
    $device_id,
    $pdo
);


/*
|--------------------------------------------------------------------------
| SEND OTP
|--------------------------------------------------------------------------
*/

$sms_sent = sendOTPSMS(
    $user['phone_number'],
    $otp,
    $user['full_name']
);


if ($sms_sent) {

    $_SESSION['otp_message'] =
        'A new OTP has been sent to your phone.';

} else {

    $_SESSION['otp_error'] =
        'Unable to send a new OTP. Please try again.';
}


/*
|--------------------------------------------------------------------------
| RETURN TO OTP PAGE
|--------------------------------------------------------------------------
*/

header(
    'Location: verify-otp.php'
);

exit();