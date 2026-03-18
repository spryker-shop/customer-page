<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Plugin\CustomerPage;

use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;
use SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\CustomerRedirectStrategyPluginInterface;

/**
 * @method \SprykerShop\Yves\CustomerPage\CustomerPageFactory getFactory()
 * @method \SprykerShop\Yves\CustomerPage\CustomerPageConfig getConfig()
 */
class LastVisitedPageCustomerRedirectStrategyPlugin extends AbstractPlugin implements CustomerRedirectStrategyPluginInterface
{
    /**
     * {@inheritDoc}
     * - Returns `true` if a valid last visited page URL is found in the last visited page storage.
     * - The storage strategy is configurable and defaults to cookie-based storage.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CustomerTransfer $customerTransfer
     *
     * @return bool
     */
    public function isApplicable(CustomerTransfer $customerTransfer): bool
    {
        return $this->getFactory()->createLastVisitedPageRedirectResolver()->isApplicable();
    }

    /**
     * {@inheritDoc}
     * - Returns the last visited Storefront page URL from the last visited page storage.
     * - The storage strategy is configurable and defaults to cookie-based storage.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CustomerTransfer $customerTransfer
     *
     * @return string
     */
    public function getRedirectUrl(CustomerTransfer $customerTransfer): string
    {
        return $this->getFactory()->createLastVisitedPageRedirectResolver()->getRedirectUrl();
    }
}
