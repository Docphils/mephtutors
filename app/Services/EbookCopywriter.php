<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class EbookCopywriter
{
    private const MAX_SOURCE_CHARACTERS = 6000;

    private const MAX_RATE_LIMIT_WAIT_SECONDS = 10;

    public function generate(string $title, string $chapterText, ?string $pdfPath = null): array
    {
        $key = (string) config('services.groq.api_key');
        if ($key === '') {
            throw new RuntimeException('Groq API key is not configured.');
        }

        $pdfText = '';
        $images = [];
        if (trim($chapterText) === '' && $pdfPath && is_file($pdfPath)) {
            [$pdfText, $images] = $this->readPdfSource($pdfPath);
        }

        $sourceText = trim($chapterText) !== ''
            ? "Free chapter text:\n".trim($chapterText)
            : ($pdfText !== '' ? "Extracted PDF text:\n".$pdfText : '');

        $content = [[
            'type' => 'text',
            'text' => "Book title: {$title}\n\nCreate accurate, useful draft publishing copy from this source. Do not invent claims or content absent from the source. Return a JSON object with description, audience, contents, faq (array of question/answer objects), meta_title, meta_description, primary_keyword.\n\n".$this->compactSourceText($sourceText),
        ]];

        array_push($content, ...$images);

        $payload = [
            'model' => config('services.groq.model', 'qwen/qwen3.8-27b'),
            'messages' => [
                ['role' => 'system', 'content' => 'You write clear, persuasive eBook landing page drafts. Use only details supported by the supplied title and source chapter. Avoid fabricated outcomes, credentials, or chapter details. Return only valid JSON with the requested keys.'],
                ['role' => 'user', 'content' => $content],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.3,
            'max_completion_tokens' => 2048,
        ];

        [$response, $model] = $this->sendRequest($key, $payload, $images !== []);
        if (! $response->successful()) {
            $providerMessage = $response->json('error.message');
            $providerMessage = is_string($providerMessage)
                ? mb_substr(trim((string) preg_replace('/\s+/', ' ', $providerMessage)), 0, 400)
                : '';
            $message = "Groq rejected the copywriting request with model {$model} (HTTP {$response->status()})";
            if ($providerMessage !== '') {
                $message .= ': '.rtrim($providerMessage, '.');
            }

            throw new GroqApiException($message.'.');
        }

        $output = $response->json('choices.0.message.content');
        $draft = is_string($output) ? json_decode($output, true) : null;
        if (! is_array($draft)) {
            throw new RuntimeException('The copywriting response was invalid.');
        }

        $faq = $draft['faq'] ?? [];
        if (! is_array($faq) || ! array_is_list($faq)) {
            throw new RuntimeException('The copywriting response contained an invalid FAQ list.');
        }

        foreach ($faq as $row) {
            if (! is_array($row)) {
                throw new RuntimeException('The copywriting response contained an invalid FAQ item.');
            }
        }

        return [
            'description' => mb_substr($this->responseText($draft['description'] ?? ''), 0, 20000),
            'audience' => mb_substr($this->responseText($draft['audience'] ?? ''), 0, 10000),
            'contents' => mb_substr($this->responseText($draft['contents'] ?? ''), 0, 10000),
            'faq' => collect($faq)->take(20)
                ->map(fn ($row) => [
                    'question' => mb_substr($this->responseText($row['question'] ?? ''), 0, 300),
                    'answer' => mb_substr($this->responseText($row['answer'] ?? ''), 0, 3000),
                ])->all(),
            'meta_title' => mb_substr($this->responseText($draft['meta_title'] ?? ''), 0, 200),
            'meta_description' => mb_substr($this->responseText($draft['meta_description'] ?? ''), 0, 320),
            'primary_keyword' => mb_substr($this->responseText($draft['primary_keyword'] ?? ''), 0, 180),
        ];
    }

    /**
     * @return array{summary: string, description: string}
     */
    public function generateProductCopy(
        string $name,
        string $category,
        string $releaseStatus,
        string $summary,
        string $description,
        string $notes,
    ): array {
        $key = (string) config('services.groq.api_key');
        if ($key === '') {
            throw new RuntimeException('Groq API key is not configured.');
        }

        $payload = [
            'model' => config('services.groq.model', 'qwen/qwen3.8-27b'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You write clear, accurate website copy for a company product portfolio. Use only facts supplied by the administrator. Do not invent capabilities, results, pricing, integrations, credentials, or availability details. Return only valid JSON with summary and description string fields.',
                ],
                [
                    'role' => 'user',
                    'content' => implode("\n", [
                        "Product name: {$name}",
                        "Category: {$category}",
                        "Availability: {$releaseStatus}",
                        'Current short description: '.($summary !== '' ? $summary : '(not provided)'),
                        'Current additional details: '.($description !== '' ? $description : '(not provided)'),
                        'Administrator notes: '.($notes !== '' ? $notes : '(none)'),
                        '',
                        'Draft a concise product-card summary (maximum 500 characters) and useful additional details (maximum 5000 characters). Do not repeat unsupported claims. If the supplied facts are sparse, keep the copy general and factual.',
                    ]),
                ],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.3,
            'max_completion_tokens' => 1200,
        ];

        [$response, $model] = $this->sendRequest((string) $key, $payload, false);
        if (! $response->successful()) {
            $providerMessage = $response->json('error.message');
            $providerMessage = is_string($providerMessage)
                ? mb_substr(trim((string) preg_replace('/\s+/', ' ', $providerMessage)), 0, 400)
                : '';
            $message = "Groq rejected the product copy request with model {$model} (HTTP {$response->status()})";
            if ($providerMessage !== '') {
                $message .= ': '.rtrim($providerMessage, '.');
            }

            throw new GroqApiException($message.'.');
        }

        $output = $response->json('choices.0.message.content');
        $draft = is_string($output) ? json_decode($output, true) : null;
        if (! is_array($draft)) {
            throw new RuntimeException('The product copy response was invalid.');
        }

        return [
            'summary' => mb_substr($this->responseText($draft['summary'] ?? ''), 0, 500),
            'description' => mb_substr($this->responseText($draft['description'] ?? ''), 0, 5000),
        ];
    }

    private function responseText(mixed $value): string
    {
        if (is_string($value) || is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_null($value)) {
            return '';
        }

        if (! is_array($value) || ! array_is_list($value)) {
            throw new RuntimeException('The copywriting response contained an unsupported field value.');
        }

        $parts = array_map(fn ($item) => $this->responseText($item), $value);

        return implode("\n", array_filter($parts, fn ($part) => $part !== ''));
    }

    private function compactSourceText(string $source): string
    {
        $source = trim($source);
        $length = mb_strlen($source);
        if ($length <= self::MAX_SOURCE_CHARACTERS) {
            return $source;
        }

        $windowLength = intdiv(self::MAX_SOURCE_CHARACTERS, 3) - 30;
        $starts = [0, intdiv($length - $windowLength, 2), $length - $windowLength];
        $excerpts = [];

        foreach ($starts as $index => $start) {
            $excerpts[] = trim(mb_substr($source, $start, $windowLength));
        }

        return implode("\n\n[...chapter excerpt omitted...]\n\n", $excerpts);
    }

    /** @return array{\Illuminate\Http\Client\Response, string} */
    private function sendRequest(string $key, array $payload, bool $hasImages): array
    {
        $url = 'https://api.groq.com/openai/v1/chat/completions';
        $primaryModel = (string) $payload['model'];
        $fallbackModels = config('services.groq.fallback_models', []);
        $models = array_values(array_unique(array_filter([
            $primaryModel,
            ...(is_array($fallbackModels) ? $fallbackModels : []),
        ], fn ($model) => is_string($model) && trim($model) !== '')));
        $visionModels = config('services.groq.vision_models', []);
        $visionModels = is_array($visionModels) ? $visionModels : [];
        if ($hasImages) {
            $models = array_values(array_filter(
                $models,
                fn ($model) => in_array($model, $visionModels, true)
            ));
        }
        $lastResponse = null;
        $lastModel = $primaryModel;

        foreach ($models as $model) {
            $modelPayload = [...$payload, 'model' => $model];
            $response = Http::withToken($key)->timeout(120)->post($url, $modelPayload);
            $lastResponse = $response;
            $lastModel = $model;

            if ($response->successful()) {
                return [$response, $model];
            }

            if ($response->status() === 429 && $model === $primaryModel) {
                $waitSeconds = $this->rateLimitWaitSeconds($response);
                if ($waitSeconds <= self::MAX_RATE_LIMIT_WAIT_SECONDS) {
                    usleep((int) ceil($waitSeconds * 1_000_000));
                    $response = Http::withToken($key)->timeout(120)->post($url, $modelPayload);
                    $lastResponse = $response;

                    if ($response->successful()) {
                        return [$response, $model];
                    }
                }
            }

            if (! $this->shouldTryNextModel($response)) {
                return [$response, $model];
            }
        }

        if ($lastResponse instanceof \Illuminate\Http\Client\Response) {
            return [$lastResponse, $lastModel];
        }

        throw new RuntimeException('No vision-capable Groq model is configured for this PDF input.');
    }

    private function shouldTryNextModel(\Illuminate\Http\Client\Response $response): bool
    {
        if ($response->status() === 429 || $response->status() === 404) {
            return true;
        }

        $message = $response->json('error.message');

        return $response->status() === 400
            && is_string($message)
            && preg_match('/model.{0,40}(not found|unavailable|not available|decommissioned)/i', $message) === 1;
    }

    private function rateLimitWaitSeconds(\Illuminate\Http\Client\Response $response): float
    {
        $retryAfter = $response->header('Retry-After');
        if (is_numeric($retryAfter)) {
            return max(0, (float) $retryAfter);
        }

        $message = $response->json('error.message');
        if (is_string($message) && preg_match('/try again in\s+([0-9]+(?:\.[0-9]+)?)\s*s/i', $message, $matches)) {
            return max(0, (float) $matches[1]) + 0.1;
        }

        return 1;
    }

    /** @return array{string, list<array{type: string, image_url: array{url: string}}>} */
    private function readPdfSource(string $pdfPath): array
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ebook-ai-'.Str::uuid();
        if (! mkdir($directory, 0700) && ! is_dir($directory)) {
            throw new RuntimeException('Could not prepare temporary PDF preview files.');
        }

        $textPath = $directory.DIRECTORY_SEPARATOR.'chapter.txt';
        $binary = (string) config('ebooks.fulfilment.pdf_stamper_binary');

        try {
            $textProcess = new Process([$binary, '-dSAFER', '-dBATCH', '-dNOPAUSE', '-sDEVICE=txtwrite', '-sOutputFile='.$textPath, '-f', $pdfPath]);
            $textProcess->setTimeout(60);
            $textProcess->run();
            $pdfText = '';
            if ($textProcess->isSuccessful() && is_file($textPath)) {
                $extractedText = file_get_contents($textPath);
                if (is_string($extractedText) && trim($extractedText) !== '') {
                    $pdfText = trim($extractedText);
                }
            }

            if ($pdfText !== '') {
                return [$pdfText, []];
            }

            $ocrBinary = (string) config('ebooks.fulfilment.ocr_binary', '');
            if ($ocrBinary !== '') {
                $ocrPattern = $directory.DIRECTORY_SEPARATOR.'ocr-page-%d.png';
                $ocrImages = $this->renderPdfPages($binary, $pdfPath, $ocrPattern, 3, 'pnggray', 200);
                $pdfText = $this->ocrPdfPages($ocrBinary, $ocrImages);
                if ($pdfText !== '') {
                    return [$pdfText, []];
                }
            }

            $imagePattern = $directory.DIRECTORY_SEPARATOR.'page-%d.jpg';
            $images = $this->renderPdfPages($binary, $pdfPath, $imagePattern, 1, 'jpeg', 90, ['-dJPEGQ=65']);

            return ['', $this->encodePdfImages($images)];
        } finally {
            foreach (glob($directory.DIRECTORY_SEPARATOR.'*') ?: [] as $temporaryFile) {
                @unlink($temporaryFile);
            }
            @rmdir($directory);
        }
    }

    /** @return list<string> */
    private function renderPdfPages(string $binary, string $pdfPath, string $imagePattern, int $lastPage, string $device, int $resolution, array $deviceOptions = []): array
    {
        $process = new Process([
            $binary,
            '-dSAFER',
            '-dBATCH',
            '-dNOPAUSE',
            '-dFirstPage=1',
            '-dLastPage='.$lastPage,
            '-sDEVICE='.$device,
            ...$deviceOptions,
            '-r'.$resolution,
            '-sOutputFile='.$imagePattern,
            '-f',
            $pdfPath,
        ]);
        $process->setTimeout(90);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new RuntimeException('The PDF preview could not be rendered for AI analysis.');
        }

        return glob(str_replace('%d', '*', $imagePattern)) ?: [];
    }

    /** @param list<string> $imagePaths */
    private function ocrPdfPages(string $binary, array $imagePaths): string
    {
        $pages = [];
        foreach ($imagePaths as $imagePath) {
            $process = new Process([$binary, $imagePath, 'stdout', '-l', 'eng']);
            $process->setTimeout(30);
            $process->run();
            if ($process->isSuccessful() && trim($process->getOutput()) !== '') {
                $pages[] = trim($process->getOutput());
            }
        }

        return mb_substr(implode("\n\n", $pages), 0, self::MAX_SOURCE_CHARACTERS);
    }

    /**
     * @param  list<string>  $imagePaths
     * @return list<array{type: string, image_url: array{url: string}}>
     */
    private function encodePdfImages(array $imagePaths): array
    {
        $images = [];
        foreach ($imagePaths as $imagePath) {
            $imageData = file_get_contents($imagePath);
            if (is_string($imageData)) {
                $images[] = ['type' => 'image_url', 'image_url' => ['url' => 'data:image/jpeg;base64,'.base64_encode($imageData)]];
            }
        }

        return $images;
    }
}
