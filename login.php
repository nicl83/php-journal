<?php
require __DIR__ . '/vendor/autoload.php';

use Defuse\Crypto\Crypto;
use Defuse\Crypto\KeyProtectedByPassword;

$login_failure = False;
session_start();

if ($_SESSION['authorised']) {
    # User is authed, how did we get here?
    header("Location: index.php");
    exit();
}
if (isset($_POST['login_name']) and isset($_POST['password'])) {
    # try to login    
    $db = new mysqli("localhost", "nick", "nickmysql", "journal_site");
    if ($db->connect_error) {
        die("Fatal backend error: " . $db->connect_error);
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE login_name = ?");
    $stmt->bind_param("s", $_POST['login_name']);
    $stmt->execute();
    $sql_result = $stmt->get_result();

    if ($sql_result->num_rows == 0) {
        # failed login, try again
        error_log("no such user " . $_POST['login_name']);
        $login_failure = True;
    } else {
        # user exists, but verify password before going further
        $row = $sql_result->fetch_assoc();
        if (password_verify($_POST['password'], $row['password_hash'])) {
            # password ok - user is who they claim to be!
            # unlock key and save to session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_key'] = KeyProtectedByPassword::loadFromAsciiSafeString(
                $row['encryption_key']
            )->unlockKey($_POST['password']);
            $_SESSION['friendly_name'] = Crypto::decrypt($row['friendly_name'], $_SESSION['user_key']);
            $_SESSION['authorised'] = True;
            header("Location: index.php");
            exit();
        } else {
            error_log("auth failure for user " . $_POST['login_name']);
            # boo, someone guessed the username but not the password
            $login_failure = True;
        }
    }
}
?>

<html>

<head>
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
</head>

<body>
    <div class="main_content">
        <h1> Login </h1>
        <hr>
        <?php
        if ($login_failure) {
            echo "<b>Login unsuccessful, please retry.</b>";
        }
        ?>
        <form action="login.php" method="POST">
            <div>Username:<input type="text" name="login_name" /></div>
            <div>Password:<input type="password" name="password" /></div>
            <div>
                <input type="submit" name="submitButton" value="Login" />
                <a href="index.php">Cancel</a>
            </div>
        </form>
    </div>
</body>

</html>