<?php

namespace Spinen\QuickBooks\Stubs;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Mockery;
use Spinen\QuickBooks\HasQuickBooksToken;

/**
 * Class User
 *
 * Stub for a Laravel User model
 */
class User
{
    use HasQuickBooksToken;

    public function hasOne($relationship): HasOne
    {
        $related_mock = Mockery::mock($relationship);
        $related_mock->shouldIgnoreMissing();

        $builder_mock = Mockery::mock(Builder::class);
        $builder_mock->shouldIgnoreMissing();
        $builder_mock->shouldReceive('getModel')->andReturn($related_mock);

        $parent_mock = Mockery::mock(Model::class);
        $parent_mock->shouldIgnoreMissing();

        return new HasOne($builder_mock, $parent_mock, 'foreignKey', 'localKey');
    }
}
