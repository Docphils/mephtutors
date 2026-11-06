<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class BlogArtworkGenerator
{
    public function store(string $title, string $category, int $runId): string
    {
        if (extension_loaded('gd')) {
            return $this->storePng($title, $category, $runId);
        }

        return $this->storeSvg($title, $category, $runId);
    }

    private function storePng(string $title, string $category, int $runId): string
    {
        $canvas = imagecreatetruecolor(600, 315);
        if ($canvas === false) {
            throw new \RuntimeException('The article artwork canvas could not be created.');
        }

        imagealphablending($canvas, true);
        for ($y = 0; $y < 315; $y++) {
            $ratio = $y / 314;
            $color = imagecolorallocate(
                $canvas,
                (int) round(11 + (8 * $ratio)),
                (int) round(42 + (84 * $ratio)),
                (int) round(71 + (68 * $ratio)),
            );
            imageline($canvas, 0, $y, 600, $y, $color);
        }

        $circle = imagecolorallocatealpha($canvas, 141, 227, 209, 98);
        imagefilledellipse($canvas, 520, 45, 230, 230, $circle);
        imagefilledellipse($canvas, 560, 315, 310, 310, $circle);
        $mint = imagecolorallocate($canvas, 184, 245, 232);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $muted = imagecolorallocate($canvas, 215, 244, 239);
        imagefilledrectangle($canvas, 44, 37, 88, 41, $mint);
        imagestring($canvas, 5, 44, 56, $this->asciiText(mb_strtoupper(mb_substr($category, 0, 40))), $mint);

        $titleLines = array_slice(explode("\n", wordwrap($this->asciiText($title), 45, "\n", true)), 0, 4);
        foreach ($titleLines as $index => $line) {
            imagestring($canvas, 5, 44, 108 + ($index * 28), $line, $white);
        }
        imagestring($canvas, 5, 44, 270, 'MephEd Insights', $muted);

        $image = imagecreatetruecolor(1200, 630);
        if ($image === false) {
            imagedestroy($canvas);
            throw new \RuntimeException('The article artwork image could not be created.');
        }
        imagecopyresized($image, $canvas, 0, 0, 0, 0, 1200, 630, 600, 315);
        ob_start();
        $written = imagepng($image);
        $contents = ob_get_clean();
        imagedestroy($image);
        imagedestroy($canvas);

        if (! $written || ! is_string($contents) || $contents === '') {
            throw new \RuntimeException('The article artwork image could not be encoded.');
        }

        $path = 'blog/illustrations/'.$runId.'.png';
        Storage::disk('public')->put($path, $contents);
        if (! Storage::disk('public')->exists($path)) {
            throw new \RuntimeException('The article artwork could not be stored.');
        }

        return $path;
    }

    private function storeSvg(string $title, string $category, int $runId): string
    {
        $path = 'blog/illustrations/'.$runId.'.svg';
        $lines = $this->wrap($title);
        $lineMarkup = '';
        foreach ($lines as $index => $line) {
            $lineMarkup .= '<text x="88" y="'.(258 + ($index * 68)).'" class="title">'.htmlspecialchars($line, ENT_QUOTES | ENT_XML1, 'UTF-8').'</text>';
        }

        $category = htmlspecialchars(mb_strtoupper(mb_substr($category, 0, 40)), ENT_QUOTES | ENT_XML1, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630" role="img" aria-label="'.htmlspecialchars($title, ENT_QUOTES | ENT_XML1, 'UTF-8').'">'
            .'<defs><linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#0B2A47"/><stop offset="1" stop-color="#087E8B"/></linearGradient></defs>'
            .'<rect width="1200" height="630" fill="url(#bg)"/><circle cx="1020" cy="90" r="220" fill="#ffffff" opacity=".07"/><circle cx="1060" cy="570" r="300" fill="#8DE3D1" opacity=".12"/>'
            .'<path d="M88 112h88" stroke="#8DE3D1" stroke-width="8" stroke-linecap="round"/>'
            .'<text x="88" y="170" class="eyebrow">'.$category.'</text>'
            .$lineMarkup
            .'<text x="88" y="548" class="brand">MephEd Insights</text>'
            .'<style>.eyebrow{font:700 22px Arial,sans-serif;letter-spacing:4px;fill:#b8f5e8}.title{font:700 54px Arial,sans-serif;fill:#fff}.brand{font:600 24px Arial,sans-serif;fill:#d7f4ef}</style>'
            .'</svg>';

        Storage::disk('public')->put($path, $svg);

        if (! Storage::disk('public')->exists($path)) {
            throw new \RuntimeException('The article artwork could not be stored.');
        }

        return $path;
    }

    private function asciiText(string $value): string
    {
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        return mb_substr($converted === false ? preg_replace('/[^\x20-\x7E]/', '', $value) : $converted, 0, 130);
    }

    /**
     * @return list<string>
     */
    private function wrap(string $title): array
    {
        $words = preg_split('/\s+/u', trim($title)) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $candidate = $line === '' ? $word : $line.' '.$word;
            if (mb_strlen($candidate) > 27 && $line !== '') {
                $lines[] = $line;
                $line = $word;

                continue;
            }
            $line = $candidate;
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        if (count($lines) > 4) {
            $lines = array_slice($lines, 0, 3);
            $lines[] = mb_substr($lines[2], 0, 23).'...';
        }

        return array_slice($lines, 0, 4);
    }
}
