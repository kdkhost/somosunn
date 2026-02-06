<?php

namespace App\Support;

class RichText
{
    /**
     * Renderiza texto rico de forma segura:
     * - Se detectar HTML, sanitiza e retorna HTML permitido.
     * - Se for texto puro, escapa e converte quebras de linha em <br>.
     */
    public static function toHtml($value): string
    {
        $value = (string) ($value ?? '');
        if (trim($value) === '') {
            return '';
        }

        // Detecta se parece HTML (ex.: <p>, <strong>, etc.)
        if (preg_match('/<\\s*\\/?\\s*[a-z][^>]*>/i', $value)) {
            return static::sanitizeHtml($value);
        }

        return nl2br(e($value));
    }

    public static function sanitizeHtml(string $html): string
    {
        $allowedTags = [
            'p', 'br',
            'strong', 'b', 'em', 'i', 'u',
            'ul', 'ol', 'li',
            'blockquote',
            'code', 'pre',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'hr',
            'span',
            'a',
        ];

        $dropTags = [
            'script', 'style', 'iframe', 'object', 'embed', 'link', 'meta',
            'form', 'input', 'button', 'textarea', 'select', 'option',
        ];

        $allowedAttributes = [
            'a' => ['href', 'title', 'target', 'rel'],
        ];

        try {
            $doc = new \DOMDocument();
            $old = libxml_use_internal_errors(true);

            $wrapped = '<div>' . $html . '</div>';
            $doc->loadHTML('<?xml encoding="utf-8" ?>' . $wrapped, \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);

            libxml_clear_errors();
            libxml_use_internal_errors($old);

            $root = $doc->getElementsByTagName('div')->item(0);
            if (!$root) {
                return e(strip_tags($html));
            }

            static::sanitizeNode($root, $allowedTags, $dropTags, $allowedAttributes);

            $out = '';
            foreach (iterator_to_array($root->childNodes) as $child) {
                $out .= $doc->saveHTML($child);
            }

            return $out;
        } catch (\Throwable $e) {
            // Fallback seguro: remove tags e preserva quebras
            return nl2br(e(strip_tags($html)));
        }
    }

    private static function sanitizeNode(\DOMNode $node, array $allowedTags, array $dropTags, array $allowedAttributes): void
    {
        if (!$node->hasChildNodes()) {
            return;
        }

        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                /** @var \DOMElement $el */
                $el = $child;
                $tag = strtolower($el->tagName);

                if (in_array($tag, $dropTags, true)) {
                    $node->removeChild($el);
                    continue;
                }

                if (!in_array($tag, $allowedTags, true)) {
                    static::unwrapElement($el);
                    continue;
                }

                // Limpa atributos
                $allowedForTag = $allowedAttributes[$tag] ?? [];
                $attrNames = [];
                if ($el->hasAttributes()) {
                    foreach ($el->attributes as $attr) {
                        $attrNames[] = $attr->name;
                    }
                }

                foreach ($attrNames as $name) {
                    if (!in_array(strtolower((string) $name), $allowedForTag, true)) {
                        $el->removeAttribute($name);
                    }
                }

                if ($tag === 'a') {
                    $href = trim((string) $el->getAttribute('href'));
                    if ($href !== '') {
                        $hrefLower = strtolower($href);
                        $isAllowed =
                            str_starts_with($hrefLower, 'http://')
                            || str_starts_with($hrefLower, 'https://')
                            || str_starts_with($hrefLower, '/')
                            || str_starts_with($hrefLower, '#')
                            || str_starts_with($hrefLower, 'mailto:')
                            || str_starts_with($hrefLower, 'tel:');

                        if (!$isAllowed || str_starts_with($hrefLower, 'javascript:')) {
                            $el->removeAttribute('href');
                        }
                    }

                    $target = trim((string) $el->getAttribute('target'));
                    if ($target !== '' && $target !== '_blank') {
                        $el->removeAttribute('target');
                    }

                    if ($el->getAttribute('target') === '_blank') {
                        $rel = trim((string) $el->getAttribute('rel'));
                        if ($rel === '') {
                            $el->setAttribute('rel', 'noopener noreferrer');
                        }
                    }
                }

                static::sanitizeNode($el, $allowedTags, $dropTags, $allowedAttributes);
            } elseif ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);
            } else {
                // mantém TEXT_NODE, etc.
            }
        }
    }

    private static function unwrapElement(\DOMElement $el): void
    {
        $parent = $el->parentNode;
        if (!$parent) {
            return;
        }

        while ($el->firstChild) {
            $parent->insertBefore($el->firstChild, $el);
        }
        $parent->removeChild($el);
    }
}

