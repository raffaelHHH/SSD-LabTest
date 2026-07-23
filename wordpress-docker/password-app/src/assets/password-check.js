// Client-side password check per OWASP Proactive Controls C7, Level 1
// Passwords: length range only. This is a UX convenience check -
// the backend re-checks length AND the common-password blocklist,
// since client-side checks can always be bypassed.
function isPasswordValid(password) {
  return password.length >= 8 && password.length <= 64;
}

document.getElementById('registerForm')?.addEventListener('submit', function (event) {
  const password = document.getElementById('password').value;
  const errorEl = document.getElementById('clientError');

  if (!isPasswordValid(password)) {
    event.preventDefault();
    errorEl.textContent = 'Password must be between 8 and 64 characters long.';
  }
});
