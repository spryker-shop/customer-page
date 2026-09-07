<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Oauth\Security\Handler;

use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\Yves\Router\Router\ChainRouter;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToCustomerClientInterface;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToSessionClientInterface;
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

    protected const string ACCESS_MODE_PRE_AUTH = 'ACCESS_MODE_PRE_AUTH';

    /**
     * @uses \SprykerShop\Yves\CustomerPage\Plugin\Provider\CustomerAuthenticationSuccessHandler::MULTI_FACTOR_AUTH_LOGIN_CUSTOMER_EMAIL_SESSION_KEY
     */
    protected const string MULTI_FACTOR_AUTH_LOGIN_CUSTOMER_EMAIL_SESSION_KEY = '_multi_factor_auth_login_customer_email';

    /**
     * @uses \Spryker\Yves\MultiFactorAuth\Plugin\Router\Customer\MultiFactorAuthCustomerRouteProviderPlugin::MULTI_FACTOR_AUTH_NAME_GET_CUSTOMER_OAUTH_LOGIN_ENABLED_TYPES
     */
    protected const string ROUTE_CUSTOMER_OAUTH_MFA = 'multiFactorAuth/customerLogin';

    protected const string ROUTE_CUSTOMER_OVERVIEW = '/customer/overview';

    public function __construct(
        protected CustomerPageToCustomerClientInterface $customerClient,
        protected CustomerPageToSessionClientInterface $sessionClient,
        protected ChainRouter $router,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $customerTransfer = $request->attributes->get(static::REQUEST_ATTRIBUTE_CUSTOMER);

        if (in_array(static::ACCESS_MODE_PRE_AUTH, $token->getRoleNames(), true)) {
            if ($customerTransfer instanceof CustomerTransfer) {
                $this->sessionClient->set(
                    static::MULTI_FACTOR_AUTH_LOGIN_CUSTOMER_EMAIL_SESSION_KEY,
                    $customerTransfer->getEmail(),
                );
            }

            return new RedirectResponse($this->router->generate(static::ROUTE_CUSTOMER_OAUTH_MFA));
        }

        if ($customerTransfer instanceof CustomerTransfer) {
            $this->customerClient->setCustomer($customerTransfer);
        }

        return new RedirectResponse(static::ROUTE_CUSTOMER_OVERVIEW);
    }
}
