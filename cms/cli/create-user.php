<?php
/**
 * CLI: create or update a CMS admin/marketer user.
 *
 * Usage (from project root):
 *   php cms/cli/create-user.php <email> <password> [role] [name]
 *   role = marketer | reviewer | admin   (default: admin)
 *
 * Requires a working DB connection (set DB_PASS in .env).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script is CLI-only.\n");
}

require_once __DIR__ . '/../bootstrap.php';

$email = $argv[1] ?? '';
$password = $argv[2] ?? '';
$role = $argv[3] ?? 'admin';
$name = $argv[4] ?? '';

if ($email === '' || $password === '') {
    fwrite(STDERR, "Usage: php cms/cli/create-user.php <email> <password> [role] [name]\n");
    exit(1);
}
if (strlen($password) < 10) {
    fwrite(STDERR, "Password must be at least 10 characters.\n");
    exit(1);
}

$id = cms_upsert_user($email, $password, $role, $name);
if ($id === false) {
    fwrite(STDERR, "Failed to create user (check email format and DB connection).\n");
    exit(1);
}

fwrite(STDOUT, "User '{$email}' saved with role '{$role}'.\n");
exit(0);
