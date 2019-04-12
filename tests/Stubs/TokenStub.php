<?php

namespace Spinen\QuickBooks\Stubs;

use Spinen\QuickBooks\Token;

/**
 * Class TokenStub
 *
 * Stub for a Laravel Token model
 *
 * @package Spinen\QuickBooks\Stubs
 */
class TokenStub extends Token
{
    // Put public properties on this stub to keep the Model from tyring to get them from db
    public $access_token = null;

    public $access_token_expires_at = null;

    public $realm_id = null;

    public $refresh_token = null;

    public $refresh_token_expires_at = null;

    public $user = null;

    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        return $related . ',' . $foreignKey . ',' . $ownerKey;
    }
}
