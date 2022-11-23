<?php

namespace Spinen\QuickBooks;

use ArrayAccess;
use Countable;
use Iterator;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Class TestCase
 */
abstract class TestCase extends PHPUnitTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Helper to allow mocking of iterator classes.
     *
     * @link https://gist.github.com/VladaHejda/8299871
     *
     * @return void
     */
    protected function mockArrayIterator(MockInterface $mock, array $items)
    {
        if ($mock instanceof ArrayAccess) {
            foreach ($items as $key => $val) {
                $mock
                    ->shouldReceive('offsetGet')
                    ->with($key)
                    ->andReturn($val);

                $mock
                    ->shouldReceive('offsetExists')
                    ->with($key)
                    ->andReturn(true);
            }

            $mock->shouldReceive('offsetExists')->andReturn(false);
        }

        if ($mock instanceof Iterator) {
            $counter = 0;

            $mock->shouldReceive('rewind')->andReturnUsing(function () use (&$counter) {
                $counter = 0;
            });

            $vals = array_values($items);
            $keys = array_values(array_keys($items));

            $mock->shouldReceive('valid')->andReturnUsing(function () use (&$counter, $vals) {
                return isset($vals[$counter]);
            });

            $mock->shouldReceive('current')->andReturnUsing(function () use (&$counter, $vals) {
                return $vals[$counter];
            });

            $mock->shouldReceive('key')->andReturnUsing(function () use (&$counter, $keys) {
                return $keys[$counter];
            });

            $mock->shouldReceive('next')->andReturnUsing(function () use (&$counter) {
                $counter++;
            });
        }

        if ($mock instanceof Countable) {
            $mock->shouldReceive('count')->andReturn(count($items));
        }
    }
}
