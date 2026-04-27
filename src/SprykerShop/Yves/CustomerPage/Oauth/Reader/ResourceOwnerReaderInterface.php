<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Oauth\Reader;

use Generated\Shared\Transfer\ResourceOwnerRequestTransfer;
use Generated\Shared\Transfer\ResourceOwnerResponseTransfer;

interface ResourceOwnerReaderInterface
{
    /**
     * @throws \SprykerShop\Yves\CustomerPage\Oauth\Exception\AuthenticationStrategyNotFoundException
     */
    public function getResourceOwner(ResourceOwnerRequestTransfer $resourceOwnerRequestTransfer): ResourceOwnerResponseTransfer;
}
