<?php
session_start();
if (!isset($_SESSION['username'], $_SESSION['password'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];
$password = $_SESSION['password'];
unset($_SESSION['username'], $_SESSION['password']);
?>
<!doctype html>
<html>
<head><title>Welcome</title></head>
<body>
<h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>
<p>Your password: <?= htmlspecialchars($password) ?></p>
<a href="logout.php">Logout</a>
</body>
</html>
