<?php

namespace Spinen\QuickBooks\Http\Controllers;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Session\Store;
use Mockery;
use Mockery\Mock;
use QuickBooksOnline\API\DataService\DataService;
use Spinen\QuickBooks\Client as QuickBooks;
use Spinen\QuickBooks\TestCase;

/**
 * Class ControllerTest
 */
class ControllerTest extends TestCase
{
    /**
     * @var Controller
     */
    protected $controller;

    /**
     * @var Mock
     */
    protected $data_service_mock;

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
    protected $view_factory_mock;

    /**
     * @var Mock
     */
    protected $view_mock;

    protected function setUp(): void
    {
        $this->data_service_mock = Mockery::mock(DataService::class);
        $this->quickbooks_mock = Mockery::mock(QuickBooks::class);
        $this->redirector_mock = Mockery::mock(Redirector::class);
        $this->request_mock = Mockery::mock(Request::class);
        $this->session_mock = Mockery::mock(Store::class);
        $this->view_factory_mock = Mockery::mock(ViewFactory::class);
        $this->view_mock = Mockery::mock(View::class);

        $this->controller = new Controller();
    }

    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(Controller::class, $this->controller);
    }

    /**
     * @test
     */
    public function it_shows_view_to_connect_if_account_not_linked()
    {
        $this->data_service_mock
            ->shouldReceive('getCompanyInfo')
            ->once()
            ->withNoArgs()
            ->andReturn([
                'name' => 'Company',
            ]);

        $this->quickbooks_mock
            ->shouldReceive('hasValidRefreshToken')
            ->once()
            ->withNoArgs()
            ->andReturnTrue();

        $this->quickbooks_mock
            ->shouldReceive('getDataService')
            ->once()
            ->withNoArgs()
            ->andReturn($this->data_service_mock);

        $this->view_factory_mock
            ->shouldReceive('make')
            ->once()
            ->with('quickbooks::disconnect')
            ->andReturn($this->view_mock);

        $this->view_mock
            ->shouldReceive('with')
            ->once()
            ->withArgs([
                'company',
                [
                    'name' => 'Company',
                ],
            ])
            ->andReturnSelf();

        $this->controller->connect($this->quickbooks_mock, $this->view_factory_mock);
    }

    /**
     * @test
     */
    public function it_shows_view_to_disconnect_if_account_linked()
    {
        $this->quickbooks_mock
            ->shouldReceive('hasValidRefreshToken')
            ->once()
            ->withNoArgs()
            ->andReturnFalse();

        $this->quickbooks_mock
            ->shouldReceive('authorizationUri')
            ->once()
            ->withNoArgs()
            ->andReturn('http://uri');

        $this->view_factory_mock
            ->shouldReceive('make')
            ->once()
            ->with('quickbooks::connect')
            ->andReturn($this->view_mock);

        $this->view_mock
            ->shouldReceive('with')
            ->once()
            ->withArgs(['authorization_uri', 'http://uri'])
            ->andReturnSelf();

        $this->controller->connect($this->quickbooks_mock, $this->view_factory_mock);
    }

    /**
     * @test
     */
    public function it_disconnects_from_quickbooks_when_requested()
    {
        $this->request_mock
            ->shouldReceive('session')
            ->once()
            ->andReturn($this->session_mock);

        $this->session_mock
            ->shouldReceive('flash')
            ->once()
            ->withAnyArgs();

        $this->redirector_mock
            ->shouldReceive('back')
            ->once()
            ->andReturn(new RedirectResponse('/test', 302));

        $this->quickbooks_mock
            ->shouldReceive('deleteToken')
            ->once()
            ->withNoArgs();

        $result = $this->controller->disconnect(
            $this->redirector_mock,
            $this->request_mock,
            $this->quickbooks_mock,
        );

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    /**
     * @test
     */
    public function it_finishes_connecting_to_quickbooks_when_given_a_valid_token_by_quickbooks()
    {
        $this->url_generator_mock = Mockery::mock(UrlGenerator::class);
        $realmId = random_int(1, 9999);

        $this->quickbooks_mock
            ->shouldReceive('exchangeCodeForToken')
            ->once()
            ->withArgs(['code', $realmId]);

        $this->request_mock
            ->shouldReceive('get')
            ->once()
            ->withArgs(['code'])
            ->andReturn('code');

        $this->request_mock
            ->shouldReceive('get')
            ->once()
            ->withArgs(['realmId'])
            ->andReturn($realmId);

        $this->request_mock
            ->shouldReceive('session')
            ->once()
            ->andReturn($this->session_mock);

        $this->session_mock
            ->shouldReceive('flash')
            ->once()
            ->withAnyArgs();

        $this->redirector_mock
            ->shouldReceive('intended')
            ->once()
            ->withAnyArgs()
            ->andReturn(new RedirectResponse('/test', 302));

        $this->url_generator_mock
            ->shouldReceive('route')
            ->withArgs(['quickbooks.connect'])
            ->once();

        $result = $this->controller->token(
            $this->redirector_mock,
            $this->request_mock,
            $this->quickbooks_mock,
            $this->url_generator_mock,
        );

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }
}
