<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CustomerPage;

use Codeception\Actor;
use Generated\Shared\Transfer\AddressesTransfer;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CustomerTransfer;

/**
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 * @method \SprykerShop\Yves\CustomerPage\CustomerPageFactory getFactory()
 *
 * @SuppressWarnings(\SprykerShopTest\Yves\CustomerPage\PHPMD)
 */
class CustomerPageTester extends Actor
{
    use _generated\CustomerPageTesterActions;

    /**
     * @var string
     */
    public const MOCKSESSID = 'MOCKSESSID';

    /**
     * Label rendered for every address built by {@link createCustomerTransferWithAddressIds()}.
     */
    public const string ADDRESS_LABEL = 'Mr John Doe, Street 1, 10115 Berlin';

    protected const string SALUTATION = 'Mr';

    protected const string FIRST_NAME = 'John';

    protected const string LAST_NAME = 'Doe';

    protected const string ADDRESS_1 = 'Street';

    protected const string ADDRESS_2 = '1';

    protected const string ZIP_CODE = '10115';

    protected const string CITY = 'Berlin';

    /**
     * @param array<int> $customerAddressIds
     */
    public function createCustomerTransferWithAddressIds(array $customerAddressIds): CustomerTransfer
    {
        $addressesTransfer = new AddressesTransfer();

        foreach ($customerAddressIds as $idCustomerAddress) {
            $addressesTransfer->addAddress(
                (new AddressTransfer())
                    ->setIdCustomerAddress($idCustomerAddress)
                    ->setSalutation(static::SALUTATION)
                    ->setFirstName(static::FIRST_NAME)
                    ->setLastName(static::LAST_NAME)
                    ->setAddress1(static::ADDRESS_1)
                    ->setAddress2(static::ADDRESS_2)
                    ->setZipCode(static::ZIP_CODE)
                    ->setCity(static::CITY),
            );
        }

        return (new CustomerTransfer())->setAddresses($addressesTransfer);
    }
}
