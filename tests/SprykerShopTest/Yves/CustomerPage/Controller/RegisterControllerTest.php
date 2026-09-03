<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CustomerPage\Controller;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerResponseTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use SprykerShop\Shared\CustomerPage\CustomerPageConfig;
use SprykerShop\Yves\CustomerPage\Controller\RegisterController;
use SprykerShop\Yves\CustomerPage\CustomerPageFactory;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToCustomerClientInterface;
use SprykerShop\Yves\CustomerPage\Plugin\Router\CustomerPageRouteProviderPlugin;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group SprykerShopTest
 * @group Yves
 * @group CustomerPage
 * @group Controller
 * @group RegisterControllerTest
 */
class RegisterControllerTest extends Unit
{
    public function testExecuteConfirmActionWithLocaleRedirectsWithCorrectRoute(): void
    {
        // Arrange
        $token = 'test-registration-token';
        $locale = 'de_DE';

        $request = new Request([
            'token' => $token,
            CustomerPageConfig::URL_PARAM_LOCALE => $locale,
        ]);

        $registerControllerMock = $this->getMockBuilder(RegisterController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFactory', 'redirectResponseInternal', 'redirectWithLocale'])
            ->getMock();

        // Assert
        $registerControllerMock->expects($this->once())
            ->method('redirectWithLocale')
            ->with(
                CustomerPageRouteProviderPlugin::ROUTE_NAME_CONFIRM_REGISTRATION,
                $locale,
                ['token' => $token],
            )
            ->willReturn(new RedirectResponse('/'));

        // Act
        $registerControllerMock->confirmAction($request);
    }

    public function testExecuteConfirmActionRedirectsToOverviewWithErrorWhenCustomerAlreadyLoggedIn(): void
    {
        // Arrange
        $request = new Request(['token' => 'test-registration-token']);

        $registerControllerMock = $this->getMockBuilder(RegisterController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFactory', 'redirectResponseInternal', 'isLoggedInCustomer', 'addErrorMessage'])
            ->getMock();

        $registerControllerMock->method('isLoggedInCustomer')->willReturn(true);

        // Assert
        $registerControllerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with('customer_page.error.customer_already_logged_in');

        $registerControllerMock->expects($this->once())
            ->method('redirectResponseInternal')
            ->with(CustomerPageRouteProviderPlugin::ROUTE_NAME_CUSTOMER_OVERVIEW)
            ->willReturn(new RedirectResponse('/'));

        $registerControllerMock->expects($this->never())
            ->method('getFactory');

        // Act
        $registerControllerMock->confirmAction($request);
    }

    public function testExecuteConfirmActionConfirmsCustomerAndRedirectsToLoginWhenNotLoggedIn(): void
    {
        // Arrange
        $token = 'test-registration-token';
        $request = new Request(['token' => $token]);

        $customerResponseTransfer = (new CustomerResponseTransfer())->setIsSuccess(true);

        $customerClientMock = $this->getMockBuilder(CustomerPageToCustomerClientInterface::class)
            ->getMock();
        $customerClientMock->expects($this->once())
            ->method('confirmCustomerRegistration')
            ->with($this->callback(function (CustomerTransfer $customerTransfer) use ($token) {
                return $customerTransfer->getRegistrationKey() === $token;
            }))
            ->willReturn($customerResponseTransfer);

        $customerPageFactoryMock = $this->getMockBuilder(CustomerPageFactory::class)
            ->onlyMethods(['getCustomerClient'])
            ->getMock();
        $customerPageFactoryMock->method('getCustomerClient')->willReturn($customerClientMock);

        $registerControllerMock = $this->getMockBuilder(RegisterController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFactory', 'redirectResponseInternal', 'isLoggedInCustomer', 'addSuccessMessage'])
            ->getMock();

        $registerControllerMock->method('isLoggedInCustomer')->willReturn(false);
        $registerControllerMock->method('getFactory')->willReturn($customerPageFactoryMock);

        // Assert
        $registerControllerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('customer.authorization.account_confirmed');

        $registerControllerMock->expects($this->once())
            ->method('redirectResponseInternal')
            ->with('login')
            ->willReturn(new RedirectResponse('/'));

        // Act
        $registerControllerMock->confirmAction($request);
    }
}
