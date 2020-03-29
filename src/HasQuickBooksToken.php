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

    public function getClient(): Client
    {
        if (!$this->quickBooksToken) {
            $token = $this->quickBooksToken()->make();
        }

        return new Client(config('quickbooks'), $token ?? $this->quickBooksToken);
    }
}
