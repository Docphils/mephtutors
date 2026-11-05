<?php

namespace Tests\Feature;

use App\Services\EbookPdfStamper;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class EbookPdfStamperTest extends TestCase
{
    public function test_stamp_is_applied_to_each_page_of_a_real_pdf(): void
    {
        $binary = (string) config('ebooks.fulfilment.pdf_stamper_binary');
        $probe = new Process([$binary, '--version']);
        $probe->run();
        $this->assertTrue($probe->isSuccessful(), 'Ghostscript must be installed on the worker host.');

        $directory = storage_path('framework/testing/ebook-pdf-'.bin2hex(random_bytes(6)));
        mkdir($directory, 0700, true);
        $sourcePs = $directory.DIRECTORY_SEPARATOR.'source.ps';
        $sourcePdf = $directory.DIRECTORY_SEPARATOR.'source.pdf';
        $stampedPdf = $directory.DIRECTORY_SEPARATOR.'stamped.pdf';
        $textFile = $directory.DIRECTORY_SEPARATOR.'stamped.txt';
        file_put_contents($sourcePs, "%!PS\n/Helvetica findfont 14 scalefont setfont\n72 720 moveto (Page one) show\nshowpage\n72 720 moveto (Page two) show\nshowpage\n");

        try {
            $create = new Process([$binary, '-dBATCH', '-dNOPAUSE', '-sDEVICE=pdfwrite', '-sOutputFile='.$sourcePdf, $sourcePs]);
            $create->run();
            $this->assertTrue($create->isSuccessful(), $create->getErrorOutput());

            app(EbookPdfStamper::class)->stamp($sourcePdf, $stampedPdf, 'buyer@example.com');
            $extract = new Process([$binary, '-dBATCH', '-dNOPAUSE', '-sDEVICE=txtwrite', '-sOutputFile='.$textFile, $stampedPdf]);
            $extract->run();
            $this->assertTrue($extract->isSuccessful(), $extract->getErrorOutput());
            $text = file_get_contents($textFile);
            $this->assertSame(2, substr_count($text, 'buyer@example.com'), $text);
            $this->assertStringContainsString('Page one', $text);
            $this->assertStringContainsString('Page two', $text);
        } finally {
            foreach ([$sourcePs, $sourcePdf, $stampedPdf, $textFile] as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }
            if (is_dir($directory)) {
                rmdir($directory);
            }
        }
    }
}
