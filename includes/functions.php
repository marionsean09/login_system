<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';


/*
|--------------------------------------------------------------------------
| GENERATE OTP
|--------------------------------------------------------------------------
*/

function generateOTP($length = 6)
{
    if ($length < 4) {
        $length = 6;
    }

    $min = (int) pow(10, $length - 1);
    $max = (int) pow(10, $length) - 1;

    return (string) random_int($min, $max);
}


/*
|--------------------------------------------------------------------------
| FORMAT PHONE NUMBER
|--------------------------------------------------------------------------
*/

function formatPhoneNumber($phone_number)
{
    $phone_number = preg_replace(
        '/[^0-9+]/',
        '',
        trim($phone_number)
    );

    // 09123456789
    if (preg_match('/^09\d{9}$/', $phone_number)) {
        return '+63' . substr($phone_number, 1);
    }

    // 639123456789
    if (preg_match('/^639\d{9}$/', $phone_number)) {
        return '+' . $phone_number;
    }

    // +639123456789
    if (preg_match('/^\+639\d{9}$/', $phone_number)) {
        return $phone_number;
    }

    return $phone_number;
}


/*
|--------------------------------------------------------------------------
| SEND OTP THROUGH TELERIVET
|--------------------------------------------------------------------------
*/

function sendOTPSMS($phone_number, $otp, $name)
{
    try {

        $api_key = trim(
            $_ENV['TELERIVET_API_KEY'] ?? ''
        );

        $project_id = trim(
            $_ENV['TELERIVET_PROJECT_ID'] ?? ''
        );


        // Check API key
        if ($api_key === '') {
            throw new Exception(
                'TELERIVET_API_KEY is missing from the .env file.'
            );
        }


        // Check project ID
        if ($project_id === '') {
            throw new Exception(
                'TELERIVET_PROJECT_ID is missing from the .env file.'
            );
        }


        // Check phone number
        if (empty($phone_number)) {
            throw new Exception(
                'Phone number is empty.'
            );
        }


        // Format phone number
        $phone_number = formatPhoneNumber(
            $phone_number
        );


        // Validate Philippine number
        if (!preg_match('/^\+639\d{9}$/', $phone_number)) {

            throw new Exception(
                'Invalid Philippine mobile number: ' .
                $phone_number
            );
        }


        // Connect to Telerivet
        $telerivet = new Telerivet_API(
            $api_key
        );


        // Initialize project
        $project = $telerivet->initProjectById(
            $project_id
        );


        if (!$project) {

            throw new Exception(
                'Unable to initialize Telerivet project.'
            );
        }


        // Application settings
        $app_name = $_ENV['APP_NAME']
            ?? 'Secure Login System';

        $expiry_minutes = (int) (
            $_ENV['OTP_EXPIRY_MINUTES']
            ?? 5
        );


        if ($expiry_minutes <= 0) {
            $expiry_minutes = 5;
        }


        // Create SMS message
        $message =
            "Hello " .
            $name .
            ", your OTP for " .
            $app_name .
            " is: " .
            $otp .
            ". This OTP will expire in " .
            $expiry_minutes .
            " minutes.";


        // Send SMS
        $result = $project->sendMessage([
            'to_number' => $phone_number,
            'content' => $message
        ]);


        if (!$result) {

            throw new Exception(
                'Telerivet returned an empty response.'
            );
        }


        return true;


    } catch (Throwable $e) {

        /*
        |--------------------------------------------------------------------------
        | LOG ERROR
        |--------------------------------------------------------------------------
        */

        error_log(
            'Telerivet SMS error: ' .
            $e->getMessage()
        );

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| GET DEVICE INFORMATION
|--------------------------------------------------------------------------
*/

function getDeviceInfo()
{
    $user_agent =
        $_SERVER['HTTP_USER_AGENT'] ?? '';

    $ip =
        $_SERVER['REMOTE_ADDR'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | CREATE DEVICE ID
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | Do NOT use the IP address as part of the device ID.
    |
    | This prevents the OTP from becoming invalid if the IP
    | changes between login and verification.
    |
    */

    $device_id = hash(
        'sha256',
        $user_agent
    );


    /*
    |--------------------------------------------------------------------------
    | DETERMINE DEVICE TYPE
    |--------------------------------------------------------------------------
    */

    if (
        stripos($user_agent, 'Mobile') !== false ||
        stripos($user_agent, 'Android') !== false
    ) {

        $device_type = 'Mobile';

    } elseif (
        stripos($user_agent, 'Tablet') !== false ||
        stripos($user_agent, 'iPad') !== false
    ) {

        $device_type = 'Tablet';

    } else {

        $device_type = 'Desktop';
    }


    return [

        'id' =>
            $device_id,

        'type' =>
            $device_type,

        'ip' =>
            $ip,

        'user_agent' =>
            $user_agent
    ];
}


/*
|--------------------------------------------------------------------------
| CHECK TRUSTED DEVICE
|--------------------------------------------------------------------------
*/

function isTrustedDevice(
    $user_id,
    $device_id,
    $pdo
) {

    $stmt = $pdo->prepare(
        "SELECT id
         FROM login_sessions
         WHERE user_id = ?
         AND device_info = ?
         AND is_verified = 1
         LIMIT 1"
    );


    $stmt->execute([
        $user_id,
        $device_id
    ]);


    return
        $stmt->fetch(PDO::FETCH_ASSOC)
        !== false;
}


/*
|--------------------------------------------------------------------------
| SAVE LOGIN SESSION
|--------------------------------------------------------------------------
*/

function saveLoginSession(
    $user_id,
    $device_info,
    $pdo
) {

    $stmt = $pdo->prepare(
        "INSERT INTO login_sessions
        (
            user_id,
            device_info,
            ip_address,
            user_agent,
            is_verified
        )
        VALUES (?, ?, ?, ?, 0)"
    );


    $stmt->execute([

        $user_id,

        $device_info['id'],

        $device_info['ip'],

        $device_info['user_agent']

    ]);


    return $pdo->lastInsertId();
}


/*
|--------------------------------------------------------------------------
| SAVE OTP
|--------------------------------------------------------------------------
*/

function saveOTP(
    $user_id,
    $otp,
    $device_id,
    $pdo
) {

    /*
    |--------------------------------------------------------------------------
    | GET OTP EXPIRATION
    |--------------------------------------------------------------------------
    */

    $expiry_minutes = (int) (
        $_ENV['OTP_EXPIRY_MINUTES']
        ?? 5
    );


    if ($expiry_minutes <= 0) {
        $expiry_minutes = 5;
    }


    /*
    |--------------------------------------------------------------------------
    | INVALIDATE PREVIOUS OTPs
    |--------------------------------------------------------------------------
    */

    $invalidate = $pdo->prepare(
        "UPDATE otp_codes
         SET is_used = 1
         WHERE user_id = ?
         AND device_id = ?
         AND is_used = 0"
    );


    $invalidate->execute([
        $user_id,
        $device_id
    ]);


    /*
    |--------------------------------------------------------------------------
    | CREATE EXPIRATION DATE
    |--------------------------------------------------------------------------
    */

    $expires_at = date(
        'Y-m-d H:i:s',
        time() + ($expiry_minutes * 60)
    );


    /*
    |--------------------------------------------------------------------------
    | SAVE NEW OTP
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        "INSERT INTO otp_codes
        (
            user_id,
            otp_code,
            device_id,
            expires_at,
            is_used
        )
        VALUES (?, ?, ?, ?, 0)"
    );


    $stmt->execute([

        $user_id,

        $otp,

        $device_id,

        $expires_at

    ]);


    return $pdo->lastInsertId();
}


/*
|--------------------------------------------------------------------------
| VERIFY OTP
|--------------------------------------------------------------------------
*/

function verifyOTP(
    $user_id,
    $otp,
    $device_id,
    $pdo
) {

    /*
    |--------------------------------------------------------------------------
    | GET THE MOST RECENT OTP
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        "SELECT *
         FROM otp_codes
         WHERE user_id = ?
         AND device_id = ?
         ORDER BY id DESC
         LIMIT 1"
    );


    $stmt->execute([
        $user_id,
        $device_id
    ]);


    $otp_record =
        $stmt->fetch(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | NO OTP FOUND
    |--------------------------------------------------------------------------
    */

    if (!$otp_record) {
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK IF OTP WAS USED
    |--------------------------------------------------------------------------
    */

    if (
        (int) $otp_record['is_used'] === 1
    ) {

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK OTP VALUE
    |--------------------------------------------------------------------------
    */

    if (
        (string) $otp_record['otp_code']
        !==
        (string) $otp
    ) {

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK EXPIRATION
    |--------------------------------------------------------------------------
    */

    $expires_at = strtotime(
        $otp_record['expires_at']
    );


    if ($expires_at === false) {
        return false;
    }


    if ($expires_at <= time()) {
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | MARK OTP AS USED
    |--------------------------------------------------------------------------
    */

    $update = $pdo->prepare(
        "UPDATE otp_codes
         SET is_used = 1
         WHERE id = ?"
    );


    $update->execute([
        $otp_record['id']
    ]);


    /*
    |--------------------------------------------------------------------------
    | MARK LOGIN SESSION AS VERIFIED
    |--------------------------------------------------------------------------
    */

    if (
        isset($_SESSION['pending_session_id']) &&
        !empty($_SESSION['pending_session_id'])
    ) {

        $session = $pdo->prepare(
            "UPDATE login_sessions
             SET is_verified = 1
             WHERE id = ?
             AND user_id = ?"
        );


        $session->execute([

            $_SESSION['pending_session_id'],

            $user_id

        ]);

    } else {

        /*
        |--------------------------------------------------------------------------
        | FALLBACK
        |--------------------------------------------------------------------------
        */

        $session = $pdo->prepare(
            "UPDATE login_sessions
             SET is_verified = 1
             WHERE user_id = ?
             AND device_info = ?
             AND is_verified = 0"
        );


        $session->execute([

            $user_id,

            $device_id

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | OTP VERIFIED
    |--------------------------------------------------------------------------
    */

    return true;
}