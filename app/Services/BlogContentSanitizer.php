<?php

namespace App\Services;

use DOMDocument;
use DOMNode;
use DOMText;
use DOMXPath;

class BlogContentSanitizer
{
    private const ALLOWED_TAGS = [
        'a', 'b', 'blockquote', 'br', 'code', 'del', 'em', 'h1', 'h2', 'h3', 'h4', 'hr',
        'i', 'li', 'ol', 'p', 'pre', 's', 'strong', 'u', 'ul',
    ];

    private const DROP_CONTENT_TAGS = [
        'iframe', 'object', 'script', 'style', 'svg', 'template',
    ];

    public function sanitize(string $html): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorSetting = libxml_use_internal_errors(true);

        try {
            $document->loadHTML(
                '<!doctype html><html><head><meta charset="utf-8"></head><body><div id="blog-content">'.$html.'</div></body></html>',
                LIBXML_NONET
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorSetting);
        }

        $nodes = (new DOMXPath($document))->query('//*[@id="blog-content"]');
        $root = $nodes === false ? null : $nodes->item(0);

        if ($root === null) {
            return '';
        }

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $this->sanitizeNode($child);
        }

        return $result;
    }

    private function sanitizeNode(DOMNode $node): string
    {
        if ($node instanceof DOMText) {
            return htmlspecialchars($node->nodeValue ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return '';
        }

        $tag = strtolower($node->nodeName);
        if (in_array($tag, self::DROP_CONTENT_TAGS, true)) {
            return '';
        }

        $children = '';
        foreach ($node->childNodes as $child) {
            $children .= $this->sanitizeNode($child);
        }

        if (! in_array($tag, self::ALLOWED_TAGS, true)) {
            return $children;
        }

        $attributes = '';
        if ($tag === 'a' && $node->attributes?->getNamedItem('href') !== null) {
            $href = trim($node->attributes->getNamedItem('href')->nodeValue ?? '');
            if ($this->isSafeLink($href)) {
                $attributes = ' href="'.htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'" rel="nofollow noopener noreferrer"';
                $title = $node->attributes->getNamedItem('title')?->nodeValue;
                if ($title !== null) {
                    $attributes .= ' title="'.htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"';
                }
            }
        }

        if (in_array($tag, ['br', 'hr'], true)) {
            return '<'.$tag.'>';
        }

        return '<'.$tag.$attributes.'>'.$children.'</'.$tag.'>';
    }

    private function isSafeLink(string $href): bool
    {
        if ($href === '' || str_contains($href, '\\') || preg_match('/[\x00-\x20]/', $href)) {
            return false;
        }

        if (str_starts_with($href, '/') && ! str_starts_with($href, '//')) {
            return true;
        }

        if (str_starts_with($href, '#')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https', 'mailto'], true);
    }
}
