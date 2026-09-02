<?php

session_start();

require_once 'config/database.php';
require_once 'includes/functions.php';


/*
|--------------------------------------------------------------------------
| CHECK LOGIN SESSION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {

    header('Location: login.php');
    exit();

}


/*
|--------------------------------------------------------------------------
| GET USER INFORMATION
|--------------------------------------------------------------------------
*/

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    "SELECT *
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$stmt->execute([
    $user_id
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| CHECK IF USER EXISTS
|--------------------------------------------------------------------------
*/

if (!$user) {

    session_destroy();

    header('Location: login.php');
    exit();

}


/*
|--------------------------------------------------------------------------
| GET LOGIN HISTORY
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    "SELECT *
     FROM login_sessions
     WHERE user_id = ?
     ORDER BY login_time DESC
     LIMIT 10"
);

$stmt->execute([
    $user_id
]);

$login_history = $stmt->fetchAll(
    PDO::FETCH_ASSOC
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Dashboard - Secure Login System
    </title>

    <link rel="stylesheet" href="css/style.css">

</head>


<body>


<div class="dashboard">


    <!-- ======================================================
         DASHBOARD HEADER
         ====================================================== -->

    <div class="header">

        <h1>

            Welcome,
            <?= htmlspecialchars(
                $user['full_name']
            ) ?>!

        </h1>


        <div class="user-info">

            <p>

                <strong>
                    Full Name:
                </strong>

                <?= htmlspecialchars(
                    $user['full_name']
                ) ?>

            </p>


            <p>

                <strong>
                    Email:
                </strong>

                <?= htmlspecialchars(
                    !empty($user['email']) ? $user['email'] : 'Not provided'
                ) ?>

            </p>


            <div class="phone-info">

                <strong>
                    Registered Phone:
                </strong>

                <?= htmlspecialchars(
                    $user['phone_number']
                ) ?>

            </div>

        </div>


        <!-- ==================================================
             LOGOUT
             ================================================== -->

        <a
            href="logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>


    <!-- ======================================================
         LOGIN HISTORY
         ====================================================== -->

    <div class="device-list">

        <h2>
            Recent Login Sessions
        </h2>


        <?php if (count($login_history) > 0): ?>


            <?php foreach ($login_history as $session): ?>

                <div class="device-item">


                    <!-- DEVICE + STATUS -->

                    <div>

                        <span class="device-type">

                            <?= htmlspecialchars(
                                $session['device_info']
                                ?? 'Unknown Device'
                            ) ?>

                        </span>


                        <?php if (
                            $session['is_verified']
                        ): ?>

                            <span class="trusted">

                                ✓ Verified

                            </span>

                        <?php else: ?>

                            <span class="untrusted">

                                ⚠ Pending Verification

                            </span>

                        <?php endif; ?>


                    </div>


                    <!-- SESSION DETAILS -->

                    <div class="session-details">

                        <div>

                            <strong>
                                Login Time:
                            </strong>

                            <?= htmlspecialchars(
                                $session['login_time']
                                ?? 'Unknown'
                            ) ?>

                        </div>


                        <div>

                            <strong>
                                IP Address:
                            </strong>

                            <?= htmlspecialchars(
                                $session['ip_address']
                                ?? 'Unknown'
                            ) ?>

                        </div>


                        <div>

                            <strong>
                                User Agent:
                            </strong>

                            <?= htmlspecialchars(
                                $session['user_agent']
                                ?? 'Unknown'
                            ) ?>

                        </div>

                    </div>

                </div>


            <?php endforeach; ?>


        <?php else: ?>


            <div class="no-history">

                No login sessions found.

            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>