<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CustomerPage\Authenticator;

use Codeception\Test\Unit;
use Spryker\Yves\Router\Router\ChainRouter;
use SprykerShop\Yves\CustomerPage\Authenticator\CustomerLoginFormAuthenticator;
use SprykerShop\Yves\CustomerPage\Badge\MultiFactorAuthBadge;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;

/**
 * @group SprykerShopTest
 * @group Yves
 * @group CustomerPage
 * @group Authenticator
 * @group CustomerLoginFormAuthenticatorTest
 */
class CustomerLoginFormAuthenticatorTest extends Unit
{
    protected const FAKE_LOGIN_URL = '/login';

    public function testStartReturnsRedirectForRegularRequest(): void
    {
        // Arrange
        $authenticator = $this->createAuthenticator();
        $request = new Request();

        // Act
        $response = $authenticator->start($request);

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame(static::FAKE_LOGIN_URL, $response->getTargetUrl());
    }

    public function testStartReturnsJsonUnauthorizedForXmlHttpRequest(): void
    {
        // Arrange
        $authenticator = $this->createAuthenticator();
        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        // Act
        $response = $authenticator->start($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('redirect', $data);
        $this->assertSame(static::FAKE_LOGIN_URL, $data['redirect']);
    }

    protected function createAuthenticator(): CustomerLoginFormAuthenticator
    {
        $routerMock = $this->createMock(ChainRouter::class);
        $routerMock->method('generate')->willReturn(static::FAKE_LOGIN_URL);

        return new CustomerLoginFormAuthenticator(
            $this->createMock(UserProviderInterface::class),
            $this->createMock(RememberMeBadge::class),
            $this->createMock(AuthenticationSuccessHandlerInterface::class),
            $this->createMock(AuthenticationFailureHandlerInterface::class),
            $routerMock,
            $this->createMock(MultiFactorAuthBadge::class),
        );
    }
}
