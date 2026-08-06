<?php
/**
 * CLI: seed a demo published CMS page so the read path can be verified.
 *
 * Usage (from project root):
 *   php cms/cli/seed-demo-page.php
 *
 * Creates/updates a published page at slug "welcome-demo". Idempotent.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script is CLI-only.\n");
}

require_once __DIR__ . '/../bootstrap.php';

$slug = 'welcome-demo';
$body = [
    ['type' => 'rich_text', 'html' => '<h2>Affordable coverage, made simple</h2><p>This page is managed entirely from the CMS. Marketers can edit this copy, swap the hero, and publish without touching code.</p><p><a href="/multi-quote/">Start your quote</a> in under a minute.</p>'],
    ['type' => 'cta_banner', 'text' => 'See plans available in your area', 'cta_text' => 'Get My Quotes', 'href' => '/multi-quote/'],
    ['type' => 'faq_list', 'heading' => 'Common questions', 'items' => [
        ['q' => 'Is this comprehensive coverage?', 'a' => '<p>Ask the licensed agent to confirm plan details and whether the plan meets your needs.</p>'],
        ['q' => 'How much does it cost?', 'a' => '<p>Pricing and eligibility vary by state and personal circumstances.</p>'],
    ]],
];

$bodyJson = json_encode($body, JSON_UNESCAPED_SLASHES);

$result = cms_write(
    "INSERT INTO cms_pages
        (slug, title, meta_description, theme, status, hero_headline, hero_subtitle,
         cta_text, cta_href, body_json, created_by, updated_by, published_at)
     VALUES (?, ?, ?, ?, 'published', ?, ?, ?, ?, ?, 'seed', 'seed', NOW())
     ON DUPLICATE KEY UPDATE
        title = VALUES(title),
        meta_description = VALUES(meta_description),
        theme = VALUES(theme),
        status = 'published',
        hero_headline = VALUES(hero_headline),
        hero_subtitle = VALUES(hero_subtitle),
        cta_text = VALUES(cta_text),
        cta_href = VALUES(cta_href),
        body_json = VALUES(body_json),
        updated_by = 'seed',
        published_at = NOW()",
    'sssssssss',
    [
        $slug,
        'Welcome — Affordable Healthcare',
        'A CMS-managed landing page demo for Affordable Healthcare.',
        'golden-trust',
        'Find Affordable Healthcare',
        'You may qualify for a plan with no monthly cost',
        'Get My Quotes',
        '/multi-quote/',
        $bodyJson,
    ]
);

if ($result === false) {
    fwrite(STDERR, "Failed to seed demo page (check DB connection).\n");
    exit(1);
}

fwrite(STDOUT, "Seeded published page at /{$slug}\n");
exit(0);
