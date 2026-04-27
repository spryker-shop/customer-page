<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Controller;

use LogicException;
use Spryker\Yves\Kernel\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerShop\Yves\CustomerPage\CustomerPageFactory getFactory()
 * @method \SprykerShop\Yves\CustomerPage\CustomerPageConfig getConfig()
 */
class OauthLoginController extends AbstractController
{
    public function callbackAction(Request $request): never
    {
        throw new LogicException(
            'This action must never execute — it is intercepted by OauthCustomerTokenAuthenticator before reaching the controller. '
            . 'If you see this exception, the OAuth firewall is not configured correctly. Check that: '
            . '(1) OauthCustomerSecurityPlugin is registered in the Yves SecurityDependencyProvider, '
            . '(2) it is registered AFTER the base customer security plugin so the "secured" firewall exists before being expanded, '
            . '(3) the "secured" firewall covers the OAuth callback route defined in CustomerPageConfig::getOauthCallbackRoutePath().',
        );
    }
}
