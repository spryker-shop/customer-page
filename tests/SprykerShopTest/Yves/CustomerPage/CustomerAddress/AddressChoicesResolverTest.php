<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CustomerPage\CustomerAddress;

use Codeception\Test\Unit;
use SprykerShop\Yves\CustomerPage\Form\CheckoutAddressForm;
use SprykerShopTest\Yves\CustomerPage\CustomerPageTester;

/**
 * @group SprykerShopTest
 * @group Yves
 * @group CustomerPage
 * @group CustomerAddress
 * @group AddressChoicesResolverTest
 * Add your own group annotations below this line
 */
class AddressChoicesResolverTest extends Unit
{
    protected const int ID_CUSTOMER_ADDRESS_1 = 11;

    protected const int ID_CUSTOMER_ADDRESS_2 = 12;

    protected const int ID_CUSTOMER_ADDRESS_3 = 13;

    protected CustomerPageTester $tester;

    public function testGetAddressChoicesReturnsEveryCustomerAddressWhenSeveralAddressesShareTheSameLabel(): void
    {
        // Arrange
        $customerTransfer = $this->tester->createCustomerTransferWithAddressIds([
            static::ID_CUSTOMER_ADDRESS_1,
            static::ID_CUSTOMER_ADDRESS_2,
            static::ID_CUSTOMER_ADDRESS_3,
        ]);

        // Act
        $addressChoices = $this->tester->getFactory()->createAddressChoicesResolver()->getAddressChoices($customerTransfer);

        // Assert
        $this->assertEquals([
            CheckoutAddressForm::GLOSSARY_KEY_ACCOUNT_ADD_NEW_ADDRESS => CheckoutAddressForm::VALUE_ADD_NEW_ADDRESS,
            CustomerPageTester::ADDRESS_LABEL => static::ID_CUSTOMER_ADDRESS_1,
            CustomerPageTester::ADDRESS_LABEL . ' - 1' => static::ID_CUSTOMER_ADDRESS_2,
            CustomerPageTester::ADDRESS_LABEL . ' - 2' => static::ID_CUSTOMER_ADDRESS_3,
        ], $addressChoices);
    }

    public function testGetAddressChoicesKeepsLabelUnchangedWhenCustomerHasSingleAddress(): void
    {
        // Arrange
        $customerTransfer = $this->tester->createCustomerTransferWithAddressIds([static::ID_CUSTOMER_ADDRESS_1]);

        // Act
        $addressChoices = $this->tester->getFactory()->createAddressChoicesResolver()->getAddressChoices($customerTransfer);

        // Assert
        $this->assertEquals([
            CheckoutAddressForm::GLOSSARY_KEY_ACCOUNT_ADD_NEW_ADDRESS => CheckoutAddressForm::VALUE_ADD_NEW_ADDRESS,
            CustomerPageTester::ADDRESS_LABEL => static::ID_CUSTOMER_ADDRESS_1,
        ], $addressChoices);
    }
}
