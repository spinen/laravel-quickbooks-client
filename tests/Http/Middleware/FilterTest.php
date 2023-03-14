<?php

namespace Spinen\QuickBooks\Http\Middleware;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Mockery;
use Mockery\Mock;
use Spinen\QuickBooks\Client as QuickBooks;
use Spinen\QuickBooks\TestCase;

/**
 * Class FilterTest
 */
class FilterTest extends TestCase
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var Mock
     */
    protected $quickbooks_mock;

    /**
     * @var Mock
     */
    protected $redirector_mock;

    /**
     * @var Mock
     */
    protected $request_mock;

    /**
     * @var Mock
     */
    protected $session_mock;

    /**
     * @var Mock
     */
    protected $url_generator_mock;

    protected function setUp(): void
    {
        $this->quickbooks_mock = Mockery::mock(QuickBooks::class);
        $this->redirector_mock = Mockery::mock(Redirector::class);
        $this->request_mock = Mockery::mock(Request::class);
        $this->session_mock = Mockery::mock(Session::class);
        $this->url_generator_mock = Mockery::mock(UrlGenerator::class);

        $this->filter = new Filter(
            $this->quickbooks_mock,
            $this->redirector_mock,
            $this->session_mock,
            $this->url_generator_mock,
        );
    }

    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(Filter::class, $this->filter);
    }

    /**
     * @test
     */
    public function it_just_passes_the_request_to_the_next_middleware_if_account_linked_to_quickbooks()
    {
        $next_middleware = function ($request) {
            $this->assertEquals($this->request_mock, $request);
        };

        $this->quickbooks_mock
            ->shouldReceive('hasValidRefreshToken')
            ->once()
            ->withNoArgs()
            ->andReturnTrue();

        $this->filter->handle($this->request_mock, $next_middleware);
    }

    /**
     * @test
     */
    public function it_redirects_to_quickbooks_connect_route_after_setting_intended_session_if_account_not_linked()
    {
        $this->request_mock
            ->shouldReceive('path')
            ->once()
            ->withNoArgs()
            ->andReturn('path');

        $this->url_generator_mock
            ->shouldReceive('to')
            ->once()
            ->with('path')
            ->andReturn('http://to/path');

        $this->session_mock
            ->shouldReceive('put')
            ->once()
            ->withArgs(['url.intended', 'http://to/path'])
            ->andReturnNull();

        $this->redirector_mock
            ->shouldReceive('route')
            ->once()
            ->with('quickbooks.connect')
            ->andReturnSelf();

        $next_middleware = function ($request) {
            // If this is called, then fail test
            $this->assertTrue(false);
        };

        $this->quickbooks_mock
            ->shouldReceive('hasValidRefreshToken')
            ->once()
            ->withNoArgs()
            ->andReturnFalse();

        $this->filter->handle($this->request_mock, $next_middleware);
    }
}
