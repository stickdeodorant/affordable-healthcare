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
        :root {
            --ah:#0d7d6e;
            --ah-strong:#0a6153;
            --ah-dark:#0b1220;
            --ah-soft:#e7f3f0;
            --ink:#18212e;
            --ink-soft:#586675;
            --ink-faint:#8a97a5;
            --line:#e7eaef;
            --line-strong:#d8dde4;
            --panel:#ffffff;
            --bg:#f4f6f9;
            --danger:#c0433f;
            --warn:#b1791c;
            --ok:#1f7a53;
            --gold:#c39a4d;
            --gold-soft:#f6efdf;
            --shadow-sm:0 1px 2px rgba(16,26,42,.06);
            --shadow:0 8px 24px rgba(16,26,42,.07);
            --shadow-lg:0 20px 48px rgba(16,26,42,.14);
            --radius:14px;
            --radius-sm:10px;
            --radius-lg:18px;
        }
        * { box-sizing:border-box; }
        html, body { min-height:100%; }
        body {
            margin:0;
            font-family:"Avenir Next","Nunito Sans","Segoe UI",system-ui,sans-serif;
            color:var(--ink);
            font-size:15px;
            line-height:1.5;
            -webkit-font-smoothing:antialiased;
            text-rendering:optimizeLegibility;
            background:
                radial-gradient(820px 340px at 100% -6%, rgba(13,125,110,.06), transparent 60%),
                var(--bg);
        }
        a { color:var(--ah); text-decoration:none; }
        a:hover { color:var(--ah-strong); text-decoration:underline; }
        .cms-shell {
            display:grid;
            grid-template-columns: 280px minmax(0, 1fr);
            min-height:100vh;
        }
        .cms-sidebar {
            background:linear-gradient(190deg, #0b1220 0%, #0e1626 60%, #101c30 100%);
            border-right:1px solid rgba(255,255,255,.06);
            color:#c7d2df;
            padding:1.15rem 1rem;
            position:sticky;
            top:0;
            height:100vh;
            overflow-y:auto;
            display:flex;
            flex-direction:column;
        }
        .cms-brand {
            font-family:"Avenir Next Demi Bold","Montserrat","Segoe UI",sans-serif;
            letter-spacing:.01em;
            font-weight:700;
            color:#fff;
            font-size:1rem;
            margin-bottom:.35rem;
            display:inline-flex;
            align-items:center;
            gap:.55rem;
            padding:.15rem .1rem;
        }
        .cms-brand:hover { text-decoration:none; }
        .cms-brand .brand-mark {
            width:30px;
            height:30px;
            border-radius:9px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            background:linear-gradient(140deg, var(--ah) 0%, #14b39b 100%);
            color:#fff;
            box-shadow:0 6px 16px rgba(13,125,110,.4);
            font-size:.95rem;
        }
        .cms-sidebar .who {
            display:flex;
            align-items:center;
            gap:.6rem;
            color:#9fb0c2;
            font-size:.8rem;
            margin:0 0 1.1rem;
            padding:.6rem .65rem;
            line-height:1.35;
            background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.06);
            border-radius:12px;
        }
        .cms-sidebar .who .who-avatar {
            width:30px; height:30px; flex:0 0 30px;
            border-radius:50%;
            background:linear-gradient(140deg,#26364b,#3a4f6b);
            color:#e6eefa;
            display:inline-flex; align-items:center; justify-content:center;
            font-weight:700; font-size:.82rem;
        }
        .cms-sidebar .who .who-role { color:#8496a8; text-transform:capitalize; }
        .cms-nav-title {
            color:#67788c;
            font-size:.68rem;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:.09em;
            margin:1.15rem .55rem .4rem;
        }
        .cms-nav {
            display:flex;
            flex-direction:column;
            gap:.16rem;
        }
        .cms-nav a {
            display:flex;
            align-items:center;
            gap:.62rem;
            font-size:.9rem;
            font-weight:600;
            padding:.58rem .7rem;
            border-radius:10px;
            color:#c1cddb;
            border:1px solid transparent;
            transition:background .16s ease, color .16s ease;
        }
        .cms-nav a svg { width:17px; height:17px; flex:0 0 17px; opacity:.75; }
        .cms-nav a:hover {
            text-decoration:none;
            background:rgba(255,255,255,.06);
            color:#fff;
        }
        .cms-nav a:hover svg { opacity:1; }
        .cms-nav a.active {
            background:linear-gradient(135deg, rgba(13,125,110,.9) 0%, rgba(16,140,122,.72) 100%);
            color:#fff;
            box-shadow:0 6px 16px rgba(13,125,110,.32);
        }
        .cms-nav a.active svg { opacity:1; }
        .cms-nav-cta {
            margin:.3rem 0 .2rem;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:.45rem;
            padding:.6rem .7rem;
            border-radius:10px;
            font-weight:700;
            font-size:.88rem;
            color:#fff;
            background:linear-gradient(135deg, var(--ah) 0%, #12a58f 100%);
            box-shadow:0 8px 18px rgba(13,125,110,.35);
        }
        .cms-nav-cta:hover { text-decoration:none; color:#fff; filter:brightness(1.05); }
        .cms-logout {
            margin-top:auto;
            display:inline-flex;
            align-items:center;
            gap:.45rem;
            color:#93a3b5;
            font-weight:600;
            font-size:.82rem;
            padding-top:1.1rem;
        }
        .cms-logout:hover { color:#fff; text-decoration:none; }
        .cms-workspace {
            min-width:0;
            display:flex;
            flex-direction:column;
        }
        .cms-topbar {
            position:sticky;
            top:0;
            z-index:10;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:.9rem;
            padding:.85rem 1.6rem;
            background:rgba(255,255,255,.82);
            backdrop-filter: blur(12px);
            border-bottom:1px solid var(--line);
        }
        .cms-topbar .page-kicker {
            color:var(--ink-faint);
            font-size:.72rem;
            font-weight:700;
            letter-spacing:.08em;
            text-transform:uppercase;
            margin-bottom:.12rem;
        }
        .cms-topbar .page-title {
            font-family:"Avenir Next Demi Bold","Montserrat","Segoe UI",sans-serif;
            color:var(--ink);
            font-size:1.02rem;
            font-weight:700;
        }
        .ah-main { max-width:1240px; width:100%; margin:1.6rem auto; padding:0 1.6rem 3.5rem; }
        h1 {
            font-family:"Avenir Next Demi Bold","Montserrat","Segoe UI",sans-serif;
            letter-spacing:-.01em;
            font-size:1.55rem;
            margin:0 0 1.15rem;
            color:var(--ink);
        }
        .flash {
            padding:.82rem 1rem;
            border-radius:12px;
            margin-bottom:1rem;
            font-size:.9rem;
            border:1px solid transparent;
            box-shadow:var(--shadow);
        }
        .flash.success { background:#e9f8f1; color:#1f6f4b; border-color:#b7e7cd; }
        .flash.error { background:#fff0f1; color:#9d2b34; border-color:#f3c4c8; }
        .flash.info { background:#eff6fb; color:#244d71; border-color:#c8d9e8; }
        .card {
            background:var(--panel);
            border:1px solid var(--line);
            border-radius:var(--radius);
            padding:1.35rem 1.4rem;
            margin-bottom:1.25rem;
            box-shadow:var(--shadow-sm);
        }
        .card h2 { color:var(--ink); }
        table {
            width:100%;
            border-collapse:separate;
            border-spacing:0;
            background:#fff;
            border:1px solid var(--line);
            border-radius:var(--radius);
            overflow:hidden;
            box-shadow:var(--shadow-sm);
        }
        th,td {
            text-align:left;
            padding:.82rem 1rem;
            border-bottom:1px solid var(--line);
            font-size:.9rem;
            vertical-align:middle;
        }
        th {
            background:#fbfcfe;
            font-size:.7rem;
            font-weight:700;
            letter-spacing:.06em;
            text-transform:uppercase;
            color:var(--ink-faint);
        }
        tr:last-child td { border-bottom:none; }
        tbody tr { transition:background .12s ease; }
        tbody tr:hover td { background:#f7fafc; }
        .diff-changed td { background:#f0fbf8 !important; }
        .badge {
            display:inline-flex;
            align-items:center;
            gap:.32rem;
            padding:.24rem .6rem;
            border-radius:999px;
            font-size:.71rem;
            font-weight:700;
            letter-spacing:.02em;
            border:1px solid transparent;
        }
        .badge::before { content:""; width:6px; height:6px; border-radius:50%; background:currentColor; opacity:.7; }
        .badge-published { background:#e8f6ee; color:#1c6e46; border-color:#c6ebd4; }
        .badge-draft { background:#eef1f5; color:#566472; border-color:#dde3ea; }
        .badge-review { background:#fdf3df; color:#8a6112; border-color:#f2e0b4; }
        .badge-archived { background:#f6ebec; color:#7c4449; border-color:#eacfd1; }
        .badge-risk-high { background:#fdecec; color:#a1332f; border-color:#f2cccb; }
        .badge-risk-medium { background:#fdf3df; color:#8a6112; border-color:#f2e0b4; }
        .badge-risk-low { background:#e8f6ef; color:#1f6e53; border-color:#c9ecd7; }
        label { display:block; font-weight:700; font-size:.8rem; margin:.86rem 0 .3rem; color:#284155; }
        input[type=text], input[type=email], input[type=password], input[type=url], input[type=number], select, textarea {
            width:100%;
            padding:.6rem .72rem;
            border:1px solid var(--line-strong);
            border-radius:var(--radius-sm);
            font-size:.9rem;
            font-family:inherit;
            color:var(--ink);
            background:#fff;
            transition:border-color .16s ease, box-shadow .16s ease;
        }
        input::placeholder, textarea::placeholder { color:#a9b4c0; }
        textarea { min-height:120px; resize:vertical; }
        input:hover, select:hover, textarea:hover { border-color:#c2ccd6; }
        input:focus, select:focus, textarea:focus {
            outline:none;
            border-color:var(--ah);
            box-shadow:0 0 0 3px rgba(13,125,110,.16);
        }
        .hint { font-size:.78rem; color:var(--ink-soft); margin-top:.28rem; }
        .row { display:flex; gap:1rem; flex-wrap:wrap; }
        .row > .col { flex:1; min-width:220px; }
        .btn {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:.4rem;
            cursor:pointer;
            border:1px solid transparent;
            border-radius:var(--radius-sm);
            padding:.55rem 1rem;
            font-size:.86rem;
            font-weight:700;
            font-family:inherit;
            line-height:1.1;
            transition:transform .1s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease, color .18s ease;
        }
        .btn:hover { text-decoration:none; }
        .btn:focus-visible { outline:none; box-shadow:0 0 0 3px rgba(13,125,110,.3); }
        .btn:active { transform:translateY(1px); }
        .btn-primary {
            background:linear-gradient(135deg, var(--ah) 0%, var(--ah-strong) 100%);
            color:#fff;
            box-shadow:0 6px 16px rgba(13,125,110,.26);
        }
        .btn-primary:hover { filter:brightness(1.06); box-shadow:0 8px 20px rgba(13,125,110,.32); }
        .btn-ghost {
            background:#fff;
            color:var(--ink);
            border-color:var(--line-strong);
        }
        .btn-ghost:hover { background:#f5f7fa; border-color:#c6cfd9; }
        .btn-danger {
            background:#fff;
            color:var(--danger);
            border-color:#eccacb;
        }
        .btn-danger:hover { background:#fdf0f0; border-color:#e2b3b4; }
        .btn-sm { padding:.36rem .66rem; font-size:.78rem; border-radius:8px; }
        .actions { display:flex; gap:.4rem; align-items:center; flex-wrap:wrap; }
        .toolbar {
            display:flex;
            gap:.85rem;
            align-items:flex-end;
            flex-wrap:wrap;
            margin-bottom:1rem;
            padding:1rem 1.1rem;
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#fff;
            box-shadow:var(--shadow-sm);
        }
        .inline-form { display:inline; margin:0; }
        .muted { color:var(--ink-soft); font-size:.8rem; }
        .stat-grid {
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(150px, 1fr));
            gap:.85rem;
        }
        .stat-card {
            border:1px solid var(--line);
            border-radius:var(--radius-sm);
            padding:.9rem 1rem;
            background:#fff;
        }
        .stat-card .stat-label {
            font-size:.74rem;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:.05em;
            color:var(--ink-faint);
            display:flex; align-items:center; gap:.4rem;
        }
        .stat-card .stat-value {
            font-size:1.7rem;
            font-weight:800;
            color:var(--ink);
            line-height:1.1;
            margin-top:.25rem;
        }
        .stat-card .stat-dot { width:9px; height:9px; border-radius:50%; display:inline-block; }
        .dot-draft { background:#8a97a5; }
        .dot-review { background:var(--warn); }
        .dot-published { background:var(--ok); }
        .dot-archived { background:#b06a6f; }
        .section-head {
            display:flex;
            align-items:flex-start;
            gap:.85rem;
            margin-bottom:1rem;
        }
        .section-head .section-icon {
            width:38px; height:38px; flex:0 0 38px;
            border-radius:11px;
            display:inline-flex; align-items:center; justify-content:center;
            background:var(--ah-soft);
            color:var(--ah-strong);
        }
        .section-head .section-icon svg { width:19px; height:19px; }
        .section-head .section-title {
            font-family:"Avenir Next Demi Bold","Montserrat","Segoe UI",sans-serif;
            font-size:1.06rem;
            font-weight:700;
            color:var(--ink);
            margin:0;
            line-height:1.25;
        }
        .section-head .section-desc {
            font-size:.84rem;
            color:var(--ink-soft);
            margin:.2rem 0 0;
        }
        .segmented {
            display:inline-flex;
            padding:3px;
            gap:2px;
            background:#eef1f5;
            border:1px solid var(--line);
            border-radius:999px;
        }
        .segmented label {
            margin:0;
            display:inline-flex; align-items:center; gap:.4rem;
            font-size:.82rem; font-weight:600;
            color:var(--ink-soft);
            padding:.4rem .85rem;
            border-radius:999px;
            cursor:pointer;
            transition:background .15s ease, color .15s ease;
        }
        .segmented label:hover { color:var(--ink); }
        .segmented input { position:absolute; opacity:0; width:0; height:0; }
        .segmented label:has(input:checked) {
            background:#fff;
            color:var(--ah-strong);
            box-shadow:var(--shadow-sm);
        }
        .block-card {
            border:1px solid #d4dfe9;
            border-radius:12px;
            padding:1rem;
            margin-bottom:.9rem;
            background:linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
        }
        .block-card .block-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:.5rem; }
        .block-card .block-type {
            font-weight:800;
            font-size:.79rem;
            text-transform:uppercase;
            letter-spacing:.04em;
            color:#235d78;
        }
        .faq-item { border:1px dashed #c8d8e8; border-radius:9px; padding:.62rem; margin-bottom:.6rem; }
        .login-wrap { max-width:420px; margin:7vh auto; }
        .login-wrap .card { padding:1.8rem; }
        code {
            background:#f0f5fa;
            border:1px solid #dbe6f1;
            border-radius:6px;
            padding:.08rem .35rem;
            color:#27455b;
            font-family:"Cascadia Mono","Consolas",monospace;
            font-size:.82rem;
        }
        @media (max-width: 980px) {
            .cms-shell { grid-template-columns: 1fr; }
            .cms-sidebar {
                position:static;
                height:auto;
                border-right:none;
                border-bottom:1px solid rgba(255,255,255,.16);
            }
            .cms-topbar {
                position:static;
            }
            .ah-main { margin:1rem auto; padding:0 .8rem 2rem; }
            table, thead, tbody, tr, th, td { font-size:.84rem; }
            .toolbar { padding:.75rem; }
        }
    </style>
</head>
<body>
    <?php $isAuth = !$user; ?>
    <?php if ($isAuth): ?>
        <div class="ah-main">
            <?php if ($flash): ?>
                <div class="flash <?= cms_e($flash['type']) ?>"><?= cms_e($flash['msg']) ?></div>
            <?php endif; ?>
    <?php else: ?>
        <?php
            $self = strtolower((string)basename((string)($_SERVER['PHP_SELF'] ?? '')));
            $active = [
                'pages' => ($self === 'index.php'),
                'compose' => ($self === 'edit.php'),
                'redirects' => ($self === 'redirects.php'),
                'snippets' => ($self === 'snippets.php'),
                'guide' => ($self === 'guide.php'),
            ];
        ?>
        <?php
            $displayName = $user['name'] !== '' ? $user['name'] : $user['email'];
            $initials = strtoupper(mb_substr(trim($displayName), 0, 1));
            if ($initials === '') { $initials = 'U'; }
        ?>
        <div class="cms-shell">
            <aside class="cms-sidebar">
                <a class="cms-brand" href="<?= cms_e($base) ?>/">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-4.35-9.5-8.5C1 10 2 6.5 5.5 6.5c2 0 3 1.2 3.5 2 .5-.8 1.5-2 3.5-2C16 6.5 17 10 15.5 12.5 13 16.65 12 21 12 21z"/></svg>
                    </span>
                    <span>Healthcare CMS</span>
                </a>
                <div class="who">
                    <span class="who-avatar" aria-hidden="true"><?= cms_e($initials) ?></span>
                    <span>
                        <?= cms_e($displayName) ?><br>
                        <span class="who-role"><?= cms_e($user['role']) ?></span>
                    </span>
                </div>

                <a class="cms-nav-cta" href="<?= cms_e($base) ?>/edit.php">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    New page
                </a>

                <div class="cms-nav-title">Content</div>
                <nav class="cms-nav">
                    <a class="<?= $active['pages'] ? 'active' : '' ?>" href="<?= cms_e($base) ?>/">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z"/><path d="M4 9h16"/><path d="M9 9v11"/></svg>
                        All pages
                    </a>
                    <a class="<?= $active['compose'] ? 'active' : '' ?>" href="<?= cms_e($base) ?>/edit.php">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                        Page editor
                    </a>
                </nav>

                <div class="cms-nav-title">Configuration</div>
                <nav class="cms-nav">
                    <a class="<?= $active['redirects'] ? 'active' : '' ?>" href="<?= cms_e($base) ?>/redirects.php">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h13l-3-3"/><path d="M20 17H7l3 3"/></svg>
                        Redirects
                    </a>
                    <?php if (cms_user_can('admin')): ?>
                        <a class="<?= $active['snippets'] ? 'active' : '' ?>" href="<?= cms_e($base) ?>/snippets.php">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                            Section library
                        </a>
                    <?php endif; ?>
                </nav>

                <div class="cms-nav-title">Help</div>
                <nav class="cms-nav">
                    <a class="<?= $active['guide'] ? 'active' : '' ?>" href="<?= cms_e($base) ?>/guide.php">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 2.5-3 4"/><line x1="12" y1="17" x2="12" y2="17"/></svg>
                        User guide
                    </a>
                </nav>
                <a class="cms-logout" href="<?= cms_e($base) ?>/logout.php">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Log out
                </a>
            </aside>

            <section class="cms-workspace">
                <header class="cms-topbar">
                    <div>
                        <div class="page-kicker">Content Management</div>
                        <div class="page-title"><?= cms_e($pageTitle) ?></div>
                    </div>
                </header>
                <div class="ah-main">
                    <?php if ($flash): ?>
                        <div class="flash <?= cms_e($flash['type']) ?>"><?= cms_e($flash['msg']) ?></div>
                    <?php endif; ?>
    <?php endif; ?>
<?php
}

function admin_footer(): void {
    ?>
    <?php if (cms_is_logged_in()): ?>
                </div>
            </section>
        </div>
    <?php else: ?>
        </div>
    <?php endif; ?>
</body>
</html>
<?php
}
