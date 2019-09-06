<?php

namespace Spinen\QuickBooks\Stubs;

use Spinen\QuickBooks\HasQuickBooksToken;

/**
 * Class User
 *
 * Stub for a Laravel User model
 *
 * @package Spinen\QuickBooks\Stubs
 */
class User
{
    use HasQuickBooksToken;

    public function hasOne($relationship)
    {
        return $relationship;
    }
}
