<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Mapper;

use Generated\Shared\Transfer\AddressTransfer;

interface CustomerMapperInterface
{
    public function mapCustomerAddressTransferToAddressTransfer(
        AddressTransfer $customerAddressTransfer,
        AddressTransfer $addressTransfer
    ): AddressTransfer;
}
