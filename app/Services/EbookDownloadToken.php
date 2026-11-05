<?php

namespace App\Services;

use RuntimeException;

class EbookDownloadToken
{
    public function generate(string $orderReference, int $generation): string
    {
        $key = (string) config('app.key');
        if ($key === '' || $generation < 1) {
            throw new RuntimeException('The eBook download token key is not configured.');
        }

        return hash_hmac('sha256', 'ebook-download:'.$orderReference.':'.$generation, $key);
    }
}
