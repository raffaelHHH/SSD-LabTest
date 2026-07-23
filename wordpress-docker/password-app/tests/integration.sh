#!/usr/bin/env bash
# Integration tests: exercise the running passwordapp container together
# with its MySQL database over plain HTTP (the blocklist check and the
# created-user log both require a real DB round trip).
set -euo pipefail

BASE="${BASE_URL:-http://localhost:8081}"
JAR="$(mktemp)"
trap 'rm -f "$JAR"' EXIT

fail() {
    echo "FAIL: $1" >&2
    exit 1
}

echo "== common password is rejected =="
resp=$(curl -s -i -X POST "$BASE/register.php" -d "username=citest_common&password=password1")
echo "$resp" | grep -q "^Location: index.php?message=" || fail "expected redirect back to index.php"
echo "$resp" | grep -qi "commonly+used" || fail "expected common-password error message"

echo "== too-short password is rejected =="
resp=$(curl -s -i -X POST "$BASE/register.php" -d "username=citest_short&password=abc")
echo "$resp" | grep -qi "at+least+8+characters" || fail "expected minimum-length error message"

echo "== strong, uncommon password is accepted and logged =="
resp=$(curl -s -c "$JAR" -b "$JAR" -i -X POST "$BASE/register.php" -d "username=citest_ok&password=Zq7\$pLxRt2vM")
echo "$resp" | grep -q "^Location: welcome.php" || fail "expected redirect to welcome.php"
curl -s -c "$JAR" -b "$JAR" "$BASE/welcome.php" | grep -q "Welcome, citest_ok" || fail "expected welcome page for citest_ok"

echo "== login recognises the account just created =="
resp=$(curl -s -i -X POST "$BASE/login.php" -d "username=citest_ok&password=anything")
echo "$resp" | grep -qi "message=Welcome+back" || fail "expected login to recognise citest_ok"

echo "All integration tests passed."
