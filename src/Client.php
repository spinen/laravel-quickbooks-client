<?php

namespace Spinen\QuickBooks;

use Exception;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Exception\SdkException;
use QuickBooksOnline\API\Exception\ServiceException;
use QuickBooksOnline\API\ReportService\ReportService;

/**
 * Class Client
 *
 * @package Spinen\QuickBooks
 */
class Client
{
    /**
     * The configs to set up a DataService
     *
     * @var array
     */
    protected $configs;

    /**
     * The DataService instance
     *
     * @var DataService
     */
    protected $data_service;

    /**
     * The ReportService instance
     *
     * @var ReportService
     */
    protected $report_service;

    /**
     * The Token instance
     *
     * @var Token
     */
    protected $token;

    /**
     * Client constructor.
     *
     * @param array $configs
     * @param Token $token
     */
    public function __construct(array $configs, Token $token)
    {
        $this->configs = $configs;

        $this->setToken($token);
    }

    /**
     * Build URI to request authorization
     *
     * @return String
     * @throws SdkException
     * @throws ServiceException
     */
    public function authorizationUri()
    {
        return $this->getDataService()
                    ->getOAuth2LoginHelper()
                    ->getAuthorizationCodeURL();
    }

    /**
     * Configure the logging per config/quickbooks.php
     *
     * @return DataService
     */
    public function configureLogging()
    {
        // In case any of the keys are not in the configs, just disable logging
        try {
            if ($this->configs['logging']['enabled'] && dir($this->configs['logging']['location'])) {
                $this->data_service->setLogLocation($this->configs['logging']['location']);

                return $this->data_service->enableLog();
            }
        } catch (Exception $e) {
            // TODO: Figure out what to do with this exception
        }

        return $this->data_service->disableLog();
    }

    /**
     * Delete the token
     *
     * @return $this
     * @throws Exception
     */
    public function deleteToken()
    {
        $this->setToken($this->token->remove());

        return $this;
    }

    /**
     * Convert code to an access token
     *
     * Upon the user allowing access to their account, there is a code sent to
     * over that needs to be converted to an OAuth token.
     *
     * @param string $code
     * @param integer $realm_id
     *
     * @return $this
     * @throws SdkException
     * @throws ServiceException
     */
    public function exchangeCodeForToken($code, $realm_id)
    {
        $oauth_token = $this->getDataService()
                            ->getOAuth2LoginHelper()
                            ->exchangeAuthorizationCodeForToken($code, $realm_id);

        $this->getDataService()
             ->updateOAuth2Token($oauth_token);

        $this->token->parseOauthToken($oauth_token)
                    ->save();

        return $this;
    }

    /**
     * Getter for the DataService
     *
     * Makes sure that it is setup & ready to be used.
     *
     * @return DataService
     * @throws SdkException
     * @throws ServiceException
     */
    public function getDataService()
    {
        if (!$this->hasValidAccessToken() || !isset($this->data_service)) {
            $this->data_service = $this->makeDataService();

            $this->configureLogging();
        }

        return $this->data_service;
    }

    /**
     * Getter for the ReportService
     *
     * Makes sure that it is setup & ready to be used.
     *
     * @return ReportService
     * @throws SdkException
     * @throws ServiceException
     */
    public function getReportService()
    {
        if (!$this->hasValidAccessToken() || !isset($this->report_service)) {
            $this->report_service = new ReportService(
                $this->getDataService()
                     ->getServiceContext()
            );
        }

        return $this->report_service;
    }

    /**
     * Check to see if the token has a valid access token
     *
     * @return boolean
     */
    public function hasValidAccessToken()
    {
        return $this->token->hasValidAccessToken;
    }

    /**
     * Check to see if the token has a valid refresh token
     *
     * @return boolean
     */
    public function hasValidRefreshToken()
    {
        return $this->token->hasValidRefreshToken;
    }

    /**
     * Factory to make DataService
     *
     * There are 3 use cases for making a DataService....
     *
     *      1) Have valid access token, so ready to be used
     *      2) Have valid refresh token, so renew access token & then use
     *      3) No existing token, so need to link account
     *
     * @return DataService
     * @throws SdkException
     * @throws ServiceException
     */
    protected function makeDataService()
    {
        // Associative array to use to filter out only the needed config keys when using existing token
        $existing_keys = [
            'auth_mode'    => null,
            'baseUrl'      => null,
            'ClientID'     => null,
            'ClientSecret' => null,
        ];

        // Have good access & refresh, so allow app to run
        if ($this->hasValidAccessToken()) {
            // Pull in the configs from the token into needed keys from the configs
            return DataService::Configure(
                array_merge(
                    array_intersect_key($this->parseDataConfigs(), $existing_keys),
                    [
                        'accessTokenKey'  => $this->token->access_token,
                        'QBORealmID'      => $this->token->realm_id,
                        'refreshTokenKey' => $this->token->refresh_token,
                    ]
                )
            );
        }

        // Have refresh, so update access & allow app to run
        if ($this->hasValidRefreshToken()) {
            // Pull in the configs from the token into needed keys from the configs
            $data_service = DataService::Configure(
                array_merge(
                    array_intersect_key($this->parseDataConfigs(), $existing_keys),
                    [
                        'QBORealmID'      => $this->token->realm_id,
                        'refreshTokenKey' => $this->token->refresh_token,
                    ]
                )
            );

            $oauth_token = $data_service->getOAuth2LoginHelper()
                                        ->refreshToken();

            $data_service->updateOAuth2Token($oauth_token);

            $this->token->parseOauthToken($oauth_token)
                        ->save();

            return $data_service;
        }

        // Create new...
        return DataService::Configure($this->parseDataConfigs());
    }

    /**
     * QuickBooks is not consistent on their naming of variables, so map them
     */
    protected function parseDataConfigs()
    {
        return [
            'auth_mode'    => $this->configs['data_service']['auth_mode'],
            'baseUrl'      => $this->configs['data_service']['base_url'],
            'ClientID'     => $this->configs['data_service']['client_id'],
            'ClientSecret' => $this->configs['data_service']['client_secret'],
            'RedirectURI'  => route('quickbooks.token'),
            'scope'        => $this->configs['data_service']['scope'],
        ];
    }

    /**
     * Allow setting a token to switch "user"
     *
     * @param Token $token
     *
     * @return $this
     */
    public function setToken(Token $token)
    {
        $this->token = $token;

        // The DataService is tied to a specific token, so remove it when using a new one
        unset($this->data_service);

        return $this;
    }
}
