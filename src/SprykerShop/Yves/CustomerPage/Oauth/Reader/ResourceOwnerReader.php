<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Oauth\Reader;

use Generated\Shared\Transfer\ResourceOwnerRequestTransfer;
use Generated\Shared\Transfer\ResourceOwnerResponseTransfer;
use SprykerShop\Yves\CustomerPage\Oauth\Exception\AuthenticationStrategyNotFoundException;

class ResourceOwnerReader implements ResourceOwnerReaderInterface
{
    protected const string ERROR_MESSAGE_STRATEGY_NOT_FOUND = 'No OAuth customer client strategy found for the given state.';

    /**
     * @param array<\SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\OauthCustomerClientStrategyPluginInterface> $oauthCustomerClientStrategyPlugins
     */
    public function __construct(protected array $oauthCustomerClientStrategyPlugins)
    {
    }

    public function getResourceOwner(ResourceOwnerRequestTransfer $resourceOwnerRequestTransfer): ResourceOwnerResponseTransfer
    {
        foreach ($this->oauthCustomerClientStrategyPlugins as $oauthCustomerClientStrategyPlugin) {
            if ($oauthCustomerClientStrategyPlugin->isApplicable($resourceOwnerRequestTransfer)) {
                return $oauthCustomerClientStrategyPlugin->getResourceOwner($resourceOwnerRequestTransfer);
            }
        }

        throw new AuthenticationStrategyNotFoundException(static::ERROR_MESSAGE_STRATEGY_NOT_FOUND);
    }
}
