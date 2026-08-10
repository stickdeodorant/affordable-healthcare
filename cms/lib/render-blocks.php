<?php
/**
 * Block renderer for CMS pages. Emits safe, Bootstrap-styled markup for each
 * supported block type. All output is escaped or allowlist-sanitized.
 */

require_once __DIR__ . '/sanitize.php';
require_once __DIR__ . '/legacy-sections.php';

/**
 * Render an ordered list of body blocks.
 */
function cms_render_blocks(array $blocks, array $page): void {
    foreach ($blocks as $block) {
        if (!is_array($block) || empty($block['type'])) {
            continue;
        }
        switch ($block['type']) {
            case 'rich_text':
                cms_block_rich_text($block);
                break;
            case 'legacy_html':
                cms_block_legacy_html($block);
                break;
            case 'snapshot_html':
                cms_block_snapshot_html($block);
                break;
            case 'cta_banner':
                cms_block_cta_banner($block, $page);
                break;
            case 'image':
                cms_block_image($block);
                break;
            case 'faq_list':
                cms_block_faq_list($block);
                break;
            case 'legacy_section':
                cms_block_legacy_section($block);
                break;
            // 'hero' is rendered by inc/hero.php; ignore here.
        }
    }
}

function cms_block_legacy_section(array $block): void {
    $key = trim((string)($block['section_key'] ?? ''));
    if ($key === '' || !cms_legacy_section_exists($key)) {
        return;
    }
    cms_render_legacy_section($key);
}

function cms_block_rich_text(array $block): void {
    $html = cms_sanitize_html((string)($block['html'] ?? ''));
    if ($html === '') {
        return;
    }
    echo '<section class="container py-4"><div class="row"><div class="col-lg-10 offset-lg-1 cms-richtext">'
        . $html
        . '</div></div></section>';
}

function cms_block_legacy_html(array $block): void {
    $html = cms_sanitize_legacy_html((string)($block['html'] ?? ''));
    if ($html === '') {
        return;
    }
    echo $html;
}

function cms_block_snapshot_html(array $block): void {
    $html = (string)($block['html'] ?? '');
    if (trim($html) === '') {
        return;
    }
    echo $html;
}

function cms_block_cta_banner(array $block, array $page): void {
    $text = trim((string)($block['text'] ?? ''));
    $href = trim((string)($block['href'] ?? ($page['cta_href'] ?? '')));
    $label = trim((string)($block['cta_text'] ?? ($page['cta_text'] ?? 'Get Started')));
    if (!cms_url_is_safe($href)) {
        $href = '/multi-quote/';
    }
    echo '<section class="container-fluid bg-primary text-white py-5 my-4"><div class="container text-center">';
    if ($text !== '') {
        echo '<h2 class="h1 mb-4">' . cms_e($text) . '</h2>';
    }
    echo '<a class="button bg-secondary text-white scale-4x" href="' . cms_e($href) . '">' . cms_e($label) . '</a>';
    echo '</div></section>';
}

function cms_block_image(array $block): void {
    $src = trim((string)($block['src'] ?? ''));
    $alt = trim((string)($block['alt'] ?? ''));
    // Only allow site-relative image paths (uploads live under /img/...).
    if ($src === '' || !preg_match('#^/img/[A-Za-z0-9_\-/.]+\.(jpg|jpeg|png|webp|gif|svg)$#i', $src)) {
        return;
    }
    echo '<section class="container py-4 text-center"><img class="img-fluid" src="'
        . cms_e($src) . '" alt="' . cms_e($alt) . '"></section>';
}

function cms_block_faq_list(array $block): void {
    $items = $block['items'] ?? [];
    if (!is_array($items) || !$items) {
        return;
    }
    echo '<section class="container py-4"><div class="row"><div class="col-lg-10 offset-lg-1">';
    $heading = trim((string)($block['heading'] ?? ''));
    if ($heading !== '') {
        echo '<h2 class="h1 mb-4 text-center">' . cms_e($heading) . '</h2>';
    }
    echo '<div class="cms-faq">';
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $q = trim((string)($item['q'] ?? ''));
        $a = cms_sanitize_html((string)($item['a'] ?? ''));
        if ($q === '' || $a === '') {
            continue;
        }
        echo '<div class="cms-faq-item mb-3"><h3 class="h5 mb-1">' . cms_e($q) . '</h3>'
            . '<div class="cms-faq-answer">' . $a . '</div></div>';
    }
    echo '</div></div></div></section>';
}
