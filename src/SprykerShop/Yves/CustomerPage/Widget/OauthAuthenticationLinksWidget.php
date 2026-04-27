<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Widget;

use Spryker\Yves\Kernel\Widget\AbstractWidget;

/**
 * @method \SprykerShop\Yves\CustomerPage\CustomerPageFactory getFactory()
 */
class OauthAuthenticationLinksWidget extends AbstractWidget
{
    protected const string PARAMETER_AUTHENTICATION_LINKS = 'authenticationLinks';

    public function __construct()
    {
        $this->addAuthenticationLinksParameter();
    }

    public static function getName(): string
    {
        return 'OauthAuthenticationLinksWidget';
    }

    public static function getTemplate(): string
    {
        return '@CustomerPage/views/oauth-authentication-links/oauth-authentication-links.twig';
    }

    protected function addAuthenticationLinksParameter(): void
    {
        $authenticationLinks = [];

        foreach ($this->getFactory()->getCustomerAuthenticationLinkPlugins() as $customerAuthenticationLinkPlugin) {
            $authenticationLinks = array_merge(
                $authenticationLinks,
                $customerAuthenticationLinkPlugin->getAuthenticationLinks(),
            );
        }

        $this->addParameter(static::PARAMETER_AUTHENTICATION_LINKS, $authenticationLinks);
    }
}
