<?php
session_start();
$message = $_GET['message'] ?? '';
?>
<!doctype html>
<html>
<head><title>Home - Login</title></head>
<body>
<h1>Login</h1>
<?php if ($message !== ''): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>
<form method="post" action="login.php">
    <label>Username: <input type="text" name="username" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button type="submit">Login</button>
</form>
<p><a href="register.php">Create an account</a></p>
</body>
</html>
