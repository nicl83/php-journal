<?php
require __DIR__ . '/vendor/autoload.php';

use Defuse\Crypto\KeyProtectedByPassword;
use Defuse\Crypto\Crypto;

session_start();

$username_in_use = False;

if ($_SESSION['authorised']) {
    # Logged in, you don't need to register
    header("Location: index.php");
}

if (
    isset($_POST['login_name']) and
    isset($_POST['friendly_name']) and
    isset($_POST['password'])
) {
    $db = new mysqli("localhost", "nick", "nickmysql", "journal_site");
    if ($db->connect_error) {
        die("Faatal backend error: " . $db->connect_error);
    }

    $stmt = $db->prepare("SELECT login_name FROM users WHERE login_name = ?");
    if ($stmt == False) {
        die("SQL statement error when duplicate-checking");
    }
    $stmt->bind_param("s", $_POST["login_name"]);
    $stmt->execute();
    $sql_result = $stmt->get_result();

    if ($sql_result->num_rows > 0) {
        $username_in_use = True;
    } else {
        $user_key_raw = KeyProtectedByPassword::createRandomPasswordProtectedKey($_POST["password"]);
        $user_key = $user_key_raw->saveToAsciiSafeString();

        $user_pw = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        # encrypt user's chosen name for privacy
        $user_key_unlocked = $user_key_raw->unlockKey($_POST['password']);
        $friendly_name_encrypted = Crypto::encrypt($_POST["friendly_name"], $user_key_unlocked);

        $stmt = $db->prepare(
            "INSERT INTO pending_users (login_name, friendly_name, password_hash, encryption_key)
            VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssss",
            $_POST["login_name"],
            $friendly_name_encrypted,
            $user_pw,
            $user_key
        );
        $register_ok = $stmt->execute();
        if ($register_ok) {
            echo "Registration seems OK - please contact the admin to activate your account";
        } else {
            echo "Registration failed on server-side<br>";
            echo "Boring nerd stuff coming up:<br>";
            echo "Your key would be ".$user_key."<br>";
            echo "Your password hash would be ".$user_pw."<br>";
            echo "POST data: "; var_dump($_POST);
        }
        exit();
    }
}
?>

<html>

<head>
    <title>Registration</title>
</head>

<body>
    <?php
    if (!file_exists("/var/www/state/journal_registration_open")) {
        # Failsafe in case someone guesses the url - it's not hard
        echo "
        <p>sorry, registration is not open at this time...</p>
        </body>
        </html>
        ";
        exit();
    }
    ?>
    <h1>Register a new account</h1>
    <form action="register.php" method="POST">
        <p>Username:<input type="text" name="login_name" /></p>
        <p>Your name:<input type="text" name="friendly_name" /></p>
        <p>Password:<input type="password" name="password" /></p>
        <input type="submit" name="submitButton" value="register" />
    </form>
</body>

</html>