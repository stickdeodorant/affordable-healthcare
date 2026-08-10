<?php
/**
 * Output escaping, slugs, and HTML sanitization helpers.
 */

/**
 * HTML-escape a scalar for safe output.
 */
function cms_e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape a hero headline while keeping a tiny inline whitelist so marketers can
 * highlight part of it (<span class="text-secondary">), add a line break, or use
 * basic emphasis. Everything else is escaped first, so the result is XSS-safe:
 * only these exact, fixed constructs are restored (no arbitrary attributes).
 */
function cms_sanitize_headline($value): string {
    $out = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    $out = preg_replace('/&lt;span\s+class=(?:&quot;|&#0?39;)?text-secondary(?:&quot;|&#0?39;)?&gt;/i', '<span class="text-secondary">', $out);
    $out = str_ireplace('&lt;/span&gt;', '</span>', $out);
    $out = preg_replace('/&lt;br\s*\/?&gt;/i', '<br>', $out);
    $out = preg_replace('#&lt;(/?)(strong|em|b|i)&gt;#i', '<$1$2>', $out);
    return $out;
}

/**
 * Normalize an arbitrary string into a URL-safe slug.
 */
function cms_slug(string $value): string {
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    $value = trim($value, '-');
    return $value;
}

/**
 * Sanitize marketer-supplied HTML down to a safe allowlist of tags/attributes.
 */
function cms_sanitize_html(string $html): string {
    return cms_sanitize_html_fragment($html, [
        'p', 'br', 'h2', 'h3', 'h4', 'ul', 'ol', 'li',
        'a', 'strong', 'em', 'b', 'i', 'blockquote',
    ], [
        'a' => ['href', 'title'],
    ]);
}

/**
 * Sanitize imported legacy landing-page HTML with a broader structural allowlist.
 */
function cms_sanitize_legacy_html(string $html): string {
    return cms_sanitize_html_fragment($html, [
        'div', 'section', 'header', 'footer', 'main', 'article', 'aside', 'nav',
        'span', 'p', 'br', 'hr', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'a', 'strong', 'em', 'b', 'i', 'blockquote',
        'img', 'picture', 'source', 'figure', 'figcaption',
        'button', 'form', 'label', 'input', 'textarea', 'select', 'option',
        'svg', 'path', 'circle', 'g', 'defs', 'polyline', 'rect', 'line', 'ellipse', 'text', 'tspan', 'use',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'small', 'sup', 'sub', 'code', 'pre',
    ], []);
}

/**
 * Shared fragment sanitizer used by the stricter rich-text and broader legacy blocks.
 */
function cms_sanitize_html_fragment(string $html, array $allowedTags, array $allowedAttrsByTag): string {
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $dom = new DOMDocument('1.0', 'UTF-8');
    $prev = libxml_use_internal_errors(true);
    $wrapped = '<?xml encoding="UTF-8"><div id="cms-frag">' . $html . '</div>';
    $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);

    $frag = $dom->getElementById('cms-frag');
    if (!$frag) {
        return '';
    }

    cms_sanitize_node($frag, $allowedTags, $allowedAttrsByTag);

    $out = '';
    foreach (iterator_to_array($frag->childNodes) as $child) {
        $out .= $dom->saveHTML($child);
    }
    return trim($out);
}

/**
 * Recursively enforce the tag/attribute allowlist on a DOM subtree.
 * Disallowed elements are unwrapped (children kept); unsafe attributes removed.
 */
function cms_sanitize_node(DOMNode $node, array $allowedTags, array $allowedAttrs): void {
    foreach (iterator_to_array($node->childNodes) as $child) {
        if ($child->nodeType === XML_TEXT_NODE) {
            continue;
        }
        if ($child->nodeType !== XML_ELEMENT_NODE) {
            $child->parentNode->removeChild($child);
            continue;
        }

        /** @var DOMElement $child */
        $tag = strtolower($child->tagName);
        cms_sanitize_node($child, $allowedTags, $allowedAttrs);

        if (!in_array($tag, $allowedTags, true)) {
            while ($child->firstChild) {
                $node->insertBefore($child->firstChild, $child);
            }
            $node->removeChild($child);
            continue;
        }

        $permitted = $allowedAttrs[$tag] ?? [];
        foreach (iterator_to_array($child->attributes) as $attr) {
            $name = strtolower($attr->name);

            if (str_starts_with($name, 'on')) {
                $child->removeAttribute($attr->name);
                continue;
            }

            if (in_array($name, ['href', 'src', 'xlink:href', 'action', 'formaction', 'poster'], true) && !cms_url_is_safe($attr->value)) {
                $child->removeAttribute($attr->name);
                continue;
            }

            if (in_array($name, $permitted, true)) {
                continue;
            }

            if (str_starts_with($name, 'aria-') || str_starts_with($name, 'data-')) {
                continue;
            }

            $child->removeAttribute($attr->name);
        }

        if ($tag === 'a' && $child->hasAttribute('href')) {
            $child->setAttribute('rel', 'noopener nofollow');
        }
    }
}

/**
 * Allow only http(s), mailto, tel, and root/relative URLs; block javascript:, data:, etc.
 */
function cms_url_is_safe(string $url): bool {
    $url = trim($url);
    if ($url === '') {
        return false;
    }
    if (preg_match('#^(https?:|mailto:|tel:)#i', $url)) {
        return true;
    }
    return (bool)preg_match('#^(/|\./|\.\./|\#)#', $url);
}

/**
 * Decode a JSON column into an array, tolerating null/invalid input.
 */
function cms_json_decode(?string $json): array {
    if ($json === null || $json === '') {
        return [];
    }
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}
