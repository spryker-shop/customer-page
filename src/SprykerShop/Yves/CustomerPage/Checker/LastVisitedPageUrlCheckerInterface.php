<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Checker;

use Symfony\Component\HttpFoundation\Request;

interface LastVisitedPageUrlCheckerInterface
{
    public function isEligibleForPostLoginRedirect(Request $request): bool;
}
