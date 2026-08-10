<?php
/**
 * Create / edit a CMS page: page fields, theme, status, and a structured
 * block editor. Saves a revision snapshot and auto-creates a redirect on
 * slug change.
 */

require_once __DIR__ . '/../cms/bootstrap.php';
require_once __DIR__ . '/_layout.php';

cms_require_login();

$base = CMS_ADMIN_PATH;

// Experiment registry (for per-page default variant controls). Load this BEFORE
// assigning $id: experiments.php runs file-scope `foreach (... as $id => $cfg)`
// loops that would otherwise clobber this editor's $id.
require_once CMS_APP_ROOT . '/inc/experiments.php';
$ahExperimentRegistry = isset($GLOBALS['ah_experiments']) && is_array($GLOBALS['ah_experiments']) ? $GLOBALS['ah_experiments'] : [];

$id = (int)($_GET['id'] ?? 0);
$errors = [];

/**
 * Keep only registered experiment ids whose chosen variant is a valid, non-default
 * option; returns a compact JSON string (or '' when nothing is overridden).
 */
function admin_clean_experiment_defaults($raw): string {
    $registry = isset($GLOBALS['ah_experiments']) && is_array($GLOBALS['ah_experiments']) ? $GLOBALS['ah_experiments'] : [];
    $out = [];
    if (is_array($raw)) {
        foreach ($raw as $id => $variant) {
            if (!isset($registry[$id])) {
                continue;
            }
            $variant = (string)$variant;
            $cfg = $registry[$id];
            if ($variant === '' || $variant === (string)($cfg['default'] ?? '')) {
                continue;
            }
            if (isset($cfg['options'][$variant])) {
                $out[$id] = $variant;
            }
        }
    }
    return $out ? (string)json_encode($out, JSON_UNESCAPED_SLASHES) : '';
}

/**
 * Validate/sanitize marketer-submitted blocks down to the renderer's contract.
 */
function admin_clean_blocks(array $raw): array {
    $clean = [];
    foreach ($raw as $b) {
        if (!is_array($b) || empty($b['type'])) {
            continue;
        }
        switch ($b['type']) {
            case 'rich_text':
                $html = cms_sanitize_html((string)($b['html'] ?? ''));
                if ($html !== '') {
                    $clean[] = ['type' => 'rich_text', 'html' => $html];
                }
                break;
            case 'legacy_html':
                $html = cms_sanitize_legacy_html((string)($b['html'] ?? ''));
                if ($html !== '') {
                    $clean[] = ['type' => 'legacy_html', 'html' => $html];
                }
                break;
            case 'snapshot_html':
                $html = (string)($b['html'] ?? '');
                if (trim($html) !== '') {
                    $clean[] = ['type' => 'snapshot_html', 'html' => $html];
                }
                break;
            case 'cta_banner':
                $href = trim((string)($b['href'] ?? ''));
                if (!cms_url_is_safe($href)) {
                    $href = '';
                }
                $clean[] = [
                    'type'     => 'cta_banner',
                    'text'     => trim((string)($b['text'] ?? '')),
                    'href'     => $href,
                    'cta_text' => trim((string)($b['cta_text'] ?? '')),
                ];
                break;
            case 'image':
                $src = trim((string)($b['src'] ?? ''));
                if ($src !== '' && preg_match('#^/img/[A-Za-z0-9_\-/.]+\.(jpg|jpeg|png|webp|gif|svg)$#i', $src)) {
                    $clean[] = ['type' => 'image', 'src' => $src, 'alt' => trim((string)($b['alt'] ?? ''))];
                }
                break;
            case 'faq_list':
                $items = [];
                foreach (($b['items'] ?? []) as $it) {
                    if (!is_array($it)) {
                        continue;
                    }
                    $qq = trim((string)($it['q'] ?? ''));
                    $aa = cms_sanitize_html((string)($it['a'] ?? ''));
                    if ($qq !== '' && $aa !== '') {
                        $items[] = ['q' => $qq, 'a' => $aa];
                    }
                }
                if ($items) {
                    $clean[] = ['type' => 'faq_list', 'heading' => trim((string)($b['heading'] ?? '')), 'items' => $items];
                }
                break;
            case 'legacy_section':
                $sectionKey = trim((string)($b['section_key'] ?? ''));
                if ($sectionKey !== '' && cms_legacy_section_exists($sectionKey)) {
                    $clean[] = [
                        'type' => 'legacy_section',
                        'section_key' => $sectionKey,
                    ];
                }
                break;
        }
    }
    return $clean;
}

/**
 * Critical readiness checks for publishing. These protect non-technical users
 * from publishing obviously incomplete pages.
 */
function admin_publish_checks(array $form, array $blocks): array {
    $errors = [];

    if (trim((string)($form['title'] ?? '')) === '') {
        $errors[] = 'Publish check: add a page title.';
    }
    if (trim((string)($form['hero_headline'] ?? '')) === '') {
        $errors[] = 'Publish check: add a hero headline.';
    }
    if (trim((string)($form['cta_text'] ?? '')) === '') {
        $errors[] = 'Publish check: add default CTA text.';
    }
    if (trim((string)($form['cta_href'] ?? '')) === '') {
        $errors[] = 'Publish check: add a default CTA link.';
    }
    if (count($blocks) === 0) {
        $errors[] = 'Publish check: add at least one content block.';
    }

    return $errors;
}

function admin_block_type_counts(array $blocks): array {
    $counts = [];
    foreach ($blocks as $block) {
        if (!is_array($block)) {
            continue;
        }
        $type = trim((string)($block['type'] ?? 'unknown'));
        if ($type === '') {
            $type = 'unknown';
        }
        $counts[$type] = ($counts[$type] ?? 0) + 1;
    }
    ksort($counts);
    return $counts;
}

function admin_format_block_counts(array $counts): string {
    if (!$counts) {
        return 'none';
    }
    $parts = [];
    foreach ($counts as $type => $count) {
        $parts[] = $type . ': ' . (int)$count;
    }
    return implode(', ', $parts);
}

function admin_build_change_summary(?array $existing, array $form, array $cleanBlocks): string {
    $lines = [];
    if (!$existing) {
        $lines[] = 'Created new page.';
    }

    $fieldLabels = [
        'title' => 'Title',
        'hero_headline' => 'Hero headline',
        'hero_subtitle' => 'Hero subtitle',
        'cta_text' => 'CTA text',
        'cta_href' => 'CTA link',
        'meta_description' => 'Meta description',
        'canonical' => 'Canonical URL',
        'theme' => 'Theme',
        'template' => 'Template',
        'status' => 'Status',
    ];

    foreach ($fieldLabels as $key => $label) {
        $oldVal = trim((string)($existing[$key] ?? ''));
        $newVal = trim((string)($form[$key] ?? ''));
        if ($oldVal !== $newVal) {
            $lines[] = $label . ' updated.';
        }
    }

    $oldBlocks = $existing ? cms_json_decode((string)($existing['body_json'] ?? '[]')) : [];
    $oldCounts = admin_block_type_counts(is_array($oldBlocks) ? $oldBlocks : []);
    $newCounts = admin_block_type_counts($cleanBlocks);
    if ($oldCounts !== $newCounts) {
        $lines[] = 'Section stack changed from [' . admin_format_block_counts($oldCounts) . '] to [' . admin_format_block_counts($newCounts) . '].';
    }

    if (!$lines) {
        $lines[] = 'No major content deltas detected.';
    }

    return implode("\n", $lines);
}

/**
 * A physical .php file that only forwards to the CMS (bridge stub) is not a real
 * collision: visiting its URL renders the CMS page, so it is safe to edit.
 */
function admin_file_is_bridge_stub(string $path): bool {
    $src = @file_get_contents($path);
    if ($src === false) {
        return false;
    }
    $normalized = preg_replace('/\s+/', ' ', trim($src));
    return $normalized === "<?php require __DIR__ . '/inc/cms-page.php';"
        || $normalized === "<?php require __DIR__ . '/inc/cms-page-bridge.php';";
}

