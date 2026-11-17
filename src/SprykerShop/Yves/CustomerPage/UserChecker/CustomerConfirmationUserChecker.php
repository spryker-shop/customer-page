<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\UserChecker;

use SprykerShop\Yves\CustomerPage\CustomerPageConfig;
use SprykerShop\Yves\CustomerPage\Exception\NotConfirmedAccountException;
use SprykerShop\Yves\CustomerPage\Security\CustomerUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CustomerConfirmationUserChecker extends InMemoryUserChecker
{
    /**
     * @var array<\SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\PreAuthUserCheckPluginInterface>
     */
    protected $preAuthUserCheckPlugins;

    protected CustomerPageConfig $customerPageConfig;

    /**
     * @param array<\SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\PreAuthUserCheckPluginInterface> $preAuthUserCheckPlugins
     */
    public function __construct(array $preAuthUserCheckPlugins, CustomerPageConfig $customerPageConfig)
    {
        $this->preAuthUserCheckPlugins = $preAuthUserCheckPlugins;
        $this->customerPageConfig = $customerPageConfig;
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @throws \SprykerShop\Yves\CustomerPage\Exception\NotConfirmedAccountException
     *
     * @return void
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof CustomerUserInterface) {
            return;
        }

        parent::checkPreAuth($user);

        $customerTransfer = $user->getCustomerTransfer();
        if ($this->customerPageConfig->isDoubleOptInEnabled() && $customerTransfer->getRegistered() === null) {
            $exception = new NotConfirmedAccountException();
            $exception->setUser($user);

            throw $exception;
        }

        $this->executePreAuthUserCheckPlugins($user);
    }

    protected function executePreAuthUserCheckPlugins(CustomerUserInterface $user): void
    {
        foreach ($this->preAuthUserCheckPlugins as $preAuthCheckerPlugin) {
            $preAuthCheckerPlugin->checkPreAuth($user->getCustomerTransfer(), $user);
        }
    }
}
