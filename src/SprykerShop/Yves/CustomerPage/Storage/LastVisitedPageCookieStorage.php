<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Storage;

use SprykerShop\Yves\CustomerPage\CustomerPageConfig;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LastVisitedPageCookieStorage implements LastVisitedPageStorageInterface
{
    public function __construct(protected CustomerPageConfig $config)
    {
    }

    public function save(Response $response, Request $request): void
    {
        $cookie = Cookie::create($this->config->getLastVisitedPageCookieName())
            ->withValue($request->getRequestUri())
            ->withPath($this->config->getLastVisitedPageCookiePath())
            ->withExpires($this->config->getLastVisitedPageCookieExpires())
            ->withSameSite($this->config->getLastVisitedPageCookieSameSite())
            ->withHttpOnly(true)
            ->withSecure($this->config->isLastVisitedPageCookieSecure());

        $response->headers->setCookie($cookie);
    }

    public function get(Request $request): string
    {
        return (string)$request->cookies->get($this->config->getLastVisitedPageCookieName(), '');
    }

    public function clear(Response $response): void
    {
        $response->headers->clearCookie($this->config->getLastVisitedPageCookieName(), $this->config->getLastVisitedPageCookiePath());
    }
}
