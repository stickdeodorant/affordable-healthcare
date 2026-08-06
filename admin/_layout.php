<?php
/**
 * Shared admin chrome (header/footer, flash, theme list). Not a page.
 * Included by admin entry points AFTER cms/bootstrap.php.
 */

if (!defined('CMS_ROOT')) {
    http_response_code(403);
    exit('Forbidden');
}

/**
 * The 11 themes that inc/header.php understands.
 */
function admin_theme_options(): array {
    return [
        'default', 'logo-match', 'ohio-healthplans', 'golden-trust', 'warm-orange',
        'modern-amber', 'coral-red', 'strong-red', 'burnt-orange', 'bright-healthcare', 'premium-copper',
    ];
}

function admin_flash_set(string $type, string $msg): void {
    $_SESSION['cms_flash'] = ['type' => $type, 'msg' => $msg];
}

function admin_flash_get(): ?array {
    if (empty($_SESSION['cms_flash'])) {
        return null;
    }
    $flash = $_SESSION['cms_flash'];
    unset($_SESSION['cms_flash']);
    return $flash;
}

function admin_status_badge(string $status): string {
    $status = strtolower($status);
    $label = ucfirst($status);
    return '<span class="badge badge-' . cms_e($status) . '">' . cms_e($label) . '</span>';
}

function admin_header(string $pageTitle): void {
    $user = cms_current_user();
    $base = CMS_ADMIN_PATH;
    $flash = admin_flash_get();
    ?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title><?= cms_e($pageTitle) ?> &middot; CMS Admin</title>
    <style>
        :root { --ah:#008A8C; --ah-dark:#002958; --ink:#1b2733; --muted:#6b7885; --line:#e2e8ef; --bg:#f5f7fa; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif; color:var(--ink); background:var(--bg); }
        a { color:var(--ah); text-decoration:none; }
        a:hover { text-decoration:underline; }
        .ah-top { display:flex; align-items:center; gap:1.25rem; padding:.75rem 1.25rem; background:var(--ah-dark); color:#fff; }
        .ah-top a { color:#dbe6ef; }
        .ah-top .brand { font-weight:700; color:#fff; font-size:1.05rem; }
        .ah-top nav a { margin-right:1rem; }
        .ah-top .spacer { flex:1; }
        .ah-top .who { color:#aebfd0; font-size:.85rem; }
        .ah-top .logout { color:#ffd7b0; }
        .ah-main { max-width:1080px; margin:1.5rem auto; padding:0 1.25rem 3rem; }
        h1 { font-size:1.4rem; margin:0 0 1rem; }
        .flash { padding:.75rem 1rem; border-radius:8px; margin-bottom:1rem; font-size:.9rem; }
        .flash.success { background:#e6f6ec; color:#1c6b3a; border:1px solid #b9e2c7; }
        .flash.error { background:#fdecec; color:#9b2226; border:1px solid #f4c2c2; }
        .flash.info { background:#eaf3fb; color:#1b4f7e; border:1px solid #c4dbf0; }
        .card { background:#fff; border:1px solid var(--line); border-radius:10px; padding:1.25rem; margin-bottom:1.25rem; }
        table { width:100%; border-collapse:collapse; background:#fff; border:1px solid var(--line); border-radius:10px; overflow:hidden; }
        th,td { text-align:left; padding:.65rem .85rem; border-bottom:1px solid var(--line); font-size:.9rem; vertical-align:middle; }
        th { background:#f0f4f8; font-size:.75rem; letter-spacing:.03em; text-transform:uppercase; color:var(--muted); }
        tr:last-child td { border-bottom:none; }
        .badge { display:inline-block; padding:.15rem .5rem; border-radius:20px; font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.02em; }
        .badge-published { background:#e6f6ec; color:#1c6b3a; }
        .badge-draft { background:#eef1f4; color:#5a6672; }
        .badge-review { background:#fff4e0; color:#8a5a00; }
        .badge-archived { background:#f3e8e8; color:#8a4a4a; }
        label { display:block; font-weight:600; font-size:.82rem; margin:.85rem 0 .3rem; color:#33414f; }
        input[type=text], input[type=email], input[type=password], input[type=url], select, textarea {
            width:100%; padding:.5rem .65rem; border:1px solid #cdd7e0; border-radius:7px; font-size:.9rem; font-family:inherit; background:#fff; }
        textarea { min-height:120px; resize:vertical; }
        input:focus, select:focus, textarea:focus { outline:none; border-color:var(--ah); box-shadow:0 0 0 3px rgba(0,138,140,.15); }
        .hint { font-size:.75rem; color:var(--muted); margin-top:.2rem; }
        .row { display:flex; gap:1rem; flex-wrap:wrap; }
        .row > .col { flex:1; min-width:220px; }
        .btn { display:inline-block; cursor:pointer; border:none; border-radius:7px; padding:.55rem 1rem; font-size:.88rem; font-weight:600; font-family:inherit; }
        .btn-primary { background:var(--ah); color:#fff; }
        .btn-primary:hover { background:#00777a; text-decoration:none; }
        .btn-ghost { background:#eef2f6; color:#33414f; }
        .btn-ghost:hover { background:#e2e8ee; text-decoration:none; }
        .btn-danger { background:#fbe9e9; color:#9b2226; }
        .btn-danger:hover { background:#f6d7d7; }
        .btn-sm { padding:.3rem .6rem; font-size:.78rem; }
        .actions { display:flex; gap:.4rem; align-items:center; flex-wrap:wrap; }
        .toolbar { display:flex; gap:.75rem; align-items:flex-end; flex-wrap:wrap; margin-bottom:1rem; }
        .inline-form { display:inline; margin:0; }
        .muted { color:var(--muted); font-size:.8rem; }
        .block-card { border:1px solid var(--line); border-radius:9px; padding:1rem; margin-bottom:.9rem; background:#fafcfe; }
        .block-card .block-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:.5rem; }
        .block-card .block-type { font-weight:700; font-size:.8rem; text-transform:uppercase; letter-spacing:.03em; color:var(--ah); }
        .faq-item { border:1px dashed #cdd7e0; border-radius:7px; padding:.6rem; margin-bottom:.6rem; }
        .login-wrap { max-width:380px; margin:6vh auto; }
        .login-wrap .card { padding:1.75rem; }
    </style>
</head>
<body>
    <header class="ah-top">
        <a class="brand" href="<?= cms_e($base) ?>/">AH&nbsp;CMS</a>
        <nav>
            <a href="<?= cms_e($base) ?>/">Pages</a>
            <a href="<?= cms_e($base) ?>/edit.php">New page</a>
            <a href="<?= cms_e($base) ?>/redirects.php">Redirects</a>
        </nav>
        <div class="spacer"></div>
        <?php if ($user): ?>
            <span class="who"><?= cms_e($user['name'] !== '' ? $user['name'] : $user['email']) ?> &middot; <?= cms_e($user['role']) ?></span>
            <a class="logout" href="<?= cms_e($base) ?>/logout.php">Log out</a>
        <?php endif; ?>
    </header>
    <div class="ah-main">
        <?php if ($flash): ?>
            <div class="flash <?= cms_e($flash['type']) ?>"><?= cms_e($flash['msg']) ?></div>
        <?php endif; ?>
<?php
}

function admin_footer(): void {
    ?>
    </div>
</body>
</html>
<?php
}
