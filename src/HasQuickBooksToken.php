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
        $config = config('quickbooks.user');
        return $this->hasOne(Token::class, $config['keys']['owner'], $config['keys']['foreign']);
    }
}
