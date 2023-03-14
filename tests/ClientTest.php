<?php

namespace Spinen\QuickBooks;

use Mockery;
use Mockery\Mock;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Exception\ServiceException;

/**
 * Class ClientTest
 */
class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $configs = [];

    /**
     * @var Mock
     */
    protected $token_mock;

    protected function setUp(): void
    {
        $this->configs = [
            'data_service' => [
                'auth_mode' => 'oauth2',
                'base_url' => 'Development',
                'client_id' => 'QUICKBOOKS_CLIENT_ID',
                'client_secret' => 'QUICKBOOKS_CLIENT_SECRET',
                'scope' => 'com.intuit.quickbooks.accounting',
            ],
        ];

        $this->token_mock = Mockery::mock(Token::class);

        $this->client = $this->makeClient();
    }

    private function makeClient($configs = null)
    {
        if (! is_null($configs)) {
            return new Client($configs, $this->token_mock);
        }

        return new Client($this->configs, $this->token_mock);
    }

    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(Client::class, $this->client);
    }

    /**
     * @test
     */
    public function it_returns_a_data_service_configured_to_request_oauth_token_when_token_is_empty()
    {
        $this->token_mock
            ->shouldReceive('getAttribute')
            ->twice()
            ->with('hasValidAccessToken')
            ->andReturnFalse();

        $this->token_mock
            ->shouldReceive('getAttribute')
            ->once()
            ->with('hasValidRefreshToken')
            ->andReturnFalse();

        $this->assertFalse(
            $this->client
                ->getDataService()
                ->getServiceContext()
                ->IppConfiguration->Security->isAccessTokenSet(),
        );
    }

    /**
     * @test
     */
    public function it_caches_the_data_service_once_it_is_made()
    {
        $this->token_mock
            ->shouldReceive('getAttribute')
            ->twice()
            ->with('hasValidAccessToken')
            ->andReturnFalse();

        $this->token_mock
            ->shouldReceive('getAttribute')
            ->once()
            ->with('hasValidRefreshToken')
            ->andReturnFalse();

        // TODO: Find out if this is the actual goal of this test, to make sure a DataService is received
        $this->assertEquals(DataService::class, get_class($this->client->getDataService()));
    }

    /**
     * @test
     */
    public function it_returns_a_data_service_with_oauth_token_when_valid_access_token_exist()
    {
        $this->token_mock
            ->shouldReceive('getAttribute')
            ->twice()
            ->with('hasValidAccessToken')
            ->andReturnTrue();

        $this->token_mock
            ->shouldReceive('getAttribute')
            ->once()
            ->with('access_token')
            ->andReturn('access_token');

        $this->token_mock
            ->shouldReceive('getAttribute')
            ->once()
            ->with('realm_id')
            ->andReturn('realm_id');

        $this->token_mock
            ->shouldReceive('getAttribute')
            ->once()
            ->with('refresh_token')
            ->andReturn('refresh_token');

        $this->assertTrue(
            $this->client
                ->getDataService()
                ->getServiceContext()
                ->IppConfiguration->Security->isAccessTokenSet(),
        );
    }

    /**
     * @test
     */
    public function it_returns_a_data_service_with_refreshed_token_when_access_token_expired_but_refresh_token_valid()
    {
        $this->expectException(ServiceException::class);

        $this->token_mock
            ->shouldReceive('getAttribute')
            ->twice()
            ->with('hasValidAccessToken')
            ->andReturnFalse();

        $this->token_mock
            ->shouldReceive('getAttribute')
            ->once()
            ->with('hasValidRefreshToken')
            ->andReturnTrue();

        $this->token_mock
            ->shouldReceive('getAttribute')
            ->never()
            ->with('access_token');

        $this->token_mock
            ->shouldReceive('getAttribute')
            ->once()
            ->with('realm_id')
            ->andReturn('realm_id');

        $this->token_mock
            ->shouldReceive('getAttribute')
            ->once()
            ->with('refresh_token')
            ->andReturn('refresh_token');

        // TODO: This really needs to test that things are getting called correctly on the DataService class
        $this->assertFalse(
            $this->client
                ->getDataService()
                ->getServiceContext()
                ->IppConfiguration->Security->isAccessTokenSet(),
        );
    }

    /**
     * @test
     */
    public function it_returns_a_report_service_using_the_data_service()
    {
        $this->markTestSkipped(
            'Once we figure out how to test around the static DataService::Configure',
        );
    }

    /**
     * @test
     */
    public function it_has_logging_off_by_default()
    {
        $this->markTestSkipped('Have to figure out how to test this with the new code in 5.x');

        $this->token_mock
            ->shouldReceive('getAttribute')
            ->once()
            ->with('hasValidAccessToken')
            ->andReturnFalse();

        $this->token_mock
            ->shouldReceive('getAttribute')
            ->once()
            ->with('hasValidRefreshToken')
            ->andReturnFalse();

        $this->assertFalse(
            filter_var(
                $this->client->getDataService()->getServiceContext()->IppConfiguration->Logger
                    ->RequestLog->EnableRequestResponseLogging,
                FILTER_VALIDATE_BOOLEAN,
            ),
        );
    }

    /**
     * @test
     */
    public function it_allows_logging_turned_on_and_pointed_to_expected_file()
    {
        $this->markTestSkipped('Have to figure out how to test this with the new code in 5.x');

        $this->client = $this->makeClient(
            array_merge(
                [
                    'logging' => [
                        'enabled' => true,
                        'location' => '/some/valid/path',
                    ],
                ],
                $this->configs,
            ),
        );

        $this->token_mock
            ->shouldReceive('getAttribute')
            ->once()
            ->with('hasValidAccessToken')
            ->andReturnFalse();

        $this->token_mock
            ->shouldReceive('getAttribute')
            ->once()
            ->with('hasValidRefreshToken')
            ->andReturnFalse();

        $this->assertTrue(
            filter_var(
                $this->client->getDataService()->getServiceContext()->IppConfiguration->Logger
                    ->RequestLog->EnableRequestResponseLogging,
                FILTER_VALIDATE_BOOLEAN,
            ),
        );

        $this->assertEquals(
            '/some/valid/path',
            $this->client->getDataService()->getServiceContext()->IppConfiguration->Logger
                ->RequestLog->ServiceRequestLoggingLocation,
        );
    }

    /**
     * @test
     */
    public function it_returns_self_after_deleting_token()
    {
        $this->token_mock
            ->shouldReceive('remove')
            ->once()
            ->withNoArgs()
            ->andReturnSelf();

        $this->assertInstanceOf(Client::class, $this->client->deleteToken());
    }
}

function dir($path)
{
    return $path === '/some/valid/path';
}

function route($name)
{
    return $name;
}
