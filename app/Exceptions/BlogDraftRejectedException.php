<?php

namespace App\Exceptions;

use RuntimeException;

/** A terminal editorial rejection that should not consume automatic queue retries. */
class BlogDraftRejectedException extends RuntimeException
{
}
