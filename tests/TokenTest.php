<?php

namespace Spinen\QuickBooks;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Mockery;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;
use Spinen\QuickBooks\Stubs\TokenStub;
use Spinen\QuickBooks\Stubs\User;

/**
 * Class TokenTest
 */
class TokenTest extends TestCase
{
    /**
     * @var Token
     */
    protected $token;

    protected function setUp(): void
    {
        $this->token = new TokenStub();
    }

    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(Token::class, $this->token);
    }

    /**
     * @test
     */
    public function it_has_accessor_for_valid_access_token()
    {
        $this->assertNotNull($this->token->getHasValidAccessTokenAttribute());
    }

    /**
     * @test
     */
    public function it_has_accessor_for_valid_refresh_token()
    {
        $this->assertNotNull($this->token->getHasValidRefreshTokenAttribute());
    }

    /**
     * @test
     */
    public function it_knows_that_the_access_token_expires_at_has_to_be_valid()
    {
        $this->assertFalse($this->token->getHasValidAccessTokenAttribute());
    }

    /**
     * @test
     */
    public function it_knows_that_the_refresh_token_expires_at_has_to_be_valid()
    {
        $this->assertFalse($this->token->getHasValidRefreshTokenAttribute());
    }

    /**
     * @test
     */
    public function it_know_if_access_token_expires_at_is_less_than_now_it_is_not_expired()
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $this->token->access_token_expires_at = Carbon::now()->addSecond();

        $this->assertTrue($this->token->getHasValidAccessTokenAttribute());
    }

    /**
     * @test
     */
    public function it_know_if_refresh_token_expires_at_is_less_than_now_it_is_not_expired()
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $this->token->refresh_token_expires_at = Carbon::now()->addSecond();

        $this->assertTrue($this->token->getHasValidRefreshTokenAttribute());
    }

    /**
     * @test
     */
    public function it_know_if_access_token_expires_at_is_greater_than_or_equal_to_now_it_is_expired()
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $this->token->access_token_expires_at = Carbon::now();

        $this->assertFalse($this->token->getHasValidAccessTokenAttribute());

        $this->token->access_token_expires_at->subSecond();

        $this->assertFalse($this->token->getHasValidAccessTokenAttribute());
    }

    /**
     * @test
     */
    public function it_know_if_refresh_token_expires_at_is_greater_than_or_equal_to_now_it_is_expired()
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $this->token->refresh_token_expires_at = Carbon::now();

        $this->assertFalse($this->token->getHasValidRefreshTokenAttribute());

        $this->token->refresh_token_expires_at->subSecond();

        $this->assertFalse($this->token->getHasValidRefreshTokenAttribute());
    }

    /**
     * @test
     */
    public function it_stores_the_oauth_token_parts_in_expected_properties()
    {
        $oauth_token_mock = Mockery::mock(OAuth2AccessToken::class);

        $oauth_token_mock
            ->shouldReceive('getAccessToken')
            ->once()
            ->withNoArgs()
            ->andReturn('access_token');

        $oauth_token_mock
            ->shouldReceive('getAccessTokenExpiresAt')
            ->once()
            ->withNoArgs()
            ->andReturn('now');

        $oauth_token_mock
            ->shouldReceive('getRealmID')
            ->once()
            ->withNoArgs()
            ->andReturn('realm_id');

        $oauth_token_mock
            ->shouldReceive('getRefreshToken')
            ->once()
            ->withNoArgs()
            ->andReturn('refresh_token');

        $oauth_token_mock
            ->shouldReceive('getRefreshTokenExpiresAt')
            ->once()
            ->withNoArgs()
            ->andReturn('now');

        $this->token->parseOauthToken($oauth_token_mock);
    }

    /**
     * @test
     */
    public function it_allows_itself_to_be_deleted_and_returns_new_token()
    {
        $this->token->user = Mockery::mock(User::class);

        $has_one_mock = Mockery::mock(HasOne::class);
        $token_mock = Mockery::mock(Token::class);

        $has_one_mock
            ->shouldReceive('make')
            ->once()
            ->withNoArgs()
            ->andReturn($token_mock);

        $this->token->user
            ->shouldReceive('quickBooksToken')
            ->once()
            ->withNoArgs()
            ->andReturn($has_one_mock);

        $this->token->user->id = 1;

        $this->assertEquals($token_mock, $this->token->remove());
    }

    /**
     * @test
     */
    public function it_get_related_user_model_from_configuration()
    {
        $this->assertInstanceOf(User::class, $this->token->user()->getModel());
    }
}

function config($key)
{
    return [
        'keys' => [
            'foreign' => 'user_id',
            'owner' => 'id',
        ],
        'model' => User::class,
    ];
}
