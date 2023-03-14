<?php

namespace Spinen\QuickBooks\Http\Controllers;

use Exception;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as LaravelController;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use QuickBooksOnline\API\Exception\SdkException;
use QuickBooksOnline\API\Exception\ServiceException;
use Spinen\QuickBooks\Client as QuickBooks;

/**
 * Class Controller
 */
class Controller extends LaravelController
{
    // NOTE: When using constructor injection for QuickBooks, there is a race issue with the boot order of the app

    /**
     * Form to connect/disconnect user to QuickBooks
     *
     * If the user has a valid OAuth, then give form to disconnect, otherwise link to connect it
     *
     * @throws SdkException
     * @throws ServiceException
     */
    public function connect(QuickBooks $quickbooks, ViewFactory $view_factory): ViewContract|View
    {
        // Give view to remove token if user already linked account
        if ($quickbooks->hasValidRefreshToken()) {
            return $view_factory
                ->make('quickbooks::disconnect')
                ->with('company', $quickbooks->getDataService()->getCompanyInfo());
        }

        // Give view to link account
        return $view_factory
            ->make('quickbooks::connect')
            ->with('authorization_uri', $quickbooks->authorizationUri());
    }

    /**
     * Removes the token
     *
     * @throws Exception
     */
    public function disconnect(
        Redirector $redirector,
        Request $request,
        QuickBooks $quickbooks,
    ): RedirectResponse|View {
        $quickbooks->deleteToken();

        $request->session()->flash('success', 'Disconnected from QuickBooks');

        return $redirector->back();
    }

    /**
     * Accept the code from QuickBooks to request token
     *
     * Once a user approves linking account, then QuickBooks sends back
     * a code which can be converted to an OAuth token.
     *
     * @throws SdkException
     * @throws ServiceException
     */
    public function token(
        Redirector $redirector,
        Request $request,
        QuickBooks $quickbooks,
        UrlGenerator $url_generator,
    ): RedirectResponse {
        // TODO: Deal with exceptions
        $quickbooks->exchangeCodeForToken($request->get('code'), $request->get('realmId'));

        $request->session()->flash('success', 'Connected to QuickBooks');

        return $redirector->intended($url_generator->route('quickbooks.connect'));
    }
}