// Load existing page (edit mode).
$page = null;
if ($id > 0) {
    $page = cms_select_one('SELECT * FROM cms_pages WHERE id = ? LIMIT 1', 'i', [$id]);
    if (!$page) {
        admin_flash_set('error', 'Page not found.');
        header('Location: ' . $base . '/');
        exit;
    }
}

// Default form model.
$form = [
    'slug' => $page['slug'] ?? '',
    'title' => $page['title'] ?? '',
    'meta_description' => $page['meta_description'] ?? '',
    'canonical' => $page['canonical'] ?? '',
    'og_image' => $page['og_image'] ?? '',
    'template' => $page['template'] ?? 'default',
    'theme' => $page['theme'] ?? 'default',
    'status' => $page['status'] ?? 'draft',
    'hero_headline' => $page['hero_headline'] ?? '',
    'hero_subtitle' => $page['hero_subtitle'] ?? '',
    'cta_text' => $page['cta_text'] ?? '',
    'cta_href' => $page['cta_href'] ?? '',
    'experiment_defaults' => $page['experiment_defaults'] ?? '',
    'handoff_note' => '',
    'reviewer_note' => '',
    'body_json' => $page['body_json'] ?? '[]',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_csrf_require();
    $submitIntent = (string)($_POST['submit_intent'] ?? 'save');

    $form['title'] = trim((string)($_POST['title'] ?? ''));
    $form['slug'] = cms_slug((string)($_POST['slug'] ?? ''));
    if ($form['slug'] === '' && $form['title'] !== '') {
        $form['slug'] = cms_slug($form['title']);
    }
    $form['meta_description'] = mb_substr(trim((string)($_POST['meta_description'] ?? '')), 0, 320);
    $form['canonical'] = trim((string)($_POST['canonical'] ?? ''));
    $form['og_image'] = trim((string)($_POST['og_image'] ?? ''));
    $form['template'] = in_array((string)($_POST['template'] ?? ''), array_keys($GLOBALS['CMS_PAGE_TEMPLATES']), true) ? (string)$_POST['template'] : 'default';
    $form['theme'] = in_array((string)($_POST['theme'] ?? ''), admin_theme_options(), true) ? (string)$_POST['theme'] : 'default';
    $form['status'] = in_array((string)($_POST['status'] ?? ''), $GLOBALS['CMS_STATUSES'], true) ? (string)$_POST['status'] : 'draft';
    if ($submitIntent === 'submit_review') {
        $form['status'] = 'review';
    }
    if (!cms_user_can('reviewer') && $form['status'] === 'published') {
        $form['status'] = ($page && ($page['status'] ?? '') === 'published') ? 'review' : 'draft';
    }
    $form['hero_headline'] = trim((string)($_POST['hero_headline'] ?? ''));
    $form['hero_subtitle'] = trim((string)($_POST['hero_subtitle'] ?? ''));
    $form['cta_text'] = trim((string)($_POST['cta_text'] ?? ''));
    $form['handoff_note'] = mb_substr(trim((string)($_POST['handoff_note'] ?? '')), 0, 1200);
    $form['reviewer_note'] = mb_substr(trim((string)($_POST['reviewer_note'] ?? '')), 0, 900);
    $ctaHref = trim((string)($_POST['cta_href'] ?? ''));
    $form['cta_href'] = ($ctaHref === '' || cms_url_is_safe($ctaHref)) ? $ctaHref : '';

    // Per-page experiment defaults: keep only registered ids with valid variant keys.
    $form['experiment_defaults'] = admin_clean_experiment_defaults($_POST['experiment_default'] ?? []);

    // Blocks arrive as a JSON string from the editor.
    $rawBlocks = cms_json_decode((string)($_POST['body_json'] ?? '[]'));
    $cleanBlocks = admin_clean_blocks($rawBlocks);
    $form['body_json'] = json_encode(array_values($cleanBlocks), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    // Validate.
    if ($form['slug'] === '') {
        $errors[] = 'A slug (or title to derive it from) is required.';
    }
    if ($form['canonical'] !== '' && !cms_url_is_safe($form['canonical'])) {
        $errors[] = 'Canonical URL is not a valid/safe URL.';
    }
    if ($submitIntent === 'submit_review' && !cms_user_can('reviewer') && mb_strlen($form['handoff_note']) < 20) {
        $errors[] = 'Add a short handoff note (at least 20 characters) so reviewers know what changed.';
    }
    if (cms_user_can('reviewer') && $page && ($page['status'] ?? '') !== $form['status'] && mb_strlen($form['reviewer_note']) < 10) {
        $errors[] = 'Add a reviewer decision note (at least 10 characters) when changing page status.';
    }
    // Slug must not collide with a physical file/dir served directly by Apache.
    // Exceptions: keeping an existing page's current slug, or a bridge-stub file
    // that already forwards to the CMS (e.g. imported pre-CMS pages).
    if ($form['slug'] !== '') {
        $slugUnchanged = $page && ($page['slug'] ?? '') === $form['slug'];
        $physical = CMS_APP_ROOT . '/' . $form['slug'];
        $physicalPhp = $physical . '.php';
        $isBridgeStub = is_file($physicalPhp) && admin_file_is_bridge_stub($physicalPhp);
        if (!$slugUnchanged && !$isBridgeStub && (is_file($physicalPhp) || is_dir($physical))) {
            $errors[] = 'Slug "' . $form['slug'] . '" collides with an existing file or folder and would be unreachable. Choose another.';
        }
    }
    // Slug uniqueness (excluding self).
    if ($form['slug'] !== '') {
        $dupe = cms_select_one(
            'SELECT id FROM cms_pages WHERE slug = ? AND id <> ? LIMIT 1',
            'si',
            [$form['slug'], $id]
        );
        if ($dupe) {
            $errors[] = 'Another page already uses the slug "' . $form['slug'] . '".';
        }
    }
    if ($form['status'] === 'published') {
        foreach (admin_publish_checks($form, $cleanBlocks) as $checkError) {
            $errors[] = $checkError;
        }
    }

    if (!$errors) {
        $user = cms_current_user()['email'];
        $publishedAt = null;
        $changeSummary = admin_build_change_summary($page, $form, $cleanBlocks);
        if ($form['status'] === 'published') {
            $publishedAt = ($page && !empty($page['published_at'])) ? $page['published_at'] : date('Y-m-d H:i:s');
        }

        if ($id > 0) {
            $oldSlug = $page['slug'];
            cms_write(
                'UPDATE cms_pages SET
                    slug = ?, title = ?, meta_description = ?, canonical = ?, og_image = ?,
                    template = ?,
                    theme = ?, status = ?, hero_headline = ?, hero_subtitle = ?, cta_text = ?, cta_href = ?,
                    body_json = ?, experiment_defaults = ?, updated_by = ?, published_at = ?
                 WHERE id = ?',
                'ssssssssssssssssi',
                [
                    $form['slug'], $form['title'], $form['meta_description'], $form['canonical'], $form['og_image'],
                    $form['template'], $form['theme'], $form['status'], $form['hero_headline'], $form['hero_subtitle'], $form['cta_text'], $form['cta_href'],
                    $form['body_json'], $form['experiment_defaults'], $user, $publishedAt, $id,
                ]
            );
            // Auto-redirect old slug -> new slug so links don't break.
            if ($oldSlug !== '' && $oldSlug !== $form['slug']) {
                cms_write(
                    'INSERT INTO cms_redirects (from_path, to_path, code)
                        VALUES (?, ?, 301)
                     ON DUPLICATE KEY UPDATE to_path = VALUES(to_path), code = 301',
                    'ss',
                    ['/' . $oldSlug, '/' . $form['slug']]
                );
            }
        } else {
            $id = (int)cms_write(
                'INSERT INTO cms_pages
                    (slug, title, meta_description, canonical, og_image, template, theme, status,
                     hero_headline, hero_subtitle, cta_text, cta_href, body_json, experiment_defaults, created_by, updated_by, published_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                'sssssssssssssssss',
                [
                    $form['slug'], $form['title'], $form['meta_description'], $form['canonical'], $form['og_image'],
                    $form['template'], $form['theme'], $form['status'],
                    $form['hero_headline'], $form['hero_subtitle'], $form['cta_text'], $form['cta_href'],
                    $form['body_json'], $form['experiment_defaults'], $user, $user, $publishedAt,
                ]
            );
        }

        // Revision snapshot for rollback.
        if ($id > 0) {
            $snapshot = cms_select_one('SELECT * FROM cms_pages WHERE id = ? LIMIT 1', 'i', [$id]);
            if ($snapshot) {
                $revisionNote = 'save';
                if (!$page) {
                    $revisionNote = 'create';
                } elseif (($page['status'] ?? '') !== $form['status']) {
                    $revisionNote = 'status ' . ($page['status'] ?? 'draft') . ' -> ' . $form['status'];
                } elseif ($submitIntent === 'submit_review') {
                    $revisionNote = 'submit_review';
                }
                if ($form['reviewer_note'] !== '') {
                    $revisionNote .= ' | reviewer note: ' . $form['reviewer_note'];
                }
                cms_write(
                    'INSERT INTO cms_page_revisions (page_id, snapshot_json, editor, note) VALUES (?, ?, ?, ?)',
                    'isss',
                    [$id, json_encode($snapshot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $user, $revisionNote]
                );
            }
            cms_audit('page_save', 'cms_page', $id, [
                'slug' => $form['slug'],
                'status' => $form['status'],
                'submit_intent' => $submitIntent,
                'handoff_note' => $form['handoff_note'],
                'reviewer_note' => $form['reviewer_note'],
                'change_summary' => $changeSummary,
            ]);
            if ($submitIntent === 'submit_review') {
                cms_audit('page_submit_review', 'cms_page', $id, [
                    'slug' => $form['slug'],
                    'status' => $form['status'],
                    'handoff_note' => $form['handoff_note'],
                    'change_summary' => $changeSummary,
                ]);
            }
            if ($submitIntent === 'submit_review') {
                admin_flash_set('success', 'Page saved and submitted for review.');
            } else {
                admin_flash_set('success', 'Page saved.');
            }
            header('Location: ' . $base . '/edit.php?id=' . $id);
            exit;
        }
        $errors[] = 'Could not save the page. Please try again.';
    }
}

$isNew = $id === 0;
$canPublish = cms_user_can('reviewer');
$snippetLibrary = cms_snippets_load();
$snippetBlocksForEditor = [];
foreach ($snippetLibrary as $snippetKey => $snippetConfig) {
    $snippetBlocksForEditor[(string)$snippetKey] = array_values((array)($snippetConfig['blocks'] ?? []));
}
$legacySectionsForEditor = cms_legacy_section_options();
admin_header($isNew ? 'New page' : 'Edit page');
?>
<h1><?= $isNew ? 'New page' : 'Edit page' ?></h1>

<?php if ($errors): ?>
    <div class="flash error">
        <?php foreach ($errors as $e): ?>
            <div><?= cms_e($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!$isNew && $form['status'] === 'published'): ?>
    <p><a class="btn btn-ghost btn-sm" href="/<?= cms_e($form['slug']) ?>" target="_blank" rel="noopener">View published page &rarr;</a></p>
<?php endif; ?>

<?php if (!$isNew): ?>
    <p><a class="btn btn-ghost btn-sm" href="<?= cms_e($base) ?>/revisions.php?page_id=<?= (int)$id ?>">Revision history</a></p>
<?php endif; ?>

<style>
    #page-form.simple-mode .advanced-only { display: none !important; }
    #page-form.simple-mode .advanced-add { display: none !important; }
    #page-form.simple-mode .block-card.block-advanced { display: none !important; }
    #page-form .simple-only { display: none; }
    #page-form.simple-mode .simple-only { display: block; }
    .editor-tools { display: grid; gap: .8rem; }
    .editor-tools .mode-toggle { display: flex; gap: .8rem; flex-wrap: wrap; }
    .editor-tools label.mode-option { font-weight: 600; font-size: .82rem; margin: 0; display: inline-flex; gap: .4rem; align-items: center; }
    .editor-note { font-size: .8rem; color: #5a6672; }
    .restore-banner { display: none; padding: .65rem .8rem; border: 1px solid #d9e6f5; background: #eff6ff; border-radius: 8px; }
    .restore-banner.show { display: block; }
    .publish-checklist { list-style: none; padding: 0; margin: .5rem 0 0; }
    .publish-checklist li { padding: .28rem 0; font-size: .85rem; }
    .publish-checklist .ok { color: #1c6b3a; }
    .publish-checklist .bad { color: #9b2226; }
    .publish-checklist .warn { color: #8a5a00; }
    .char-count { font-size: .74rem; color: #6b7885; margin-left: .35rem; }
    .char-count.over { color: #9b2226; font-weight: 700; }
    .block-card.block-advanced { border-left: 4px solid #c7a64d; }
    .advanced-block-note { margin: .25rem 0 .7rem; font-size: .78rem; color: #8a5a00; }
    .block-card.block-collapsed > :not(.block-head):not(.advanced-block-note) { display: none; }
    .editor-shell { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 1rem; align-items: start; }
    .editor-main { min-width: 0; }
    .editor-side { position: sticky; top: 86px; }
    .step-card { padding-top: .95rem; }
    .step-kicker { color: #5f7385; font-size: .74rem; letter-spacing: .06em; text-transform: uppercase; margin-bottom: .2rem; }
    .step-title { font-size: 1.04rem; margin: 0 0 .45rem; color: #15314a; }
    .step-note { font-size: .82rem; color: #4f6374; margin: 0 0 .65rem; }
    .composer-actions { position: sticky; bottom: 0; z-index: 8; padding: .75rem .85rem; border: 1px solid #d8e5ef; border-radius: 12px; background: rgba(255,255,255,.95); backdrop-filter: blur(8px); box-shadow: 0 10px 30px rgba(15,35,60,.09); }
    .block-card { position: relative; }
    .block-card.dragging { opacity: .6; border-style: dashed; }
    .drag-handle { cursor: move; }
    .blocks-empty { border: 1px dashed #b7cbe0; background: #f7fbff; color: #3f5e78; border-radius: 10px; padding: .8rem; font-size: .84rem; margin-bottom: .65rem; }
    .drag-tip { margin-top: .45rem; font-size: .76rem; color: #607282; }
    .role-cue { border: 1px solid #dbe8f3; border-radius: 10px; background: #f6fbff; padding: .65rem .7rem; margin-bottom: .7rem; font-size: .82rem; color: #36566f; }
    .review-handoff { min-height: 105px; }
    .composer-head { padding-bottom: 1rem; }
    .composer-head-row { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
    .composer-head-meta { display:flex; align-items:center; gap:1rem; flex-wrap:wrap; margin-top:.7rem; padding-top:.7rem; border-top:1px solid var(--line); }
    .step-card .section-head { margin-bottom: .9rem; }
    @media (max-width: 1060px) {
        .editor-shell { grid-template-columns: 1fr; }
        .editor-side { position: static; }
    }
</style>

<form method="post" action="<?= cms_e($base) ?>/edit.php<?= $isNew ? '' : '?id=' . (int)$id ?>" id="page-form">
    <?= cms_csrf_field() ?>

    <div class="editor-shell">
    <div class="editor-main">

    <div class="card composer-head">
        <div class="composer-head-row">
            <div>
                <div class="step-kicker">Guided editor</div>
                <h2 class="step-title" style="margin:0;">Build your page</h2>
                <p class="step-note" style="margin:.3rem 0 0;">Work through each section below. Everyday edits only need the simple view &mdash; switch on advanced options when you need technical controls.</p>
            </div>
            <div class="segmented" role="group" aria-label="Editor detail level">
                <label class="mode-option"><input type="radio" name="editor_mode" value="simple" checked> Simple</label>
                <label class="mode-option"><input type="radio" name="editor_mode" value="advanced"> Advanced</label>
            </div>
        </div>
        <div class="composer-head-meta">
            <span id="autosave-status" class="editor-note">Autosave active.</span>
            <span class="simple-only editor-note" id="simple-mode-block-note"></span>
            <a class="editor-note" href="<?= cms_e($base) ?>/guide.php" style="margin-left:auto;font-weight:700;color:var(--ah-strong);">Need help? Read the guide &rarr;</a>
        </div>
        <div id="restore-banner" class="restore-banner">
            <strong>Unsaved draft found.</strong> You have local changes from a previous session on this device.
            <div class="actions" style="margin-top:.45rem;">
                <button type="button" class="btn btn-ghost btn-sm" id="restore-draft-btn">Restore draft</button>
                <button type="button" class="btn btn-danger btn-sm" id="discard-draft-btn">Discard</button>
            </div>
        </div>
    </div>

    <div class="card step-card">
        <div class="section-head">
            <span class="section-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h5"/></svg></span>
            <div>
                <h2 class="section-title">Page basics</h2>
                <p class="section-desc">Give the page a name and choose who can see it.</p>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?= cms_e($form['title']) ?>" maxlength="255">
                <div class="hint">Used for the browser tab and as the hero fallback. Recommended 40-65 chars.<span class="char-count" data-count-for="title"></span></div>
            </div>
            <div class="col advanced-only">
                <label for="slug">Web address (URL)</label>
                <input type="text" id="slug" name="slug" value="<?= cms_e($form['slug']) ?>" maxlength="191" placeholder="auto from title">
                <div class="hint">Public URL: <code>/<span id="slug-preview"><?= cms_e($form['slug'] !== '' ? $form['slug'] : 'your-slug') ?></span></code></div>
            </div>
        </div>
        <div class="row">
            <div class="col advanced-only">
                <label for="template">Template</label>
                <select id="template" name="template">
                    <?php foreach ($GLOBALS['CMS_PAGE_TEMPLATES'] as $templateKey => $templateLabel): ?>
                        <option value="<?= cms_e($templateKey) ?>" <?= $form['template'] === $templateKey ? 'selected' : '' ?>><?= cms_e($templateLabel) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="hint">Matches the shared landing-page template families used by the root pages.</div>
            </div>
            <div class="col advanced-only">
                <label for="theme">Theme</label>
                <select id="theme" name="theme">
                    <?php foreach (admin_theme_options() as $t): ?>
                        <option value="<?= cms_e($t) ?>" <?= $form['theme'] === $t ? 'selected' : '' ?>><?= cms_e($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <?php foreach ($GLOBALS['CMS_STATUSES'] as $st): ?>
                        <?php
                            $lockPublished = !$canPublish && $st === 'published';
                            if ($lockPublished && $form['status'] !== 'published') {
                                continue;
                            }
                        ?>
                        <option value="<?= cms_e($st) ?>" <?= $form['status'] === $st ? 'selected' : '' ?> <?= $lockPublished ? 'disabled' : '' ?>><?= cms_e(ucfirst($st)) ?><?= $lockPublished ? ' (reviewer only)' : '' ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="hint">Only <strong>published</strong> pages are publicly visible.<?= $canPublish ? '' : ' Use Save draft and Submit for review for approvals.' ?></div>
            </div>
        </div>
    </div>

    <div class="card step-card">
        <div class="section-head">
            <span class="section-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/></svg></span>
            <div>
                <h2 class="section-title">Headline &amp; button</h2>
                <p class="section-desc">Write the top-of-page message and the main action button visitors see first.</p>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <label for="hero_headline">Headline</label>
                <input type="text" id="hero_headline" name="hero_headline" value="<?= cms_e($form['hero_headline']) ?>" maxlength="255">
                <div class="hint">Recommended 35-80 chars.<span class="char-count" data-count-for="hero_headline"></span></div>
                <div class="headline-tools" style="margin-top:.4rem; display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
                    <button type="button" class="btn btn-ghost btn-sm" id="hero_highlight_btn">Highlight selection</button>
                    <button type="button" class="btn btn-ghost btn-sm" id="hero_highlight_clear">Clear highlight</button>
                    <span class="hint" style="margin:0;">Select part of the headline, then Highlight to color it.</span>
                </div>
                <div class="headline-preview" id="hero_headline_preview" aria-live="polite" style="margin-top:.4rem;"></div>
                <style>
                    #hero_headline_preview { font-weight:700; font-size:1.05rem; line-height:1.3; }
                    #hero_headline_preview .text-secondary { color:#c9a227; } /* preview highlight only */
                </style>
            </div>
            <div class="col">
                <label for="hero_subtitle">Subtitle</label>
                <input type="text" id="hero_subtitle" name="hero_subtitle" value="<?= cms_e($form['hero_subtitle']) ?>" maxlength="255">
                <div class="hint">Recommended 60-140 chars.<span class="char-count" data-count-for="hero_subtitle"></span></div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <label for="cta_text">Button text</label>
                <input type="text" id="cta_text" name="cta_text" value="<?= cms_e($form['cta_text']) ?>" maxlength="120">
                <div class="hint">Keep concise and action-focused. Recommended 2-30 chars.<span class="char-count" data-count-for="cta_text"></span></div>
            </div>
            <div class="col">
                <label for="cta_href">Button link</label>
                <input type="text" id="cta_href" name="cta_href" value="<?= cms_e($form['cta_href']) ?>" maxlength="255" placeholder="/multi-quote/">
            </div>
        </div>
        <div class="actions" style="margin-top:.75rem;">
            <span class="editor-note" style="margin-right:.5rem;">Quick variants:</span>
            <button type="button" class="btn btn-ghost btn-sm" data-hero-variant="A">Headline A</button>
            <button type="button" class="btn btn-ghost btn-sm" data-hero-variant="B">Headline B</button>
            <button type="button" class="btn btn-ghost btn-sm" data-hero-variant="C">Headline C</button>
        </div>
    </div>

    <div class="card step-card" id="content-blocks-card">
        <div class="section-head">
            <span class="section-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="5" rx="1"/><rect x="3" y="13" width="18" height="7" rx="1"/></svg></span>
            <div>
                <h2 class="section-title">Page sections</h2>
                <p class="section-desc">Add content blocks, then drag the handle to arrange them in the order visitors will read.</p>
            </div>
        </div>
        <div id="blocks-empty-state" class="blocks-empty">Start by adding a block or insert an approved snippet pack to generate a ready-made section stack.</div>
        <div id="blocks"></div>
        <div class="drag-tip">Tip: Drag using the handle on each block, or use arrow buttons for precise ordering.</div>
        <div class="actions" style="margin-top:.75rem;">
            <button type="button" class="btn btn-ghost btn-sm" data-add="rich_text">+ Text</button>
            <button type="button" class="btn btn-ghost btn-sm" data-add="legacy_section">+ Prebuilt section</button>
            <button type="button" class="btn btn-ghost btn-sm advanced-add" data-add="legacy_html">+ Legacy HTML</button>
            <button type="button" class="btn btn-ghost btn-sm advanced-add" data-add="snapshot_html">+ Page snapshot</button>
            <button type="button" class="btn btn-ghost btn-sm" data-add="cta_banner">+ Call-to-action</button>
            <button type="button" class="btn btn-ghost btn-sm advanced-add" data-add="image">+ Image</button>
            <button type="button" class="btn btn-ghost btn-sm" data-add="faq_list">+ FAQ</button>
        </div>
        <input type="hidden" name="body_json" id="body_json" value="">
    </div>

    <div class="card step-card" id="snippet-library-card">
        <div class="section-head">
            <span class="section-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></span>
            <div>
                <h2 class="section-title">Ready-made sections</h2>
                <p class="section-desc">Insert pre-approved section packs for faster, on-brand updates.</p>
            </div>
        </div>
        <?php if ($snippetLibrary): ?>
            <div class="actions" style="margin-top:.75rem; gap:.45rem; flex-wrap:wrap;">
                <?php foreach ($snippetLibrary as $snippetKey => $snippetCfg): ?>
                    <button type="button" class="btn btn-ghost btn-sm" data-snippet="<?= cms_e((string)$snippetKey) ?>">+ <?= cms_e((string)($snippetCfg['label'] ?? $snippetKey)) ?></button>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="muted">No approved snippets are configured yet.</div>
        <?php endif; ?>
    </div>

    <div class="card advanced-only step-card">
        <div class="section-head">
            <span class="section-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg></span>
            <div>
                <h2 class="section-title">Search &amp; sharing</h2>
                <p class="section-desc">Control how this page appears in search results and when shared on social.</p>
            </div>
        </div>
        <label for="meta_description">Search description</label>
        <textarea id="meta_description" name="meta_description" maxlength="320" style="min-height:70px;"><?= cms_e($form['meta_description']) ?></textarea>
        <div class="hint">Recommended 80-160 chars for search snippets.<span class="char-count" data-count-for="meta_description"></span></div>
        <div class="row">
            <div class="col">
                <label for="canonical">Canonical URL</label>
                <input type="text" id="canonical" name="canonical" value="<?= cms_e($form['canonical']) ?>" maxlength="255">
            </div>
            <div class="col">
                <label for="og_image">Social share image</label>
                <input type="text" id="og_image" name="og_image" value="<?= cms_e($form['og_image']) ?>" maxlength="255" placeholder="/img/...">
            </div>
        </div>
    </div>

    <?php
        $ahPageDefaults = cms_json_decode((string)($form['experiment_defaults'] ?? ''));
        if (!is_array($ahPageDefaults)) { $ahPageDefaults = []; }
    ?>
    <div class="card advanced-only step-card">
        <div class="section-head">
            <span class="section-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><circle cx="4" cy="12" r="2"/><circle cx="12" cy="10" r="2"/><circle cx="20" cy="14" r="2"/></svg></span>
            <div>
                <h2 class="section-title">Page variants</h2>
                <p class="section-desc">Set a default headline/copy/color variant for THIS page. A visitor's own debug-panel choice still overrides these.</p>
            </div>
        </div>
        <div class="row" style="flex-wrap:wrap; gap:1rem;">
            <?php foreach ($ahExperimentRegistry as $expId => $expCfg): ?>
                <?php $current = (string)($ahPageDefaults[$expId] ?? ''); ?>
                <div class="col" style="min-width:260px;">
                    <label for="exp_<?= cms_e($expId) ?>"><?= cms_e($expCfg['label'] ?? $expId) ?></label>
                    <select id="exp_<?= cms_e($expId) ?>" name="experiment_default[<?= cms_e($expId) ?>]">
                        <option value="">Use site default</option>
                        <?php foreach (($expCfg['options'] ?? []) as $optKey => $optLabel): ?>
                            <?php if ((string)$optKey === (string)($expCfg['default'] ?? '')) { continue; } ?>
                            <option value="<?= cms_e($optKey) ?>" <?= $current === (string)$optKey ? 'selected' : '' ?>><?= cms_e($optLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($expCfg['note'])): ?>
                        <div class="hint"><?= cms_e($expCfg['note']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="composer-actions">
        <div class="actions">
            <button type="submit" class="btn btn-primary" name="submit_intent" value="save"><?= $canPublish ? 'Save page' : 'Save draft' ?></button>
            <?php if (!$canPublish): ?>
                <button type="submit" class="btn btn-ghost" name="submit_intent" value="submit_review">Submit for review</button>
            <?php endif; ?>
            <a class="btn btn-ghost" href="<?= cms_e($base) ?>/">Cancel</a>
        </div>
    </div>

    </div>

    <aside class="editor-side">
        <div class="card step-card">
            <div class="step-kicker">Publish Readiness</div>
            <h2 class="step-title" style="margin-bottom:.35rem;">Review And Handoff</h2>
            <?php if ($canPublish): ?>
                <div class="role-cue">Reviewer/Admin mode: you can publish when required checks pass.</div>
            <?php else: ?>
                <div class="role-cue">Marketer mode: save your draft, then submit for review with a clear handoff note.</div>
            <?php endif; ?>
            <label for="handoff_note">Reviewer handoff note</label>
            <textarea class="review-handoff" id="handoff_note" name="handoff_note" maxlength="1200" placeholder="Summarize what changed, what to verify, and any compliance notes."><?= cms_e($form['handoff_note']) ?></textarea>
            <div class="hint">Recommended 20+ characters when submitting for review.</div>
            <?php if ($canPublish): ?>
                <label for="reviewer_note">Reviewer decision note</label>
                <textarea class="review-handoff" id="reviewer_note" name="reviewer_note" maxlength="900" placeholder="When changing status, document the approval/rejection rationale."><?= cms_e($form['reviewer_note']) ?></textarea>
                <div class="hint">Required when changing status (min 10 chars).</div>
            <?php endif; ?>
            <h3 style="font-size:.92rem;margin:.85rem 0 .35rem;">Checklist</h3>
            <div id="publish-checklist-hint" class="editor-note">Required checks update as you edit.</div>
            <ul id="publish-checklist" class="publish-checklist"></ul>
        </div>
    </aside>
    </div>
    </div>
</form>

<script id="initial-body-json" type="application/json"><?= json_encode(
    cms_json_decode($form['body_json']),
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
) ?></script>
<script id="approved-snippets-json" type="application/json"><?= json_encode(
    $snippetBlocksForEditor,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
) ?></script>
<script id="legacy-sections-json" type="application/json"><?= json_encode(
    $legacySectionsForEditor,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
) ?></script>
<script>
(function () {
    var initial = [];
    var initialNode = document.getElementById('initial-body-json');
    if (initialNode) {
        try {
            initial = JSON.parse(initialNode.textContent || '[]');
        } catch (e) {
            initial = [];
        }
    }
    var approvedSnippets = {};
    var approvedSnippetsNode = document.getElementById('approved-snippets-json');
    if (approvedSnippetsNode) {
        try {
            approvedSnippets = JSON.parse(approvedSnippetsNode.textContent || '{}');
        } catch (e) {
            approvedSnippets = {};
        }
    }
    var legacySections = [];
    var legacySectionsNode = document.getElementById('legacy-sections-json');
    if (legacySectionsNode) {
        try {
            legacySections = JSON.parse(legacySectionsNode.textContent || '[]');
        } catch (e) {
            legacySections = [];
        }
    }
    var container = document.getElementById('blocks');
    var hidden = document.getElementById('body_json');
    var form = document.getElementById('page-form');
    var statusInput = document.getElementById('status');
    var autosaveStatus = document.getElementById('autosave-status');
    var restoreBanner = document.getElementById('restore-banner');
    var restoreDraftBtn = document.getElementById('restore-draft-btn');
    var discardDraftBtn = document.getElementById('discard-draft-btn');
    var checklistList = document.getElementById('publish-checklist');
    var checklistHint = document.getElementById('publish-checklist-hint');
    var blocksEmptyState = document.getElementById('blocks-empty-state');
    var modeInputs = document.querySelectorAll('input[name="editor_mode"]');
    var simpleModeBlockNote = document.getElementById('simple-mode-block-note');
    var charCounters = document.querySelectorAll('[data-count-for]');
    var snippetButtons = document.querySelectorAll('[data-snippet]');
    var variantButtons = document.querySelectorAll('[data-hero-variant]');
    var canPublish = <?= $canPublish ? 'true' : 'false' ?>;
    var originalStatus = <?= json_encode((string)($page['status'] ?? 'draft')) ?>;
    var pageId = <?= (int)$id ?>;
    var autosaveKey = 'cms_edit_draft_' + (pageId > 0 ? pageId : 'new');
    var modeKey = 'cms_edit_mode';
    var autosaveTimer = null;
    var isDirty = false;
    var saveInProgress = false;
    var baselineSnapshot = '';
    var submitIntent = 'save';
    var draggedCard = null;

    var SNIPPETS = approvedSnippets;

    var HERO_VARIANTS = {
        A: { headline: 'Find Affordable Healthcare Options', subtitle: 'Compare plan options that may fit your needs and budget.' },
        B: { headline: 'See Healthcare Quotes In Your Area', subtitle: 'Answer a few quick questions to request quote information.' },
        C: { headline: 'Shop Plans With Confidence', subtitle: 'Review plan details and ask questions before you enroll.' }
    };

    function el(tag, attrs, html) {
        var n = document.createElement(tag);
        if (attrs) { for (var k in attrs) { n.setAttribute(k, attrs[k]); } }
        if (html !== undefined) { n.innerHTML = html; }
        return n;
    }

    function fieldRow(labelText, inputEl) {
        var wrap = el('div');
        var lab = el('label', null, labelText);
        wrap.appendChild(lab);
        wrap.appendChild(inputEl);
        return wrap;
    }

    function applyAdvancedCardBehavior(card) {
        if (!card || card.getAttribute('data-advanced-init') === '1') {
            return;
        }
        card.setAttribute('data-advanced-init', '1');
        card.classList.add('block-advanced', 'block-collapsed');
        var actions = card.querySelector('.block-head .actions');
        if (actions) {
            var toggle = el('button', { type: 'button', 'class': 'btn btn-ghost btn-sm block-toggle' }, 'Expand');
            toggle.onclick = function () {
                var collapsed = card.classList.toggle('block-collapsed');
                toggle.textContent = collapsed ? 'Expand' : 'Collapse';
            };
            actions.insertBefore(toggle, actions.firstChild);
        }
        var note = el('div', { 'class': 'advanced-block-note' }, 'Advanced block: edit carefully.');
        card.insertBefore(note, card.children[1] || null);
    }

    function updateCharCounter(counterEl) {
        var key = counterEl.getAttribute('data-count-for');
        var input = document.getElementById(key);
        if (!input) {
            return;
        }
        var value = (input.value || '').trim();
        var len = value.length;
        var max = input.getAttribute('maxlength');
        counterEl.textContent = ' ' + len + (max ? '/' + max : '');
        counterEl.classList.toggle('over', !!max && len > parseInt(max, 10));
    }

    function updateCharCounters() {
        charCounters.forEach(updateCharCounter);
    }

    function appendBlocks(blocks) {
        (blocks || []).forEach(function (block) {
            var card = renderBlock(block);
            if (card) {
                container.appendChild(card);
            }
        });
    }

    function isSafeHref(v) {
        if (!v) { return false; }
        if (v.indexOf('/') === 0) { return true; }
        return /^https?:\/\//i.test(v);
    }

    function getFormState() {
        return {
            title: (document.getElementById('title') || {}).value || '',
            slug: (document.getElementById('slug') || {}).value || '',
            template: (document.getElementById('template') || {}).value || 'default',
            theme: (document.getElementById('theme') || {}).value || 'default',
            status: (document.getElementById('status') || {}).value || 'draft',
            hero_headline: (document.getElementById('hero_headline') || {}).value || '',
            hero_subtitle: (document.getElementById('hero_subtitle') || {}).value || '',
            cta_text: (document.getElementById('cta_text') || {}).value || '',
            cta_href: (document.getElementById('cta_href') || {}).value || '',
            handoff_note: (document.getElementById('handoff_note') || {}).value || '',
            reviewer_note: (document.getElementById('reviewer_note') || {}).value || '',
            meta_description: (document.getElementById('meta_description') || {}).value || '',
            canonical: (document.getElementById('canonical') || {}).value || '',
            og_image: (document.getElementById('og_image') || {}).value || '',
            blocks: collect()
        };
    }

    function snapshotState() {
        return JSON.stringify(getFormState());
    }

    function applyState(state) {
        var map = ['title', 'slug', 'template', 'theme', 'status', 'hero_headline', 'hero_subtitle', 'cta_text', 'cta_href', 'handoff_note', 'reviewer_note', 'meta_description', 'canonical', 'og_image'];
        map.forEach(function (k) {
            var node = document.getElementById(k);
            if (!node || typeof state[k] === 'undefined') {
                return;
            }
            node.value = state[k];
        });

        container.innerHTML = '';
        (state.blocks || []).forEach(function (block) {
            var card = renderBlock(block);
            if (card) { container.appendChild(card); }
        });
        hidden.value = JSON.stringify(collect());
    }

    function buildChecklist() {
        var st = getFormState();
        var checks = [
            { key: 'title', label: 'Page title is set', ok: st.title.trim() !== '', critical: true },
            { key: 'hero', label: 'Hero headline is set', ok: st.hero_headline.trim() !== '', critical: true },
            { key: 'ctaText', label: 'CTA button text is set', ok: st.cta_text.trim() !== '', critical: true },
            { key: 'ctaHref', label: 'CTA link is set to a safe URL', ok: isSafeHref(st.cta_href.trim()), critical: true },
            { key: 'blocks', label: 'At least one content block exists', ok: (st.blocks || []).length > 0, critical: true },
            { key: 'reviewerGate', label: 'Publishing requires reviewer/admin role', ok: canPublish || st.status !== 'published', critical: true },
            { key: 'handoff', label: 'Handoff note is ready for reviewer', ok: canPublish || st.handoff_note.trim().length >= 20, critical: false },
            { key: 'reviewDecision', label: 'Reviewer decision note provided for status changes', ok: !canPublish || st.status === originalStatus || st.reviewer_note.trim().length >= 10, critical: true },
            { key: 'meta', label: 'Meta description is recommended (80-160 chars)', ok: st.meta_description.trim().length >= 80 && st.meta_description.trim().length <= 160, critical: false },
            { key: 'canonical', label: 'Canonical URL should not point to localhost', ok: st.canonical.trim() === '' || st.canonical.toLowerCase().indexOf('localhost') === -1, critical: false }
        ];
        return checks;
    }

    function renderChecklist() {
        if (!checklistList) {
            return { criticalFailed: 0 };
        }
        var checks = buildChecklist();
        checklistList.innerHTML = '';
        var criticalFailed = 0;

        checks.forEach(function (it) {
            var li = document.createElement('li');
            if (it.ok) {
                li.className = 'ok';
                li.textContent = 'OK - ' + it.label;
            } else if (it.critical) {
                li.className = 'bad';
                li.textContent = 'Needs attention - ' + it.label;
                criticalFailed++;
            } else {
                li.className = 'warn';
                li.textContent = 'Recommended - ' + it.label;
            }
            checklistList.appendChild(li);
        });

        if (checklistHint) {
            if (statusInput && statusInput.value === 'published' && criticalFailed > 0) {
                checklistHint.textContent = 'You cannot publish until all required checks pass.';
            } else {
                checklistHint.textContent = 'Required checks update as you edit.';
            }
        }
        return { criticalFailed: criticalFailed };
    }

    function updateSimpleModeNote() {
        if (!simpleModeBlockNote) {
            return;
        }
        var hiddenCount = container.querySelectorAll('.block-card.block-advanced').length;
        if (form.classList.contains('simple-mode') && hiddenCount > 0) {
            simpleModeBlockNote.textContent = hiddenCount + ' advanced content block(s) are hidden in Simple mode.';
        } else {
            simpleModeBlockNote.textContent = '';
        }
    }

    function updateBlocksEmptyState() {
        if (!blocksEmptyState) {
            return;
        }
        blocksEmptyState.style.display = container.querySelectorAll(':scope > .block-card').length ? 'none' : 'block';
    }

    function setMode(mode) {
        var simple = mode === 'simple';
        form.classList.toggle('simple-mode', simple);
        try { localStorage.setItem(modeKey, simple ? 'simple' : 'advanced'); } catch (e) {}
        modeInputs.forEach(function (input) {
            input.checked = input.value === (simple ? 'simple' : 'advanced');
        });
        updateSimpleModeNote();
    }

    function updateDirtyState() {
        if (saveInProgress) {
            return;
        }
        isDirty = snapshotState() !== baselineSnapshot;
        if (autosaveStatus && !isDirty && !autosaveStatus.textContent) {
            autosaveStatus.textContent = 'All changes saved.';
        }
    }

    function saveDraftNow() {
        if (saveInProgress) {
            return;
        }
        try {
            localStorage.setItem(autosaveKey, snapshotState());
            if (autosaveStatus) {
                autosaveStatus.textContent = 'Draft autosaved at ' + new Date().toLocaleTimeString();
            }
        } catch (e) {
            if (autosaveStatus) {
                autosaveStatus.textContent = 'Autosave unavailable in this browser session.';
            }
        }
    }

    function scheduleAutosave() {
        if (autosaveTimer) {
            clearTimeout(autosaveTimer);
        }
        autosaveTimer = setTimeout(function () {
            saveDraftNow();
            updateDirtyState();
            renderChecklist();
        }, 1200);
    }

    function makeCard(type, title) {
        var card = el('div', { 'class': 'block-card', 'data-type': type, draggable: 'true' });
        var head = el('div', { 'class': 'block-head' });
        head.appendChild(el('span', { 'class': 'block-type' }, title));
        var controls = el('div', { 'class': 'actions' });
        var drag = el('button', { type: 'button', 'class': 'btn btn-ghost btn-sm drag-handle', title: 'Drag to reorder' }, '&#x2630;');
        var up = el('button', { type: 'button', 'class': 'btn btn-ghost btn-sm' }, '&uarr;');
        var down = el('button', { type: 'button', 'class': 'btn btn-ghost btn-sm' }, '&darr;');
        var del = el('button', { type: 'button', 'class': 'btn btn-danger btn-sm' }, 'Remove');
        up.onclick = function () {
            if (card.previousElementSibling) {
                container.insertBefore(card, card.previousElementSibling);
                scheduleAutosave();
                renderChecklist();
            }
        };
        down.onclick = function () {
            if (card.nextElementSibling) {
                container.insertBefore(card.nextElementSibling, card);
                scheduleAutosave();
                renderChecklist();
            }
        };
        del.onclick = function () {
            card.remove();
            updateSimpleModeNote();
            updateBlocksEmptyState();
            scheduleAutosave();
            renderChecklist();
        };
        controls.appendChild(drag); controls.appendChild(up); controls.appendChild(down); controls.appendChild(del);
        head.appendChild(controls);
        card.appendChild(head);
        card.addEventListener('dragstart', function (event) {
            draggedCard = card;
            card.classList.add('dragging');
            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', card.getAttribute('data-type') || 'block');
            }
        });
        card.addEventListener('dragend', function () {
            card.classList.remove('dragging');
            draggedCard = null;
            scheduleAutosave();
            renderChecklist();
            updateDirtyState();
        });
        return card;
    }

    container.addEventListener('dragover', function (event) {
        event.preventDefault();
        if (!draggedCard) {
            return;
        }
        var afterElement = (function (y) {
            var cards = Array.prototype.slice.call(container.querySelectorAll(':scope > .block-card:not(.dragging)'));
            var closest = { offset: Number.NEGATIVE_INFINITY, node: null };
            cards.forEach(function (child) {
                var box = child.getBoundingClientRect();
                var offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    closest = { offset: offset, node: child };
                }
            });
            return closest.node;
        })(event.clientY);

        if (!afterElement) {
            container.appendChild(draggedCard);
        } else {
            container.insertBefore(draggedCard, afterElement);
        }
    });

    function renderBlock(block) {
        var type = block.type;
        var card;
        if (type === 'rich_text') {
            card = makeCard(type, 'Rich text');
            var ta = el('textarea', { 'data-k': 'html' });
            ta.value = block.html || '';
            card.appendChild(fieldRow('HTML (allowed: p, h2-h4, ul/ol/li, a, strong, em, blockquote)', ta));
        } else if (type === 'legacy_html') {
            card = makeCard(type, 'Legacy HTML');
            var lt = el('textarea', { 'data-k': 'html' });
            lt.value = block.html || '';
            lt.style.minHeight = '260px';
            card.appendChild(fieldRow('Full landing-page HTML', lt));
            applyAdvancedCardBehavior(card);
        } else if (type === 'snapshot_html') {
            card = makeCard(type, 'Page snapshot');
            var st = el('textarea', { 'data-k': 'html' });
            st.value = block.html || '';
            st.style.minHeight = '320px';
            card.appendChild(fieldRow('Exact HTML snapshot (starts as the current page)', st));
            applyAdvancedCardBehavior(card);
        } else if (type === 'cta_banner') {
            card = makeCard(type, 'CTA banner');
            var t = el('input', { type: 'text', 'data-k': 'text' }); t.value = block.text || '';
            var h = el('input', { type: 'text', 'data-k': 'href', placeholder: '/multi-quote/' }); h.value = block.href || '';
            var c = el('input', { type: 'text', 'data-k': 'cta_text', placeholder: 'Get Started' }); c.value = block.cta_text || '';
            card.appendChild(fieldRow('Heading text', t));
            card.appendChild(fieldRow('Button link', h));
            card.appendChild(fieldRow('Button label', c));
        } else if (type === 'image') {
            card = makeCard(type, 'Image');
            var s = el('input', { type: 'text', 'data-k': 'src', placeholder: '/img/example.jpg' }); s.value = block.src || '';
            var a = el('input', { type: 'text', 'data-k': 'alt' }); a.value = block.alt || '';
            card.appendChild(fieldRow('Image path (must be under /img/...)', s));
            card.appendChild(fieldRow('Alt text', a));
            applyAdvancedCardBehavior(card);
        } else if (type === 'faq_list') {
            card = makeCard(type, 'FAQ list');
            var hd = el('input', { type: 'text', 'data-k': 'heading' }); hd.value = block.heading || '';
            card.appendChild(fieldRow('Section heading (optional)', hd));
            var items = el('div', { 'data-items': '1' });
            card.appendChild(items);
            var addItem = el('button', { type: 'button', 'class': 'btn btn-ghost btn-sm' }, '+ Q&amp;A');
            addItem.onclick = function () { items.appendChild(faqItem({})); };
            card.appendChild(addItem);
            (block.items || []).forEach(function (it) { items.appendChild(faqItem(it)); });
            if (!(block.items || []).length) { items.appendChild(faqItem({})); }
        } else if (type === 'legacy_section') {
            card = makeCard(type, 'Legacy section include');
            var select = el('select', { 'data-k': 'section_key' });
            legacySections.forEach(function (opt) {
                var option = el('option', { value: opt.key }, opt.label);
                if ((block.section_key || '') === opt.key) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
            if (!legacySections.length) {
                select.appendChild(el('option', { value: '' }, 'No sections available'));
            }
            if (!block.section_key && legacySections.length) {
                select.value = legacySections[0].key;
            }
            card.appendChild(fieldRow('Legacy section', select));
            var descWrap = el('div', { 'class': 'hint', 'data-legacy-desc': '1' });
            function updateLegacyDesc() {
                var selected = select.value || '';
                var cfg = legacySections.find(function (row) { return row.key === selected; }) || null;
                descWrap.textContent = cfg && cfg.description ? cfg.description : 'This inserts a trusted pre-CMS include section.';
            }
            updateLegacyDesc();
            select.addEventListener('change', updateLegacyDesc);
            card.appendChild(descWrap);
        } else {
            return null;
        }
        return card;
    }

    function faqItem(it) {
        var row = el('div', { 'class': 'faq-item' });
        var q = el('input', { type: 'text', 'data-k': 'q', placeholder: 'Question' }); q.value = it.q || '';
        var a = el('textarea', { 'data-k': 'a' }); a.value = it.a || ''; a.style.minHeight = '70px';
        row.appendChild(fieldRow('Question', q));
        row.appendChild(fieldRow('Answer (HTML allowed)', a));
        var rm = el('button', { type: 'button', 'class': 'btn btn-danger btn-sm' }, 'Remove Q&amp;A');
        rm.onclick = function () { row.remove(); };
        row.appendChild(rm);
        return row;
    }

    function collect() {
        var out = [];
        container.querySelectorAll(':scope > .block-card').forEach(function (card) {
            var type = card.getAttribute('data-type');
            var block = { type: type };
            if (type === 'faq_list') {
                block.heading = (card.querySelector('[data-k="heading"]') || {}).value || '';
                block.items = [];
                card.querySelectorAll('.faq-item').forEach(function (row) {
                    block.items.push({
                        q: (row.querySelector('[data-k="q"]') || {}).value || '',
                        a: (row.querySelector('[data-k="a"]') || {}).value || ''
                    });
                });
            } else {
                card.querySelectorAll('[data-k]').forEach(function (inp) {
                    block[inp.getAttribute('data-k')] = inp.value;
                });
            }
            out.push(block);
        });
        return out;
    }

    document.querySelectorAll('[data-add]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var card = renderBlock({ type: btn.getAttribute('data-add') });
            if (card) {
                container.appendChild(card);
                updateSimpleModeNote();
                updateBlocksEmptyState();
                scheduleAutosave();
                renderChecklist();
            }
        });
    });

    (initial || []).forEach(function (block) {
        var card = renderBlock(block);
        if (card) { container.appendChild(card); }
    });

    snippetButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var key = btn.getAttribute('data-snippet');
            if (!SNIPPETS[key]) {
                return;
            }
            appendBlocks(SNIPPETS[key]);
            updateSimpleModeNote();
            updateBlocksEmptyState();
            updateCharCounters();
            renderChecklist();
            scheduleAutosave();
            updateDirtyState();
        });
    });

    variantButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var key = btn.getAttribute('data-hero-variant');
            var variant = HERO_VARIANTS[key];
            if (!variant) {
                return;
            }
            var heroHeadline = document.getElementById('hero_headline');
            var heroSubtitle = document.getElementById('hero_subtitle');
            var ctaText = document.getElementById('cta_text');
            if (heroHeadline) {
                heroHeadline.value = variant.headline;
            }
            if (heroSubtitle) {
                heroSubtitle.value = variant.subtitle;
            }
            if (ctaText && ctaText.value.trim() === '') {
                ctaText.value = 'Get Quotes';
            }
            updateCharCounters();
            renderChecklist();
            scheduleAutosave();
            updateDirtyState();
        });
    });

    // Headline highlight: wrap the selected text in <span class="text-secondary">.
    (function () {
        var hh = document.getElementById('hero_headline');
        if (!hh) { return; }
        var hlBtn = document.getElementById('hero_highlight_btn');
        var clrBtn = document.getElementById('hero_highlight_clear');
        var prev = document.getElementById('hero_headline_preview');
        var OPEN = '<span class="text-secondary">';
        var CLOSE = '</span>';

        function renderPreview() {
            if (!prev) { return; }
            var esc = (hh.value || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            esc = esc
                .replace(/&lt;span\s+class=(?:&quot;|&#0?39;|")?text-secondary(?:&quot;|&#0?39;|")?&gt;/gi, OPEN)
                .replace(/&lt;\/span&gt;/gi, CLOSE)
                .replace(/&lt;br\s*\/?&gt;/gi, '<br>')
                .replace(/&lt;(\/?)(strong|em|b|i)&gt;/gi, '<$1$2>');
            prev.innerHTML = esc || '<span class="muted">Headline preview appears here</span>';
        }

        function afterChange() {
            renderPreview();
            updateCharCounters();
            renderChecklist();
            scheduleAutosave();
            updateDirtyState();
        }

        function wrapSelection() {
            var s = hh.selectionStart, e = hh.selectionEnd, val = hh.value || '';
            if (s === null || e === null || s === e) { hh.focus(); return; }
            hh.value = val.slice(0, s) + OPEN + val.slice(s, e) + CLOSE + val.slice(e);
            hh.focus();
            hh.selectionStart = s + OPEN.length;
            hh.selectionEnd = e + OPEN.length;
            afterChange();
        }

        function clearHighlight() {
            hh.value = (hh.value || '')
                .replace(/<span\s+class=(?:"|')?text-secondary(?:"|')?>/gi, '')
                .replace(/<\/span>/gi, '');
            afterChange();
        }

        if (hlBtn) { hlBtn.addEventListener('click', wrapSelection); }
        if (clrBtn) { clrBtn.addEventListener('click', clearHighlight); }
        hh.addEventListener('input', renderPreview);
        renderPreview();
    })();

    modeInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            setMode(input.value);
        });
    });

    var preferredMode = 'simple';
    try {
        preferredMode = localStorage.getItem(modeKey) || 'simple';
    } catch (e) {
        preferredMode = 'simple';
    }
    setMode(preferredMode === 'advanced' ? 'advanced' : 'simple');

    form.addEventListener('submit', function () {
        hidden.value = JSON.stringify(collect());
    });

    form.querySelectorAll('button[name="submit_intent"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            submitIntent = btn.value || 'save';
        });
    });

    form.addEventListener('submit', function (event) {
        var result = renderChecklist();
        if (statusInput && statusInput.value === 'published' && result.criticalFailed > 0) {
            event.preventDefault();
            if (checklistHint) {
                checklistHint.textContent = 'Please fix required checklist items before publishing.';
            }
            window.scrollTo({ top: checklistList.getBoundingClientRect().top + window.scrollY - 120, behavior: 'smooth' });
            return;
        }

        var handoff = (document.getElementById('handoff_note') || {}).value || '';
        if (submitIntent === 'submit_review' && !canPublish && handoff.trim().length < 20) {
            event.preventDefault();
            if (checklistHint) {
                checklistHint.textContent = 'Add a reviewer handoff note (at least 20 characters) before submitting for review.';
            }
            var handoffField = document.getElementById('handoff_note');
            if (handoffField && typeof handoffField.focus === 'function') {
                handoffField.focus();
            }
            return;
        }

        var reviewerNote = (document.getElementById('reviewer_note') || {}).value || '';
        var nextStatus = (statusInput || {}).value || 'draft';
        if (canPublish && nextStatus !== originalStatus && reviewerNote.trim().length < 10) {
            event.preventDefault();
            if (checklistHint) {
                checklistHint.textContent = 'Add a reviewer decision note (at least 10 characters) before changing status.';
            }
            var reviewerField = document.getElementById('reviewer_note');
            if (reviewerField && typeof reviewerField.focus === 'function') {
                reviewerField.focus();
            }
            return;
        }

        saveInProgress = true;
        hidden.value = JSON.stringify(collect());
        try { localStorage.removeItem(autosaveKey); } catch (e) {}
        isDirty = false;
    });

    // Slug preview.
    var slugInput = document.getElementById('slug');
    var titleInput = document.getElementById('title');
    var preview = document.getElementById('slug-preview');
    function slugify(v) {
        return v.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    }
    function updatePreview() {
        var v = slugInput.value.trim() ? slugify(slugInput.value) : slugify(titleInput.value);
        preview.textContent = v || 'your-slug';
    }
    slugInput.addEventListener('input', updatePreview);
    titleInput.addEventListener('input', updatePreview);

    document.querySelectorAll('#page-form input, #page-form textarea, #page-form select').forEach(function (node) {
        node.addEventListener('input', function () {
            updatePreview();
            updateCharCounters();
            scheduleAutosave();
            renderChecklist();
            updateDirtyState();
        });
        node.addEventListener('change', function () {
            updateCharCounters();
            scheduleAutosave();
            renderChecklist();
            updateDirtyState();
        });
    });

    try {
        var draft = localStorage.getItem(autosaveKey);
        var currentState = snapshotState();
        baselineSnapshot = currentState;
        if (draft && draft !== currentState && restoreBanner) {
            restoreBanner.classList.add('show');
        }

        if (restoreDraftBtn) {
            restoreDraftBtn.addEventListener('click', function () {
                var raw = localStorage.getItem(autosaveKey);
                if (!raw) {
                    return;
                }
                try {
                    applyState(JSON.parse(raw));
                    baselineSnapshot = snapshotState();
                    renderChecklist();
                    updateSimpleModeNote();
                    updateCharCounters();
                    if (restoreBanner) {
                        restoreBanner.classList.remove('show');
                    }
                    if (autosaveStatus) {
                        autosaveStatus.textContent = 'Draft restored.';
                    }
                } catch (e) {
                    if (autosaveStatus) {
                        autosaveStatus.textContent = 'Could not restore saved draft.';
                    }
                }
            });
        }

        if (discardDraftBtn) {
            discardDraftBtn.addEventListener('click', function () {
                localStorage.removeItem(autosaveKey);
                if (restoreBanner) {
                    restoreBanner.classList.remove('show');
                }
                if (autosaveStatus) {
                    autosaveStatus.textContent = 'Saved draft discarded.';
                }
            });
        }
    } catch (e) {
        baselineSnapshot = snapshotState();
    }

    window.addEventListener('beforeunload', function (event) {
        if (saveInProgress) {
            return;
        }
        updateDirtyState();
        if (!isDirty) {
            return;
        }
        event.preventDefault();
        event.returnValue = '';
    });

    renderChecklist();
    updateSimpleModeNote();
    updateBlocksEmptyState();
    updateCharCounters();
    baselineSnapshot = snapshotState();
    if (autosaveStatus) {
        autosaveStatus.textContent = 'Autosave active.';
    }
})();
</script>
<?php
admin_footer();
