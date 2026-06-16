<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CustomerPage\UserChecker;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerTransfer;
use SprykerShop\Yves\CustomerPage\CustomerPageConfig;
use SprykerShop\Yves\CustomerPage\Exception\NotConfirmedAccountException;
use SprykerShop\Yves\CustomerPage\Security\CustomerUserInterface;
use SprykerShop\Yves\CustomerPage\UserChecker\CustomerConfirmationUserChecker;
use SprykerShopTest\Yves\CustomerPage\CustomerPageTester;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @group SprykerShopTest
 * @group Yves
 * @group CustomerPage
 * @group UserChecker
 * @group CustomerConfirmationUserCheckerTest
 */
class CustomerConfirmationUserCheckerTest extends Unit
{
    /**
     * @var \SprykerShopTest\Yves\CustomerPage\CustomerPageTester
     */
    protected CustomerPageTester $tester;

    /**
     * @dataProvider getCheckPreAuthData
     *
     * @param bool $isDoubleOptInEnabled
     * @param string|null $registered
     * @param bool $expectException
     *
     * @return void
     */
    public function testCheckPreAuth(bool $isDoubleOptInEnabled, ?string $registered, bool $expectException): void
    {
        // Arrange
        $configMock = $this->createMock(CustomerPageConfig::class);
        $configMock->method('isDoubleOptInEnabled')->willReturn($isDoubleOptInEnabled);

        $customerTransfer = (new CustomerTransfer())->setRegistered($registered);

        $userMock = $this->createMock(CustomerUserInterface::class);
        $userMock->method('getCustomerTransfer')->willReturn($customerTransfer);

        $checker = new CustomerConfirmationUserChecker([], $configMock);

        if ($expectException) {
            $this->expectException(NotConfirmedAccountException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        // Act
        $checker->checkPreAuth($userMock);
    }

    public function testCheckPreAuthSkipsDoubleOptInCheckForNonCustomerUser(): void
    {
        // Arrange
        $configMock = $this->createMock(CustomerPageConfig::class);
        $configMock->expects($this->never())->method('isDoubleOptInEnabled');

        $checker = new CustomerConfirmationUserChecker([], $configMock);

        // Assert — non-customer users are passed through without any confirmation check
        $this->expectNotToPerformAssertions();

        // Act
        $checker->checkPreAuth($this->createMock(UserInterface::class));
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function getCheckPreAuthData(): array
    {
        return [
            'double opt-in disabled, customer not registered' => [false, null, false],
            'double opt-in enabled, customer not registered' => [true, null, true],
            'double opt-in enabled, customer registered' => [true, '2024-01-01 00:00:00', false],
        ];
    }
}
