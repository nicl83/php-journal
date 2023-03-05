<?php
require __DIR__ . '/vendor/autoload.php';

use Defuse\Crypto\Crypto;

session_start();
?>
<html>

<head>
    <title> nicl's journal project </title>
</head>

<body>
    <h1>nicl's journal project</h1>
    <hr>
    <div>
        <?php # Show relevant buttons for if a user is logged in or out
        if ($_SESSION["authorised"]) {
            $friendly_name = $_SESSION['friendly_name'];
            echo "<p>hi, $friendly_name</p>";
            echo "
            <div>
            <a href='post.php'>Create new entry</a><br>
            <a href='logout.php'>Log out</a><br>
            </div>
            ";
        } else {
            echo "
            <a href='login.php'>Log in to existing account</a><br>
            ";
            if (file_exists("/var/www/state/journal_registration_open")) {
                echo "
                <a href='register.php'>Register a new account</a>
                ";
            }
        }
        ?>

        <?php # Show entries
        if ($_SESSION["authorised"]) {
            # try to login to database
            $db = new mysqli("localhost", "nick", "nickmysql", "journal_site");
            if ($db->connect_error) {
                die("Fatal backend error: " . $db->connect_error);
            }

            # fetch entries
            $stmt = $db->prepare("SELECT * FROM entries WHERE entry_author_id = ? ORDER BY entry_timestamp DESC");
            $stmt->bind_param("i", $_SESSION["user_id"]);
            $stmt->execute();
            $sql_result = $stmt->get_result();
            echo "<div><h2>Your journal entries</h2>";
            if ($sql_result->num_rows == 0) {
                echo "<p>you haven't written any entries yet...</p>";
            } else {
                $entries = $sql_result->fetch_all(MYSQLI_ASSOC);
                foreach ($entries as $entry) {
                    # prepare relevant data for creating an entry div
                    $friendly_name = $_SESSION['friendly_name'];
                    $entry_id = $entry['id'];
                    $entry_title = Crypto::decrypt($entry['entry_title'], $_SESSION['user_key']);
                    $entry_time = $entry['entry_timestamp'];
                    echo "
                    <div>
                    <a href='view-entry.php?entry=$entry_id'>$entry_title</a><br>
                    written by $friendly_name on $entry_time
                    </div>
                    ";
                }
            }
            echo "</div>";
        }
        ?>
    </div>
</body>