<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Authenticator;

use Generated\Shared\Transfer\CustomerTransfer;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToCustomerClientInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CustomerAuthenticator implements CustomerAuthenticatorInterface
{
    /**
     * @var \SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToCustomerClientInterface
     */
    protected $customerClient;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $tokenStorage;

    public function __construct(
        CustomerPageToCustomerClientInterface $customerClient,
        TokenStorageInterface $tokenStorage
    ) {
        $this->customerClient = $customerClient;
        $this->tokenStorage = $tokenStorage;
    }

    public function authenticateCustomer(CustomerTransfer $customerTransfer, TokenInterface $token): void
    {
        $this->tokenStorage->setToken($token);
        $this->customerClient->setCustomer($customerTransfer);
    }
}
