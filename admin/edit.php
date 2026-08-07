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
$id = (int)($_GET['id'] ?? 0);
$errors = [];

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
        }
    }
    return $clean;
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
    'body_json' => $page['body_json'] ?? '[]',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_csrf_require();

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
    $form['hero_headline'] = trim((string)($_POST['hero_headline'] ?? ''));
    $form['hero_subtitle'] = trim((string)($_POST['hero_subtitle'] ?? ''));
    $form['cta_text'] = trim((string)($_POST['cta_text'] ?? ''));
    $ctaHref = trim((string)($_POST['cta_href'] ?? ''));
    $form['cta_href'] = ($ctaHref === '' || cms_url_is_safe($ctaHref)) ? $ctaHref : '';

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
    // Slug must not collide with a physical file/dir served directly by Apache.
    if ($form['slug'] !== '') {
        $physical = CMS_APP_ROOT . '/' . $form['slug'];
        if (is_file($physical . '.php') || is_dir($physical)) {
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

    if (!$errors) {
        $user = cms_current_user()['email'];
        $publishedAt = null;
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
                    body_json = ?, updated_by = ?, published_at = ?
                 WHERE id = ?',
                'sssssssssssssssi',
                [
                    $form['slug'], $form['title'], $form['meta_description'], $form['canonical'], $form['og_image'],
                    $form['template'], $form['theme'], $form['status'], $form['hero_headline'], $form['hero_subtitle'], $form['cta_text'], $form['cta_href'],
                    $form['body_json'], $user, $publishedAt, $id,
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
                     hero_headline, hero_subtitle, cta_text, cta_href, body_json, created_by, updated_by, published_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                'ssssssssssssssss',
                [
                    $form['slug'], $form['title'], $form['meta_description'], $form['canonical'], $form['og_image'],
                    $form['template'], $form['theme'], $form['status'],
                    $form['hero_headline'], $form['hero_subtitle'], $form['cta_text'], $form['cta_href'],
                    $form['body_json'], $user, $user, $publishedAt,
                ]
            );
        }

        // Revision snapshot for rollback.
        if ($id > 0) {
            $snapshot = cms_select_one('SELECT * FROM cms_pages WHERE id = ? LIMIT 1', 'i', [$id]);
            if ($snapshot) {
                cms_write(
                    'INSERT INTO cms_page_revisions (page_id, snapshot_json, editor, note) VALUES (?, ?, ?, ?)',
                    'isss',
                    [$id, json_encode($snapshot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $user, 'save']
                );
            }
            cms_audit('page_save', 'cms_page', $id, ['slug' => $form['slug'], 'status' => $form['status']]);
            admin_flash_set('success', 'Page saved.');
            header('Location: ' . $base . '/edit.php?id=' . $id);
            exit;
        }
        $errors[] = 'Could not save the page. Please try again.';
    }
}

$isNew = $id === 0;
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

<form method="post" action="<?= cms_e($base) ?>/edit.php<?= $isNew ? '' : '?id=' . (int)$id ?>" id="page-form">
    <?= cms_csrf_field() ?>

    <div class="card">
        <div class="row">
            <div class="col">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?= cms_e($form['title']) ?>" maxlength="255">
                <div class="hint">Used for the browser tab and as the hero fallback.</div>
            </div>
            <div class="col">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" value="<?= cms_e($form['slug']) ?>" maxlength="191" placeholder="auto from title">
                <div class="hint">Public URL: <code>/<span id="slug-preview"><?= cms_e($form['slug'] !== '' ? $form['slug'] : 'your-slug') ?></span></code></div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <label for="template">Template</label>
                <select id="template" name="template">
                    <?php foreach ($GLOBALS['CMS_PAGE_TEMPLATES'] as $templateKey => $templateLabel): ?>
                        <option value="<?= cms_e($templateKey) ?>" <?= $form['template'] === $templateKey ? 'selected' : '' ?>><?= cms_e($templateLabel) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="hint">Matches the shared landing-page template families used by the root pages.</div>
            </div>
            <div class="col">
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
                        <option value="<?= cms_e($st) ?>" <?= $form['status'] === $st ? 'selected' : '' ?>><?= cms_e(ucfirst($st)) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="hint">Only <strong>published</strong> pages are publicly visible.</div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2 style="font-size:1.05rem;margin:0 0 .5rem;">Hero</h2>
        <div class="row">
            <div class="col">
                <label for="hero_headline">Headline</label>
                <input type="text" id="hero_headline" name="hero_headline" value="<?= cms_e($form['hero_headline']) ?>" maxlength="255">
            </div>
            <div class="col">
                <label for="hero_subtitle">Subtitle</label>
                <input type="text" id="hero_subtitle" name="hero_subtitle" value="<?= cms_e($form['hero_subtitle']) ?>" maxlength="255">
            </div>
        </div>
        <div class="row">
            <div class="col">
                <label for="cta_text">Default CTA text</label>
                <input type="text" id="cta_text" name="cta_text" value="<?= cms_e($form['cta_text']) ?>" maxlength="120">
            </div>
            <div class="col">
                <label for="cta_href">Default CTA link</label>
                <input type="text" id="cta_href" name="cta_href" value="<?= cms_e($form['cta_href']) ?>" maxlength="255" placeholder="/multi-quote/">
            </div>
        </div>
    </div>

    <div class="card">
        <h2 style="font-size:1.05rem;margin:0 0 .5rem;">Content blocks</h2>
        <div id="blocks"></div>
        <div class="actions" style="margin-top:.75rem;">
            <button type="button" class="btn btn-ghost btn-sm" data-add="rich_text">+ Rich text</button>
            <button type="button" class="btn btn-ghost btn-sm" data-add="cta_banner">+ CTA banner</button>
            <button type="button" class="btn btn-ghost btn-sm" data-add="image">+ Image</button>
            <button type="button" class="btn btn-ghost btn-sm" data-add="faq_list">+ FAQ list</button>
        </div>
        <input type="hidden" name="body_json" id="body_json" value="">
    </div>

    <div class="card">
        <h2 style="font-size:1.05rem;margin:0 0 .5rem;">SEO</h2>
        <label for="meta_description">Meta description</label>
        <textarea id="meta_description" name="meta_description" maxlength="320" style="min-height:70px;"><?= cms_e($form['meta_description']) ?></textarea>
        <div class="row">
            <div class="col">
                <label for="canonical">Canonical URL</label>
                <input type="text" id="canonical" name="canonical" value="<?= cms_e($form['canonical']) ?>" maxlength="255">
            </div>
            <div class="col">
                <label for="og_image">OG image path</label>
                <input type="text" id="og_image" name="og_image" value="<?= cms_e($form['og_image']) ?>" maxlength="255" placeholder="/img/...">
            </div>
        </div>
    </div>

    <div class="actions">
        <button type="submit" class="btn btn-primary">Save page</button>
        <a class="btn btn-ghost" href="<?= cms_e($base) ?>/">Cancel</a>
    </div>
</form>

<script>
(function () {
    var initial = <?= json_encode(cms_json_decode($form['body_json']), JSON_UNESCAPED_SLASHES) ?>;
    var container = document.getElementById('blocks');
    var hidden = document.getElementById('body_json');
    var form = document.getElementById('page-form');

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

    function makeCard(type, title) {
        var card = el('div', { 'class': 'block-card', 'data-type': type });
        var head = el('div', { 'class': 'block-head' });
        head.appendChild(el('span', { 'class': 'block-type' }, title));
        var controls = el('div', { 'class': 'actions' });
        var up = el('button', { type: 'button', 'class': 'btn btn-ghost btn-sm' }, '&uarr;');
        var down = el('button', { type: 'button', 'class': 'btn btn-ghost btn-sm' }, '&darr;');
        var del = el('button', { type: 'button', 'class': 'btn btn-danger btn-sm' }, 'Remove');
        up.onclick = function () { if (card.previousElementSibling) { container.insertBefore(card, card.previousElementSibling); } };
        down.onclick = function () { if (card.nextElementSibling) { container.insertBefore(card.nextElementSibling, card); } };
        del.onclick = function () { card.remove(); };
        controls.appendChild(up); controls.appendChild(down); controls.appendChild(del);
        head.appendChild(controls);
        card.appendChild(head);
        return card;
    }

    function renderBlock(block) {
        var type = block.type;
        var card;
        if (type === 'rich_text') {
            card = makeCard(type, 'Rich text');
            var ta = el('textarea', { 'data-k': 'html' });
            ta.value = block.html || '';
            card.appendChild(fieldRow('HTML (allowed: p, h2-h4, ul/ol/li, a, strong, em, blockquote)', ta));
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
            if (card) { container.appendChild(card); }
        });
    });

    (initial || []).forEach(function (block) {
        var card = renderBlock(block);
        if (card) { container.appendChild(card); }
    });

    form.addEventListener('submit', function () {
        hidden.value = JSON.stringify(collect());
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
})();
</script>
<?php
admin_footer();
