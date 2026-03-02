<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Handler;

use Generated\Shared\Transfer\OrderListTransfer;
use Symfony\Component\Form\FormInterface;

interface OrderSearchFormHandlerInterface
{
    public function handleOrderSearchFormSubmit(
        FormInterface $orderSearchForm,
        OrderListTransfer $orderListTransfer
    ): OrderListTransfer;

    public function resetFilterFields(OrderListTransfer $orderListTransfer): OrderListTransfer;
}
