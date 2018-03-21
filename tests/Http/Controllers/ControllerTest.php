<?php

namespace Spinen\QuickBooks\Http\Controllers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Mockery;
use Mockery\Mock;
use Spinen\QuickBooks\Client as QuickBooks;
use Spinen\QuickBooks\TestCase;

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
    protected $view_factory_mock;

    /**
     * @var Mock
     */
    protected $view_mock;

    protected function setUp()
    {
        $this->data_service_mock = Mockery::mock();
        $this->quickbooks_mock = Mockery::mock(QuickBooks::class);
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
        $this->data_service_mock->shouldReceive('getCompanyInfo')
                                ->once()
                                ->withNoArgs()
                                ->andReturn([
                                    'name' => 'Company',
                                ]);

        $this->quickbooks_mock->shouldReceive('hasValidRefreshToken')
                              ->once()
                              ->withNoArgs()
                              ->andReturnTrue();

        $this->quickbooks_mock->shouldReceive('getDataService')
                              ->once()
                              ->withNoArgs()
                              ->andReturn($this->data_service_mock);

        $this->view_factory_mock->shouldReceive('make')
                                ->once()
                                ->with('quickbooks::disconnect')
                                ->andReturn($this->view_mock);

        $this->view_mock->shouldReceive('with')
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
        $this->quickbooks_mock->shouldReceive('hasValidRefreshToken')
                              ->once()
                              ->withNoArgs()
                              ->andReturnFalse();

        $this->quickbooks_mock->shouldReceive('authorizationUri')
                              ->once()
                              ->withNoArgs()
                              ->andReturn('http://uri');

        $this->view_factory_mock->shouldReceive('make')
                                ->once()
                                ->with('quickbooks::connect')
                                ->andReturn($this->view_mock);

        $this->view_mock->shouldReceive('with')
                        ->once()
                        ->withArgs([
                            'authorization_uri',
                            'http://uri',
                        ])
                        ->andReturnSelf();

        $this->controller->connect($this->quickbooks_mock, $this->view_factory_mock);
    }
}
