<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CustomerPage\Plugin\Provider;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerTransfer;
use ReflectionMethod;
use SprykerShop\Yves\CustomerPage\CustomerPageFactory;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToCustomerClientInterface;
use SprykerShop\Yves\CustomerPage\Plugin\Provider\CustomerUserProvider;
use SprykerShop\Yves\CustomerPage\Security\Customer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerShopTest
 * @group Yves
 * @group CustomerPage
 * @group Plugin
 * @group Provider
 * @group CustomerUserProviderTest
 * Add your own group annotations below this line
 */
class CustomerUserProviderTest extends Unit
{
    protected const string TEST_EMAIL = 'test@example.com';

    protected const string PASSWORD_HASH = '$2y$12$examplehashedpassword';

    public function testLoadUserByIdentifierUsesAuthenticationMethodThatIncludesPasswordHash(): void
    {
        // Arrange
        $customerTransfer = (new CustomerTransfer())
            ->setEmail(static::TEST_EMAIL)
            ->setIdCustomer(1)
            ->setPassword(static::PASSWORD_HASH);

        $customerClientMock = $this->createMock(CustomerPageToCustomerClientInterface::class);
        $customerClientMock
            ->expects($this->once())
            ->method('getCustomerForAuthentication')
            ->with($this->callback(fn (CustomerTransfer $transfer) => $transfer->getEmail() === static::TEST_EMAIL))
            ->willReturn($customerTransfer);

        $factoryMock = $this->getMockBuilder(CustomerPageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCustomerClient', 'createSecurityUser'])
            ->getMock();

        $factoryMock->method('getCustomerClient')->willReturn($customerClientMock);
        $factoryMock->method('createSecurityUser')->willReturn(
            new Customer($customerTransfer, static::TEST_EMAIL, static::PASSWORD_HASH, []),
        );

        $provider = new CustomerUserProvider();
        $provider->setFactory($factoryMock);

        // Act
        $provider->loadUserByIdentifier(static::TEST_EMAIL);

        // Assert – expectation on getCustomerForAuthentication::once() above covers this.
    }

    public function testUpdateUserStripsPasswordBeforeSessionStorage(): void
    {
        // Arrange
        $customerTransfer = (new CustomerTransfer())
            ->setEmail(static::TEST_EMAIL)
            ->setIdCustomer(1)
            ->setPassword(static::PASSWORD_HASH);

        $customerClientMock = $this->createMock(CustomerPageToCustomerClientInterface::class);
        $customerClientMock->method('isLoggedIn')->willReturn(false);
        $customerClientMock
            ->method('getCustomerForAuthentication')
            ->willReturn($customerTransfer);

        $customerClientMock
            ->expects($this->once())
            ->method('setCustomer')
            ->with($this->callback(
                fn (CustomerTransfer $stored) => $stored->getPassword() === null,
            ));

        $factoryMock = $this->getMockBuilder(CustomerPageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCustomerClient', 'createSecurityUser'])
            ->getMock();

        $factoryMock->method('getCustomerClient')->willReturn($customerClientMock);
        $factoryMock->method('createSecurityUser')->willReturn(
            new Customer($customerTransfer, static::TEST_EMAIL, static::PASSWORD_HASH, []),
        );

        $userMock = $this->createMock(Customer::class);
        $userMock->method('getUserIdentifier')->willReturn(static::TEST_EMAIL);
        $userMock->method('getPassword')->willReturn(null);

        $provider = new CustomerUserProvider();
        $provider->setFactory($factoryMock);

        // Act – refreshUser triggers updateUser when isDirty; test updateUser directly via reflection
        $reflection = new ReflectionMethod($provider, 'updateUser');
        $reflection->setAccessible(true);
        $reflection->invoke($provider, $userMock);

        // Assert – setCustomer expectation above covers this.
    }
}
