<?php
require __DIR__ . '/includes/db.php';
session_start();

$username = trim($_POST['username'] ?? '');

// Requirement 9 explicitly does not store passwords, so there is nothing
// to check a password against here - this only confirms the account
// exists. Full authentication would require a separately-scoped,
// securely-hashed credential store, which is out of scope for this app.
$stmt = getDb()->prepare('SELECT 1 FROM `2402294` WHERE username = ? LIMIT 1');
$stmt->execute([$username]);

$message = $stmt->fetchColumn()
    ? "Welcome back, $username."
    : 'Unknown username.';

header('Location: index.php?message=' . urlencode($message));
exit;
