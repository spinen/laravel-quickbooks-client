<?php

namespace Spinen\QuickBooks;

use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasQuickBooksToken
{
    /**
     * Have a quickBooksToken.
     */
    public function quickBooksToken(): HasOne
    {
        return $this->hasOne(Token::class);
    }
}
