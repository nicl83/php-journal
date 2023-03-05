<?php
require __DIR__ . '/vendor/autoload.php';

use Defuse\Crypto\KeyProtectedByPassword;
use Defuse\Crypto\Crypto;

session_start();

$username_in_use = False;

if (!$_SESSION['authorised']) {
    # Not logged in, you can't post now, silly!
    header("Location: index.php");
}

if (
    isset($_POST['entry_title']) and
    isset($_POST['journal_entry'])
) {
    $db = new mysqli("localhost", "nick", "nickmysql", "journal_site");
    if ($db->connect_error) {
        die("Faatal backend error: " . $db->connect_error);
    }

    # encrypt entry
    $entry_title_enc = Crypto::encrypt($_POST["entry_title"], $_SESSION["user_key"]);
    $journal_entry_enc = Crypto::encrypt($_POST["journal_entry"], $_SESSION["user_key"]);

    $stmt = $db->prepare("INSERT INTO entries (entry_title, entry_text, entry_author_id) VALUES (?, ?, ?)");
    $stmt->bind_param(
        "ssi",
        $entry_title_enc,
        $journal_entry_enc,
        $_SESSION['user_id']
    );
    $post_ok = $stmt->execute();
    if ($post_ok) {
        header("Location: index.php");
    } else {
        die("Failed to add entry!");
    }

    exit();
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
    <h1>Write in your journal</h1>
    <form action="post.php" method="POST">
        <p>Post title:<input type="text" name="entry_title" /></p>
        <p>
            Write your entry here:<br>
            <textarea id="journal_entry" name="journal_entry" rows="24" cols="80"></textarea>
        </p>
        <input type="submit" name="submitButton" value="Save" />
        <a href="index.php">Cancel</a>
    </form>
</body>

</html>