<?php

namespace Spinen\QuickBooks\Stubs;

use Spinen\QuickBooks\HasQuickBooksToken;

class User
{
    use HasQuickBooksToken;

    public function hasOne($relationship)
    {
        return $relationship;
    }
}
