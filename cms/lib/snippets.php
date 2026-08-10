<?php
/**
 * Approved snippet storage for non-technical editor insertion.
 */

function cms_snippets_file_path(): string {
    return CMS_ROOT . '/data/approved-snippets.json';
}

function cms_snippets_defaults(): array {
    return [
        'compliance_disclaimer' => [
            'label' => 'Compliance disclaimer',
            'blocks' => [[
                'type' => 'rich_text',
                'html' => '<p><strong>Important:</strong> Affordable Healthcare is a third-party lead generation website. We are not an insurance company and cannot guarantee coverage, pricing, or eligibility. Before enrolling, ask for written plan details.</p>',
            ]],
        ],
        'cta_bundle' => [
            'label' => 'CTA banner block',
            'blocks' => [[
                'type' => 'cta_banner',
                'text' => 'Find Affordable Healthcare Options',
                'href' => '/multi-quote/',
                'cta_text' => 'Get Quotes',
            ]],
        ],
        'faq_bundle' => [
            'label' => 'FAQ starter bundle',
            'blocks' => [[
                'type' => 'faq_list',
                'heading' => 'Common Questions',
                'items' => [
                    [
                        'q' => 'When can I change plans?',
                        'a' => '<p>You can usually make changes during open enrollment, or during a special enrollment period after a qualifying life event.</p>',
                    ],
                    [
                        'q' => 'How do I compare plans?',
                        'a' => '<p>Compare monthly cost, deductible, provider network, prescription coverage, and maximum out-of-pocket limits.</p>',
                    ],
                    [
                        'q' => 'What should I ask before enrolling?',
                        'a' => '<p>Ask whether your doctors are in-network, how prescriptions are covered, and whether the plan is comprehensive coverage.</p>',
                    ],
                ],
            ]],
        ],
    ];
}

function cms_snippets_load(): array {
    $defaults = cms_snippets_defaults();
    $path = cms_snippets_file_path();
    if (!is_file($path)) {
        return $defaults;
    }

    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return $defaults;
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return $defaults;
    }

    $clean = [];
    foreach ($decoded as $key => $cfg) {
        $snippetKey = preg_replace('/[^a-z0-9_]+/i', '_', (string)$key);
        $snippetKey = trim((string)$snippetKey, '_');
        if ($snippetKey === '' || !is_array($cfg)) {
            continue;
        }
        $blocks = $cfg['blocks'] ?? [];
        if (!is_array($blocks)) {
            continue;
        }
        $label = trim((string)($cfg['label'] ?? ''));
        if ($label === '') {
            $label = ucwords(str_replace('_', ' ', $snippetKey));
        }
        $clean[$snippetKey] = [
            'label' => $label,
            'blocks' => array_values($blocks),
        ];
    }

    return $clean ?: $defaults;
}

function cms_snippets_save(array $snippets): bool {
    $path = cms_snippets_file_path();
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        return false;
    }

    $json = json_encode($snippets, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (!is_string($json)) {
        return false;
    }

    return file_put_contents($path, $json) !== false;
}
