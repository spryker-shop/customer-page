<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Oauth\Security\Handler;

use Generated\Shared\Transfer\CustomerTransfer;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToCustomerClientInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class OauthCustomerAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * @see \SprykerShop\Yves\CustomerPage\Oauth\Authenticator\OauthCustomerTokenAuthenticator::REQUEST_ATTRIBUTE_CUSTOMER
     */
    protected const string REQUEST_ATTRIBUTE_CUSTOMER = '_oauth_customer';

    public function __construct(protected CustomerPageToCustomerClientInterface $customerClient)
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $customerTransfer = $request->attributes->get(static::REQUEST_ATTRIBUTE_CUSTOMER);

        if ($customerTransfer instanceof CustomerTransfer) {
            $this->customerClient->setCustomer($customerTransfer);
        }

        return new RedirectResponse('/customer/overview');
    }
}
