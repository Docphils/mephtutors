<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

class EbookPdfStamper
{
    public function stamp(string $sourcePath, string $destinationPath, string $buyerEmail): void
    {
        if (! is_file($sourcePath) || ! is_readable($sourcePath)) {
            throw new RuntimeException('The source eBook PDF is unavailable.');
        }

        $directory = dirname($destinationPath);
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new RuntimeException('Could not prepare private PDF output storage.');
        }

        $stampProgram = sprintf(
            '<< /EndPage { dup 0 eq { pop pop gsave 0.55 setgray /Helvetica findfont 5 scalefont setfont 28 18 moveto 0.72 1 scale <%s> show grestore true } { pop pop true } ifelse } bind >> setpagedevice',
            bin2hex($buyerEmail),
        );

        $process = new Process([
            (string) config('ebooks.fulfilment.pdf_stamper_binary'),
            '-dSAFER', '-dBATCH', '-dNOPAUSE', '-dNOPROMPT',
            '-sDEVICE=pdfwrite', '-dCompatibilityLevel=1.7',
            '-sOutputFile='.$destinationPath,
            '-c', $stampProgram,
            '-f', $sourcePath,
        ]);
        $process->setTimeout(config('ebooks.fulfilment.pdf_stamper_timeout'));
        $process->run();

        if (! $process->isSuccessful() || ! is_file($destinationPath) || filesize($destinationPath) < 8) {
            @unlink($destinationPath);
            throw new RuntimeException('PDF stamping failed: '.mb_substr(trim($process->getErrorOutput()), 0, 500));
        }

        $handle = fopen($destinationPath, 'rb');
        $signature = $handle ? fread($handle, 5) : false;
        if ($handle) {
            fclose($handle);
        }

        if ($signature !== '%PDF-') {
            @unlink($destinationPath);
            throw new RuntimeException('PDF stamping produced an invalid output file.');
        }
    }
}
