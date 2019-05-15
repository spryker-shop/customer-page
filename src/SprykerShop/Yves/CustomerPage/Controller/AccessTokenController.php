<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Controller;

use Generated\Shared\Transfer\CustomerTransfer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @method \SprykerShop\Yves\CustomerPage\CustomerPageFactory getFactory()
 * @method \Spryker\Client\Customer\CustomerClientInterface getClient()
 */
class AccessTokenController extends AbstractCustomerController
{
    /**
     * @uses \SprykerShop\Yves\CustomerPage\Plugin\Provider\CustomerPageControllerProvider::ROUTE_CUSTOMER_OVERVIEW
     */
    protected const ROUTE_CUSTOMER_OVERVIEW = 'customer/overview';

    protected const GLOSSARY_KEY_CUSTOMER_ALREADY_LOGGED_IN = 'customer_page.error.customer_already_logged_in';
    protected const GLOSSARY_KEY_INVALID_ACCESS_TOKEN = 'customer_page.error.invalid_access_token';

    /**
     * @param string $token
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(string $token): RedirectResponse
    {
        $response = $this->executeIndexAction($token);

        return $response;
    }

    /**
     * @param string $token
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function executeIndexAction(string $token): RedirectResponse
    {
        if ($this->isLoggedInCustomer()) {
            $this->addTranslatedErrorMessage(static::GLOSSARY_KEY_CUSTOMER_ALREADY_LOGGED_IN);

            return $this->redirectResponseInternal(static::ROUTE_CUSTOMER_OVERVIEW);
        }

        $customerResponseTransfer = $this
            ->getFactory()
            ->getAccessTokenAuthenticationHandler()
            ->getCustomerByToken($token);

        if (!$customerResponseTransfer->getIsSuccess()) {
            $this->addTranslatedErrorMessage(static::GLOSSARY_KEY_INVALID_ACCESS_TOKEN);

            throw new AccessDeniedHttpException();
        }

        $this->authenticateCustomer($customerResponseTransfer->getCustomerTransfer());

        return $this->redirectResponseInternal(static::ROUTE_CUSTOMER_OVERVIEW);
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerTransfer $customerTransfer
     *
     * @return void
     */
    protected function authenticateCustomer(CustomerTransfer $customerTransfer): void
    {
        $token = $this->getFactory()->createUsernamePasswordToken($customerTransfer);
        $this->getSecurityContext()->setToken($token);

        $this->getFactory()
            ->getCustomerClient()
            ->setCustomer($customerTransfer);
    }

    /**
     * @return \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected function getSecurityContext(): TokenStorageInterface
    {
        $application = $this->getFactory()->getApplication();

        return $application['security.token_storage'];
    }
}