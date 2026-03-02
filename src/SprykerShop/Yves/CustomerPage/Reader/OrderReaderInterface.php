<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Reader;

use Generated\Shared\Transfer\OrderListTransfer;
use Symfony\Component\HttpFoundation\Request;

interface OrderReaderInterface
{
    public function getOrderList(Request $request, OrderListTransfer $orderListTransfer): OrderListTransfer;
}
