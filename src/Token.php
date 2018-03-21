<?php

namespace Spinen\QuickBooks;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;

/**
 * Class Token
 *
 * @package Spinen\QuickBooks
 */
class Token extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quickbooks_tokens';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'access_token_expires_at',
        'refresh_token_expires_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'access_token',
        'access_token_expires_at',
        'realm_id',
        'refresh_token',
        'refresh_token_expires_at',
        'user_id',
    ];

    /**
     * Check if access token is valid
     *
     * A token is good for 1 hour, so if it expires greater than 1 hour from now, it is still valid
     *
     * @return bool
     */
    public function getHasValidAccessTokenAttribute()
    {
        return $this->access_token_expires_at && Carbon::now()
                                                       ->lt($this->access_token_expires_at);
    }

    /**
     * Check if refresh token is valid
     *
     * A token is good for 101 days, so if it expires greater than 101 days from now, it is still valid
     *
     * @return bool
     */
    public function getHasValidRefreshTokenAttribute()
    {
        return $this->refresh_token_expires_at && Carbon::now()
                                                        ->lt($this->refresh_token_expires_at);
    }

    /**
     * Parse OauthToken.
     *
     * Process the OAuth token & store it in the persistent storage
     *
     * @param OAuth2AccessToken $oauth_token
     *
     * @return Token
     * @throws \QuickBooksOnline\API\Exception\SdkException
     */
    public function parseOauthToken(OAuth2AccessToken $oauth_token)
    {
        // TODO: Deal with exception
        $this->access_token = $oauth_token->getAccessToken();
        $this->access_token_expires_at = Carbon::parse($oauth_token->getAccessTokenExpiresAt());
        $this->realm_id = $oauth_token->getRealmID();
        $this->refresh_token = $oauth_token->getRefreshToken();
        $this->refresh_token_expires_at = Carbon::parse($oauth_token->getRefreshTokenExpiresAt());

        return $this;
    }

    /**
     * Remove the token
     *
     * When a token is deleted, we still need a token for the client for the user.
     *
     * @return Token
     * @throws \Exception
     */
    public function remove()
    {
        $user = $this->user;

        $this->delete();

        return $user->quickBooksToken()
                    ->make();
    }

    /**
     * Belongs to user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        $config = config('quickbooks.user');

        return $this->belongsTo($config['model'], $config['keys']['foreign'], $config['keys']['owner']);
    }
}
