<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Expander;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CustomerTransfer;

interface CustomerAddressExpanderInterface
{
    public function expandWithCustomerAddress(AddressTransfer $addressTransfer, ?CustomerTransfer $customerTransfer): AddressTransfer;
}
