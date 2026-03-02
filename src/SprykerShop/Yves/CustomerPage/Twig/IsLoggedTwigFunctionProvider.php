<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Twig;

use Spryker\Shared\Twig\TwigFunctionProvider;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToCustomerClientInterface;

class IsLoggedTwigFunctionProvider extends TwigFunctionProvider
{
    /**
     * @var string
     */
    protected const TWIG_FUNCTION_NAME_IS_LOGGED_IN = 'isLoggedIn';

    /**
     * @var \SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToCustomerClientInterface
     */
    protected $customerClient;

    public function __construct(CustomerPageToCustomerClientInterface $customerClient)
    {
        $this->customerClient = $customerClient;
    }

    public function getFunctionName(): string
    {
        return static::TWIG_FUNCTION_NAME_IS_LOGGED_IN;
    }

    public function getFunction(): callable
    {
        return function (): bool {
            return $this->customerClient->isLoggedIn();
        };
    }
}
