<?php
require __DIR__ . '/vendor/autoload.php';

use Defuse\Crypto\Crypto;

session_start();

if (!$_SESSION['authorised']) {
    # You need to be logged in to read posts
    header("Location: index.php");
    exit();
}

# try to login to database
$db = new mysqli("localhost", "nick", "nickmysql", "journal_site");
if ($db->connect_error) {
    die("Fatal backend error: " . $db->connect_error);
}

# fetch entry from database
$stmt = $db->prepare("SELECT * FROM entries WHERE id = ? ORDER BY entry_timestamp DESC");
$stmt->bind_param('i', $_GET['entry']);
$stmt->execute();
$entry_data = $stmt->get_result()->fetch_assoc();

if ($entry_data['entry_author_id'] != $_SESSION['user_id']) {
    # Snooping on other people's journal entries is not ok!
    header("Location: index.php");
    exit();
}

# decrypt entry data
$entry_title = Crypto::decrypt($entry_data['entry_title'], $_SESSION['user_key']);
$entry_text = Crypto::decrypt($entry_data['entry_text'], $_SESSION['user_key']);
?>

<html>

<head>
    <title>Journal - <?php echo $entry_title; ?></title>
</head>

<body>
    <h1><?php echo $entry_title; ?></h1>
    <a href="index.php">Back to index</a>
    <hr>
    <p>
        <?php echo $entry_text; ?>
    </p>
</body>

</html>