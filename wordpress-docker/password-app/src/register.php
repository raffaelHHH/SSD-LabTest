<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/password_policy.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $pdo = getDb();

    $errors = passwordErrors($pdo, $password);
    if ($username === '') {
        $errors[] = 'Username is required.';
    }

    if ($errors) {
        header('Location: index.php?message=' . urlencode(implode(' ', $errors)));
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO `2402294` (username, created_at) VALUES (?, NOW())');
    $stmt->execute([$username]);

    $_SESSION['username'] = $username;
    $_SESSION['password'] = $password;
    header('Location: welcome.php');
    exit;
}
?>
<!doctype html>
<html>
<head>
    <title>Create Account</title>
    <script src="assets/password-check.js" defer></script>
</head>
<body>
<h1>Create Account</h1>
<form id="registerForm" method="post" action="register.php">
    <label>Username: <input type="text" id="username" name="username" required></label><br>
    <label>Password: <input type="password" id="password" name="password" required></label><br>
    <p id="clientError"></p>
    <button type="submit">Create Account</button>
</form>
<p><a href="index.php">Back to home</a></p>
</body>
</html>
