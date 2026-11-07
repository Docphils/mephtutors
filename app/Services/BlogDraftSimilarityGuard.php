<?php

namespace App\Services;

class BlogDraftSimilarityGuard
{
    /**
     * Return the closest recent article when the candidate is likely a duplicate.
     *
     * @param  list<array{id: int, title: string, excerpt: ?string, body: string}>  $existing
     * @return array{id: int, title: string, score: float}|null
     */
    public function closestMatch(string $title, string $body, array $existing): ?array
    {
        $candidateTitle = $this->tokens($title);
        $candidateBody = $this->shingles($body);
        $closest = null;

        foreach ($existing as $article) {
            $existingTitle = $this->tokens($article['title']);
            $titleScore = $this->jaccard($candidateTitle, $existingTitle);
            similar_text(implode(' ', $candidateTitle), implode(' ', $existingTitle), $titlePercent);
            $titleScore = max($titleScore, $titlePercent / 100);

            $bodyScore = $this->jaccard($candidateBody, $this->shingles($article['body']));
            $score = max($titleScore, $bodyScore);
            $isLikelyDuplicate = (count($candidateTitle) >= 2 && $candidateTitle === $existingTitle)
                || (count($candidateTitle) >= 4 && $titleScore >= 0.78)
                || (count($candidateTitle) >= 6 && $titleScore >= 0.68 && $bodyScore >= 0.28)
                || (count($candidateBody) >= 12 && $bodyScore >= 0.62);

            if ($isLikelyDuplicate && ($closest === null || $score > $closest['score'])) {
                $closest = [
                    'id' => $article['id'],
                    'title' => $article['title'],
                    'score' => $score,
                ];
            }
        }

        return $closest;
    }

    /** @return list<string> */
    private function tokens(string $text): array
    {
        return array_values(array_unique($this->words($text)));
    }

    /** @return list<string> */
    private function words(string $text): array
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = mb_strtolower($text);
        $words = preg_split('/[^\pL\pN]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $stopWords = ['a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from', 'how', 'in', 'is', 'it', 'of', 'on', 'or', 'the', 'this', 'to', 'was', 'what', 'when', 'why', 'with'];

        return array_values(array_filter($words, fn (string $word) => mb_strlen($word) > 2 && ! in_array($word, $stopWords, true)));
    }

    /** @return list<string> */
    private function shingles(string $text): array
    {
        $words = $this->words($text);
        if (count($words) < 5) {
            return $words;
        }

        $shingles = [];
        for ($index = 0; $index <= count($words) - 5; $index++) {
            $shingles[] = implode(' ', array_slice($words, $index, 5));
        }

        return array_values(array_unique($shingles));
    }

    /** @param list<string> $left @param list<string> $right */
    private function jaccard(array $left, array $right): float
    {
        if ($left === [] || $right === []) {
            return 0.0;
        }

        $intersection = count(array_intersect($left, $right));
        $union = count(array_unique([...$left, ...$right]));

        return $intersection / $union;
    }
}
