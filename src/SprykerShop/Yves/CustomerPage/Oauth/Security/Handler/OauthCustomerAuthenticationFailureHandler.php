<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Oauth\Security\Handler;

use Spryker\Yves\Messenger\FlashMessenger\FlashMessengerInterface;
use Spryker\Yves\Router\Router\ChainRouter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class OauthCustomerAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    /**
     * @uses \SprykerShop\Yves\CustomerPage\Plugin\Router\CustomerPageRouteProviderPlugin::ROUTE_NAME_LOGIN
     */
    protected const string ROUTE_LOGIN = 'login';

    public function __construct(
        protected FlashMessengerInterface $flashMessenger,
        protected ChainRouter $router,
    ) {
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $this->flashMessenger->addErrorMessage(
            strtr(
                $exception->getMessageKey(),
                $exception->getMessageData(),
            ),
        );

        return new RedirectResponse($this->router->generate(static::ROUTE_LOGIN));
    }
}
