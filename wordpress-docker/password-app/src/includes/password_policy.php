<?php

/**
 * Backend password check per OWASP Proactive Controls 2024, C7 (Secure
 * Digital Identities) > Level 1 Passwords: enforce a length range and
 * reject commonly-used/breached passwords. Deliberately no composition
 * rules (no forced mix of upper/lower/digit/symbol) since OWASP advises
 * against them. This is the authoritative check; the client-side check
 * is UX-only and must never be trusted on its own.
 */
function passwordErrors(PDO $pdo, string $password): array
{
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if (strlen($password) > 64) {
        $errors[] = 'Password must be at most 64 characters long.';
    }

    $stmt = $pdo->prepare('SELECT 1 FROM common_passwords WHERE password = ? LIMIT 1');
    $stmt->execute([$password]);
    if ($stmt->fetchColumn()) {
        $errors[] = 'That password is one of the most commonly used passwords. Please choose a different one.';
    }

    return $errors;
}
