<?php

namespace App\Exceptions;

class BlogDraftFieldLimitException extends BlogDraftRejectedException
{
    /** @param list<string> $issues */
    public function __construct(public readonly array $issues)
    {
        parent::__construct('The generated article exceeded editorial field limits: '.implode(' ', $issues));
    }

    public function revisionInstructions(): string
    {
        return "The previous draft exceeded these exact editorial limits: ".implode(' ', $this->issues)
            ." Rewrite the complete JSON response, preserve its verified facts and useful content, and keep every named field within its limit. Do not explain the correction.";
    }
}
