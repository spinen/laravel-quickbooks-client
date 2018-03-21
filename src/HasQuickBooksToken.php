<?php

namespace Spinen\QuickBooks;

trait HasQuickBooksToken
{
    /**
     * Have a quickBooksToken.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function quickBooksToken()
    {
        return $this->hasOne(Token::class);
    }
}
