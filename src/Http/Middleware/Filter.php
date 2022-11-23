<?php

namespace Spinen\QuickBooks\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Spinen\QuickBooks\Client as QuickBooks;

/**
 * Class Filter
 */
class Filter
{
    /**
     * Create a new QuickBooks filter middleware instance.
     */
    public function __construct(
        protected QuickBooks $quickbooks,
        protected Redirector $redirector,
        protected Session $session,
        protected UrlGenerator $url_generator,
    ) {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $this->quickbooks->hasValidRefreshToken()) {
            // Set intended route, so that after linking account, user is put where they were going
            $this->session->put('url.intended', $this->url_generator->to($request->path()));

            return $this->redirector->route('quickbooks.connect');
        }

        return $next($request);
    }
}
