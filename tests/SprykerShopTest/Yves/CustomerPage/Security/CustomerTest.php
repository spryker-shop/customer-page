<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CustomerPage\Security;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerTransfer;
use SprykerShop\Yves\CustomerPage\Security\Customer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerShopTest
 * @group Yves
 * @group CustomerPage
 * @group Security
 * @group CustomerTest
 * Add your own group annotations below this line
 */
class CustomerTest extends Unit
{
    protected const string PASSWORD_HASH = '$2y$10$examplehashvalue1234567890';

    public function testCustomersWithSameRolesAreEqual(): void
    {
        // Arrange, Act, Assert
        $this->assertTrue($this->createCustomer()->isEqualTo($this->createCustomer()));
    }

    public function testCustomersStayEqualWhenPasswordDiffers(): void
    {
        // Arrange
        // The session copy of the customer never contains the password, so it must not
        // participate in the equality check; password changes invalidate the session
        // through the customer invalidation storage record instead.
        $customer = $this->createCustomer();
        $passwordlessCustomer = $this->createCustomer(null);

        // Act, Assert
        $this->assertTrue($customer->isEqualTo($passwordlessCustomer));
    }

    public function testCustomersWithDifferentRolesAreNotEqual(): void
    {
        // Arrange
        $customer = $this->createCustomer();
        $preAuthCustomer = $this->createCustomer(static::PASSWORD_HASH, ['ACCESS_MODE_PRE_AUTH']);

        // Act, Assert
        $this->assertFalse($customer->isEqualTo($preAuthCustomer));
    }

    public function testSerializationStripsPasswordAndPreservesEquality(): void
    {
        // Arrange
        $customer = $this->createCustomer();

        // Act
        $serializedCustomer = serialize($customer);
        $unserializedCustomer = unserialize($serializedCustomer);

        // Assert
        $this->assertStringNotContainsString(static::PASSWORD_HASH, $serializedCustomer);
        $this->assertNull($unserializedCustomer->getPassword());
        $this->assertTrue($unserializedCustomer->isEqualTo($this->createCustomer()));
    }

    public function testUnserializeAcceptsSessionWrittenBeforePasswordRemoval(): void
    {
        // Arrange
        $legacySerializedCustomer = $this->createLegacySerializedCustomer();

        // Act
        $unserializedCustomer = unserialize($legacySerializedCustomer);

        // Assert
        $this->assertInstanceOf(Customer::class, $unserializedCustomer);
        $this->assertNull($unserializedCustomer->getPassword());
        // The legacy transfer still carries the hash until the session is written again;
        // the next serialization must strip it.
        $this->assertStringNotContainsString(static::PASSWORD_HASH, serialize($unserializedCustomer));
        $this->assertTrue($unserializedCustomer->isEqualTo($this->createCustomer()));
    }

    public function testLegacySessionCustomerWithDifferentRolesIsNotEqual(): void
    {
        // Arrange
        $legacySerializedCustomer = $this->createLegacySerializedCustomer();

        // Act
        $unserializedCustomer = unserialize($legacySerializedCustomer);

        // Assert
        $this->assertFalse($unserializedCustomer->isEqualTo($this->createCustomer(static::PASSWORD_HASH, ['ACCESS_MODE_PRE_AUTH'])));
    }

    /**
     * Builds the exact payload the default object serialization produced before `__serialize()` existed:
     * protected property names prefixed with "\0*\0" and no `stateHash` entry.
     */
    protected function createLegacySerializedCustomer(): string
    {
        $customerTransfer = (new CustomerTransfer())
            ->setEmail('sonia@spryker.com')
            ->setCustomerReference('DE--21')
            ->setPassword(static::PASSWORD_HASH);

        $propertyTable = [
            "\0*\0customerTransfer" => $customerTransfer,
            "\0*\0username" => 'sonia@spryker.com',
            "\0*\0password" => static::PASSWORD_HASH,
            "\0*\0roles" => ['ROLE_USER'],
        ];
        $serializedPropertyTable = serialize($propertyTable);

        return sprintf(
            'O:%d:"%s":%d:{%s',
            strlen(Customer::class),
            Customer::class,
            count($propertyTable),
            substr($serializedPropertyTable, strpos($serializedPropertyTable, '{') + 1),
        );
    }

    /**
     * @param list<string> $roles
     */
    protected function createCustomer(?string $password = self::PASSWORD_HASH, array $roles = ['ROLE_USER']): Customer
    {
        $customerTransfer = (new CustomerTransfer())
            ->setEmail('sonia@spryker.com')
            ->setCustomerReference('DE--21')
            ->setPassword($password);

        return new Customer($customerTransfer, 'sonia@spryker.com', $password, $roles);
    }
}
