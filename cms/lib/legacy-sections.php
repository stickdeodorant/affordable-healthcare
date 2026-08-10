<?php
/**
 * Legacy pre-CMS section catalog and safe rendering helpers.
 */

function cms_legacy_section_catalog(): array {
    return [
        'cta_banner' => [
            'label' => 'Legacy CTA Banner',
            'include' => 'inc/sections/cta-banner.php',
            'description' => 'Primary CTA section from the pre-CMS home page.',
        ],
        'steps_tabs' => [
            'label' => 'Legacy Steps (Tabs)',
            'include' => 'inc/sections/steps.php',
            'description' => 'Three-step tabbed "Follow a few steps" section from the landing pages.',
        ],
        'steps_cards' => [
            'label' => 'Legacy Steps (Cards)',
            'include' => 'inc/sections/steps2.php',
            'description' => 'Three-step educational section with icon cards.',
        ],
        'plane_banner' => [
            'label' => 'Legacy Plane Banner',
            'include' => 'inc/sections/plane-banner.php',
            'description' => 'SVG banner section with curved text path treatment.',
        ],
        'faq_accordion' => [
            'label' => 'Legacy FAQ Accordion',
            'include' => 'inc/sections/faq-section.php',
            'description' => 'Accordion-based FAQ include from the legacy layout.',
        ],
        'testimonials_slider' => [
            'label' => 'Legacy Testimonials Slider',
            'include' => 'inc/testimonials.php',
            'description' => 'Rotating testimonials block used in legacy pages.',
        ],
        'carrier_logos' => [
            'label' => 'Legacy Carrier Logos',
            'include' => 'inc/aff-logos.php',
            'description' => 'Insurance carrier logo rail include from legacy pages.',
        ],
    ];
}

function cms_legacy_section_exists(string $key): bool {
    $catalog = cms_legacy_section_catalog();
    return isset($catalog[$key]);
}

function cms_legacy_section_label(string $key): string {
    $catalog = cms_legacy_section_catalog();
    return (string)($catalog[$key]['label'] ?? $key);
}

function cms_legacy_section_options(): array {
    $catalog = cms_legacy_section_catalog();
    $out = [];
    foreach ($catalog as $key => $cfg) {
        $out[] = [
            'key' => (string)$key,
            'label' => (string)($cfg['label'] ?? $key),
            'description' => (string)($cfg['description'] ?? ''),
        ];
    }
    return $out;
}

function cms_render_legacy_section(string $key): void {
    $catalog = cms_legacy_section_catalog();
    if (!isset($catalog[$key])) {
        return;
    }
    $rel = (string)$catalog[$key]['include'];
    if ($rel === '' || strpos($rel, '..') !== false) {
        return;
    }
    $file = CMS_APP_ROOT . '/' . $rel;
    if (!is_file($file)) {
        return;
    }
    // Legacy partials read page-level globals ($sitename, $phone, ...) set by inc/header.php.
    extract($GLOBALS, EXTR_SKIP);
    include $file;
}
