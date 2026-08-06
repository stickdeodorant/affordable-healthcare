<?php
/**
 * Output escaping, slugs, and an allowlist HTML sanitizer for marketer rich-text.
 */

/**
 * HTML-escape a scalar for safe output.
 */
function cms_e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
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
 * Strips scripts, event handlers, styles, and neutralizes dangerous URLs.
 */
function cms_sanitize_html(string $html): string {
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $allowedTags = [
        'p', 'br', 'h2', 'h3', 'h4', 'ul', 'ol', 'li',
        'a', 'strong', 'em', 'b', 'i', 'blockquote',
    ];
    $allowedAttrs = [
        'a' => ['href', 'title'],
    ];

    $dom = new DOMDocument('1.0', 'UTF-8');
    $prev = libxml_use_internal_errors(true);
    // Wrap so we can parse a fragment as UTF-8 without adding <html>/<body> noise on output.
    $wrapped = '<?xml encoding="UTF-8"><div id="cms-frag">' . $html . '</div>';
    $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);

    $frag = $dom->getElementById('cms-frag');
    if (!$frag) {
        return '';
    }

    cms_sanitize_node($frag, $allowedTags, $allowedAttrs);

    $out = '';
    foreach (iterator_to_array($frag->childNodes) as $child) {
        $out .= $dom->saveHTML($child);
    }
    return trim($out);
}

/**
 * Recursively enforce the tag/attribute allowlist on a DOM subtree.
 * Disallowed elements are unwrapped (children kept); disallowed attributes removed.
 */
function cms_sanitize_node(DOMNode $node, array $allowedTags, array $allowedAttrs): void {
    // Snapshot children first: the list mutates as we unwrap/remove.
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

        // Recurse before deciding, so kept-but-disallowed wrappers still surface clean children.
        cms_sanitize_node($child, $allowedTags, $allowedAttrs);

        if (!in_array($tag, $allowedTags, true)) {
            // Unwrap: move children up in place, then drop the element.
            while ($child->firstChild) {
                $node->insertBefore($child->firstChild, $child);
            }
            $node->removeChild($child);
            continue;
        }

        // Strip disallowed attributes.
        $permitted = $allowedAttrs[$tag] ?? [];
        foreach (iterator_to_array($child->attributes) as $attr) {
            $name = strtolower($attr->name);
            if (!in_array($name, $permitted, true)) {
                $child->removeAttribute($attr->name);
                continue;
            }
            if ($name === 'href' && !cms_url_is_safe($attr->value)) {
                $child->removeAttribute($attr->name);
            }
        }

        // Harden external links.
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
    // Relative or root-relative paths and fragments.
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
