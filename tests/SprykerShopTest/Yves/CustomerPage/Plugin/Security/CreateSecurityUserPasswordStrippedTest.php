<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CustomerPage\Plugin\Security;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerTransfer;
use SprykerShop\Yves\CustomerPage\CustomerPageFactory;

/**
 * Auto-generated group annotations
 *
 * @group SprykerShopTest
 * @group Yves
 * @group CustomerPage
 * @group Plugin
 * @group Security
 * @group CreateSecurityUserPasswordStrippedTest
 * Add your own group annotations below this line
 */
class CreateSecurityUserPasswordStrippedTest extends Unit
{
    public function testCreateSecurityUserStripsPasswordFromCustomerTransfer(): void
    {
        // Arrange
        $customerTransfer = (new CustomerTransfer())
            ->setEmail('test@example.com')
            ->setPassword('$2y$12$hashedpassword');

        $factory = $this->getMockBuilder(CustomerPageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        // Act
        $securityUser = $factory->createSecurityUser($customerTransfer);

        // Assert – the security user carries a copy without the password key: an explicit null would
        // still count as a modified field and travel into the session and
        // QuoteSyncRequestTransfer.quoteTransfer.customer through modifiedToArray() round-trips.
        $this->assertArrayNotHasKey(CustomerTransfer::PASSWORD, $securityUser->getCustomerTransfer()->modifiedToArray());
    }

    public function testCreateSecurityUserPreservesPasswordHashInSecurityUser(): void
    {
        // Arrange
        $passwordHash = '$2y$12$hashedpassword';
        $customerTransfer = (new CustomerTransfer())
            ->setEmail('test@example.com')
            ->setPassword($passwordHash);

        $factory = $this->getMockBuilder(CustomerPageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        // Act – security user must still carry the hash for Symfony authentication
        $securityUser = $factory->createSecurityUser($customerTransfer);

        // Assert
        $this->assertSame($passwordHash, $securityUser->getPassword());
    }
}
