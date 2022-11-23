<?php

namespace Spinen\QuickBooks\Stubs;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mockery;
use Spinen\QuickBooks\Token;

/**
 * Class TokenStub
 *
 * Stub for a Laravel Token model
 */
class TokenStub extends Token
{
    // Put public properties on this stub to keep the Model from trying to get them from db
    public $access_token = null;

    public $access_token_expires_at = null;

    public $realm_id = null;

    public $refresh_token = null;

    public $refresh_token_expires_at = null;

    public $user = null;

    public function belongsTo(
        $related,
        $foreignKey = null,
        $ownerKey = null,
        $relation = null,
    ): BelongsTo {
        $related_mock = Mockery::mock($related);
        $related_mock->shouldIgnoreMissing();

        $child_mock = Mockery::mock(Model::class);
        $child_mock->shouldIgnoreMissing();

        $builder_mock = Mockery::mock(Builder::class);
        $builder_mock->shouldIgnoreMissing();
        $builder_mock->shouldReceive('getModel')->andReturn($related_mock);

        return new BelongsTo($builder_mock, $child_mock, $foreignKey, $ownerKey, $relation);
    }
}
