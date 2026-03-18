<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CustomerPage\Plugin\EventDispatcher;

use Codeception\Stub;
use Codeception\Test\Unit;
use Spryker\Shared\EventDispatcher\EventDispatcher;
use Spryker\Shared\EventDispatcherExtension\Dependency\Plugin\EventDispatcherPluginInterface;
use SprykerShop\Yves\CustomerPage\CustomerPageDependencyProvider;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToCustomerClientInterface;
use SprykerShop\Yves\CustomerPage\Plugin\EventDispatcher\LastVisitedPageEventDispatcherPlugin;
use SprykerShopTest\Yves\CustomerPage\CustomerPageTester;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Auto-generated group annotations
 *
 * @group SprykerShopTest
 * @group Yves
 * @group CustomerPage
 * @group Plugin
 * @group EventDispatcher
 * @group LastVisitedPageEventDispatcherPluginTest
 * Add your own group annotations below this line
 */
class LastVisitedPageEventDispatcherPluginTest extends Unit
{
    protected const string COOKIE_LAST_VISITED_PAGE = 'last-visited-page';

    protected const string URL_PRODUCT = '/en/products/123';

    protected CustomerPageTester $tester;

    public function testGivenLoggedInCustomerAndEligibleRequestWhenResponseDispatchedThenCookieIsSet(): void
    {
        // Arrange
        $this->mockCustomerClient(isLoggedIn: true);
        $plugin = $this->createPlugin();
        $request = Request::create(static::URL_PRODUCT);
        $event = $this->createResponseEvent($request);

        // Act
        $this->dispatchEvent($plugin, $event);

        // Assert
        $this->assertTrue($event->getResponse()->headers->has('Set-Cookie'));
        $this->assertStringContainsString(static::COOKIE_LAST_VISITED_PAGE, (string)$event->getResponse()->headers->get('Set-Cookie'));
    }

    public function testGivenCustomerNotLoggedInWhenResponseDispatchedThenCookieIsNotSet(): void
    {
        // Arrange
        $this->mockCustomerClient(isLoggedIn: false);
        $plugin = $this->createPlugin();
        $request = Request::create(static::URL_PRODUCT);
        $event = $this->createResponseEvent($request);

        // Act
        $this->dispatchEvent($plugin, $event);

        // Assert
        $this->assertFalse($event->getResponse()->headers->has('Set-Cookie'));
    }

    public function testGivenLogoutEventWithResponseWhenDispatchedThenCookieIsCleared(): void
    {
        // Arrange
        $this->mockCustomerClient(isLoggedIn: true);
        $plugin = $this->createPlugin();
        $response = new Response();
        $event = $this->createLogoutEvent($response);

        // Act
        $this->dispatchLogoutEvent($plugin, $event);

        // Assert
        $this->assertTrue($response->headers->has('Set-Cookie'));
        $this->assertStringContainsString(static::COOKIE_LAST_VISITED_PAGE, (string)$response->headers->get('Set-Cookie'));
    }

    public function testGivenLogoutEventWithoutResponseWhenDispatchedThenNothingHappens(): void
    {
        // Arrange
        $this->mockCustomerClient(isLoggedIn: true);
        $plugin = $this->createPlugin();
        $event = $this->createLogoutEvent(null);

        // Act
        $this->dispatchLogoutEvent($plugin, $event);

        // Assert
        $this->assertNull($event->getResponse());
    }

    protected function mockCustomerClient(bool $isLoggedIn): void
    {
        $customerClientMock = $this->getMockBuilder(CustomerPageToCustomerClientInterface::class)->getMock();
        $customerClientMock->method('isLoggedIn')->willReturn($isLoggedIn);

        $this->tester->setDependency(CustomerPageDependencyProvider::CLIENT_CUSTOMER, $customerClientMock);
    }

    protected function createPlugin(): LastVisitedPageEventDispatcherPlugin
    {
        $plugin = new LastVisitedPageEventDispatcherPlugin();
        $plugin->setFactory($this->tester->getFactory());

        return $plugin;
    }

    protected function dispatchEvent(EventDispatcherPluginInterface $plugin, ResponseEvent $event): void
    {
        $eventDispatcher = new EventDispatcher();
        $plugin->extend($eventDispatcher, $this->tester->getContainer());

        $eventDispatcher->dispatch($event, KernelEvents::RESPONSE);
    }

    protected function createResponseEvent(Request $request): ResponseEvent
    {
        /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $kernelMock */
        $kernelMock = Stub::makeEmpty(HttpKernelInterface::class);

        return new ResponseEvent($kernelMock, $request, HttpKernelInterface::MAIN_REQUEST, new Response());
    }

    protected function createLogoutEvent(?Response $response): LogoutEvent
    {
        $event = new LogoutEvent(Request::create('/logout'), null);

        if ($response !== null) {
            $event->setResponse($response);
        }

        return $event;
    }

    protected function dispatchLogoutEvent(EventDispatcherPluginInterface $plugin, LogoutEvent $event): void
    {
        $eventDispatcher = new EventDispatcher();
        $plugin->extend($eventDispatcher, $this->tester->getContainer());

        $eventDispatcher->dispatch($event, LogoutEvent::class);
    }
}
