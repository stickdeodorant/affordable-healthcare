<?php
/**
 * CLI: seed a STRUCTURED, field-by-field editable preview of the aetna page.
 *
 * This does NOT touch the live /aetna page. It creates a separate published
 * page at /aetna-structured-preview so you can compare the two editing models:
 *   - /aetna                     -> exact snapshot (one raw-HTML block)
 *   - /aetna-structured-preview  -> native CMS blocks (each field editable)
 *
 * Usage (from project root):
 *   php cms/cli/seed-structured-preview.php          # create/update the preview
 *   php cms/cli/seed-structured-preview.php --remove  # delete the preview page
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script is CLI-only.\n");
}

require_once __DIR__ . '/../bootstrap.php';

$slug = 'aetna-structured-preview';

if (in_array('--remove', $argv, true)) {
    cms_write('DELETE FROM cms_pages WHERE slug = ?', 's', [$slug]);
    fwrite(STDOUT, "Removed preview page /{$slug}\n");
    exit(0);
}

// Each block below maps to editable fields in the composer (Simple/Advanced).
$body = [
    // Steps section -> editable rich text (headline + three steps).
    [
        'type' => 'rich_text',
        'html' =>
            '<h2 class="text-center">Follow a few steps for affordable healthcare</h2>'
            . '<ol>'
            . '<li><strong>Enter your ZIP code</strong> to request healthcare quote information in your area.</li>'
            . '<li><strong>Submit your information</strong> so an affiliate agency or representative may contact you about health insurance options.</li>'
            . '<li><strong>Ask questions before you enroll</strong> about benefits, prescriptions, doctors, maximum out-of-pocket exposure, and whether the plan is comprehensive coverage.</li>'
            . '</ol>',
    ],
    // CTA banner -> editable headline + button text/link.
    [
        'type' => 'cta_banner',
        'text' => 'Find affordable healthcare options',
        'cta_text' => 'Find Plans',
        'href' => '/multi-quote/',
    ],
    // FAQ -> each question/answer is an editable field.
    [
        'type' => 'faq_list',
        'heading' => 'Healthcare FAQ',
        'items' => [
            [
                'q' => 'When can I switch to a more affordable plan?',
                'a' => '<p>You can switch to a new individual healthcare plan during open enrollment. Certain qualifying events &mdash; such as having a baby, losing your current coverage, moving counties, or getting married &mdash; can make you eligible for a 60-day special enrollment period. Otherwise, you can switch your plan starting November 1st.</p>',
            ],
            [
                'q' => 'HMOs (Health Maintenance Organizations)',
                'a' => '<p>Members are usually restricted to doctors, providers, or hospitals on the plan&rsquo;s list, and generally do not cover out-of-network care except in emergencies. The plan may require you to live or work in a specific service area to be eligible.</p>',
            ],
            [
                'q' => 'PPOs (Preferred Provider Organizations)',
                'a' => '<p>Coverage usually contracts with medical providers to create a network of participating providers. You typically pay less when using in-network doctors and hospitals, and more when you seek care outside the network.</p>',
            ],
            [
                'q' => 'POS (Point of Service) Plans',
                'a' => '<p>You pay less when you use doctors, hospitals, and other health care providers that belong to the plan&rsquo;s network.</p>',
            ],
        ],
    ],
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
        'Aetna Health Plans (Structured Preview)',
        'Compare affordable Aetna health plans. You may qualify for a plan with no monthly cost.',
        'default',
        'Find Affordable Healthcare!',
        'You may qualify for a plan with no monthly cost',
        'Find Plans',
        '/multi-quote/',
        $bodyJson,
    ]
);

if ($result === false) {
    fwrite(STDERR, "Failed to seed preview page (check DB connection).\n");
    exit(1);
}

fwrite(STDOUT, "Seeded structured preview at /{$slug}\n");
fwrite(STDOUT, "Compare:  /aetna  (snapshot)   vs   /{$slug}  (structured, field-editable)\n");
fwrite(STDOUT, "Remove later with: php cms/cli/seed-structured-preview.php --remove\n");
exit(0);
