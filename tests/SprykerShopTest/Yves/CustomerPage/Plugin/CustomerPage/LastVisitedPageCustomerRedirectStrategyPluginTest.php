<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CustomerPage\Plugin\CustomerPage;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerTransfer;
use SprykerShop\Yves\CustomerPage\CustomerPageDependencyProvider;
use SprykerShop\Yves\CustomerPage\Plugin\CustomerPage\LastVisitedPageCustomerRedirectStrategyPlugin;
use SprykerShopTest\Yves\CustomerPage\CustomerPageTester;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Auto-generated group annotations
 *
 * @group SprykerShopTest
 * @group Yves
 * @group CustomerPage
 * @group Plugin
 * @group CustomerPage
 * @group LastVisitedPageCustomerRedirectStrategyPluginTest
 * Add your own group annotations below this line
 */
class LastVisitedPageCustomerRedirectStrategyPluginTest extends Unit
{
    protected const string LAST_VISITED_URL = '/en/products/123';

    protected CustomerPageTester $tester;

    public function testGivenLastVisitedCookiePresentWhenIsApplicableCalledThenReturnsTrue(): void
    {
        // Arrange
        $this->tester->setDependency(CustomerPageDependencyProvider::SERVICE_REQUEST_STACK, $this->createRequestStackWithCookie(static::LAST_VISITED_URL));
        $plugin = new LastVisitedPageCustomerRedirectStrategyPlugin();

        // Act
        $result = $plugin->isApplicable(new CustomerTransfer());

        // Assert
        $this->assertTrue($result);
    }

    public function testGivenNoLastVisitedCookieWhenIsApplicableCalledThenReturnsFalse(): void
    {
        // Arrange
        $requestStack = new RequestStack();
        $requestStack->push(Request::create(static::LAST_VISITED_URL));
        $this->tester->setDependency(CustomerPageDependencyProvider::SERVICE_REQUEST_STACK, $requestStack);
        $plugin = new LastVisitedPageCustomerRedirectStrategyPlugin();

        // Act
        $result = $plugin->isApplicable(new CustomerTransfer());

        // Assert
        $this->assertFalse($result);
    }

    public function testGivenLastVisitedCookiePresentWhenGetRedirectUrlCalledThenReturnsCookieValue(): void
    {
        // Arrange
        $this->tester->setDependency(CustomerPageDependencyProvider::SERVICE_REQUEST_STACK, $this->createRequestStackWithCookie(static::LAST_VISITED_URL));
        $plugin = new LastVisitedPageCustomerRedirectStrategyPlugin();

        // Act
        $result = $plugin->getRedirectUrl(new CustomerTransfer());

        // Assert
        $this->assertSame(static::LAST_VISITED_URL, $result);
    }

    protected function createRequestStackWithCookie(string $url): RequestStack
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create(static::LAST_VISITED_URL, Request::METHOD_GET, [], ['last-visited-page' => $url]));

        return $requestStack;
    }
}
