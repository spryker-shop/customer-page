<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Checker;

use Spryker\Service\Http\HttpServiceInterface;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToCustomerClientInterface;
use Symfony\Component\HttpFoundation\Request;

class LastVisitedPageUrlChecker implements LastVisitedPageUrlCheckerInterface
{
    public function __construct(
        protected CustomerPageToCustomerClientInterface $customerClient,
        protected HttpServiceInterface $httpService,
    ) {
    }

    public function isEligibleForPostLoginRedirect(Request $request): bool
    {
        if (!$this->customerClient->isLoggedIn()) {
            return false;
        }

        return $this->httpService->isRequestEligibleForRedirect($request);
    }
}
