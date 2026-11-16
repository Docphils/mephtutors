<?php

namespace Tests\Unit;

use App\Services\BlogContentSanitizer;
use PHPUnit\Framework\TestCase;

class BlogContentSanitizerTest extends TestCase
{
    public function test_old_trix_paragraphs_keep_their_boundaries(): void
    {
        $sanitizer = new BlogContentSanitizer;
        $this->assertSame(
            '<p>First<br>line</p><p>Second paragraph</p><blockquote><p>A quote</p></blockquote>',
            $sanitizer->sanitize('<div>First<br>line</div><div>Second paragraph</div><blockquote><div>A quote</div></blockquote>')
        );
        $this->assertSame('<h2>Heading</h2><p>Paragraph</p>', $sanitizer->sanitize('<div><h2>Heading</h2><p>Paragraph</p></div>'));
    }

    public function test_safe_formatting_is_kept_while_unsafe_css_and_attributes_are_removed(): void
    {
        $sanitizer = new BlogContentSanitizer;
        $html = '<h5 onclick="alert(1)" style="text-align: right; position: fixed; background-image: url(javascript:alert(1))">Heading</h5>'
            .'<p><span style="font-family: &quot;Times New Roman&quot;; font-size: 18px; color: rgb(14, 116, 144); background-color: #fff; width: 100vw; behavior: url(bad)">Text</span></p>'
            .'<span style="font-size: 999px; color: expression(alert(1)); font-family: url(bad)">Unsafe styles</span>'
            .'<a href="javascript:alert(1)" onmouseover="alert(1)">Bad link</a><script>alert(1)</script>';
        $expected = '<h5 style="text-align: right">Heading</h5>'
            .'<p><span style="font-family: &quot;Times New Roman&quot;; font-size: 18px; color: rgb(14, 116, 144); background-color: #fff">Text</span></p>'
            .'<span>Unsafe styles</span><a>Bad link</a>';
        $this->assertSame($expected, $sanitizer->sanitize($html));
        $this->assertSame($expected, $sanitizer->sanitize($expected));
    }
}
