<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Oauth\Expander;

use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\SecurityExtension\Configuration\SecurityBuilderInterface;
use SprykerShop\Yves\CustomerPage\CustomerPageConfig;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;

class OauthSecurityBuilderExpander implements OauthSecurityBuilderExpanderInterface
{
    /**
     * @uses \SprykerShop\Shared\CustomerPage\CustomerPageConfig::SECURITY_FIREWALL_NAME
     */
    protected const string SECURITY_CUSTOMER_FIREWALL_NAME = 'secured';

    protected const string SECURITY_OAUTH_CUSTOMER_TOKEN_AUTHENTICATOR = 'security.secured.oauth_customer.authenticator';

    /**
     * @uses \SprykerShop\Yves\CustomerPage\Expander\SecurityBuilderExpander::SECURITY_CUSTOMER_LOGIN_FORM_AUTHENTICATOR
     */
    protected const string SECURITY_CUSTOMER_LOGIN_FORM_AUTHENTICATOR = 'security.secured.login_form.authenticator';

    protected const string FORM = 'form';

    protected const string AUTHENTICATORS = 'authenticators';

    protected const string ACCESS_MODE_PUBLIC = 'PUBLIC_ACCESS';

    public function __construct(
        protected CustomerPageConfig $customerPageConfig,
        protected AuthenticatorInterface $authenticator,
    ) {
    }

    public function extend(SecurityBuilderInterface $securityBuilder, ContainerInterface $container): SecurityBuilderInterface
    {
        if ($this->findFirewall(static::SECURITY_CUSTOMER_FIREWALL_NAME, $securityBuilder) === null) {
            return $securityBuilder;
        }

        $securityBuilder = $this->expandCustomerFirewall($securityBuilder);
        $securityBuilder = $this->addAccessRules($securityBuilder);
        $this->addAuthenticator($container);

        return $securityBuilder;
    }

    protected function expandCustomerFirewall(SecurityBuilderInterface $securityBuilder): SecurityBuilderInterface
    {
        $customerFirewallConfiguration = $this->findFirewall(static::SECURITY_CUSTOMER_FIREWALL_NAME, $securityBuilder);

        if ($customerFirewallConfiguration === null) {
            return $securityBuilder;
        }

        $existingFormConfig = $customerFirewallConfiguration[static::FORM] ?? [];
        $existingAuthenticators = $existingFormConfig[static::AUTHENTICATORS] ?? [static::SECURITY_CUSTOMER_LOGIN_FORM_AUTHENTICATOR];

        // Deep merge: preserve existing form config (login_path, check_path, etc.) and prepend our authenticator
        $mergedFormConfig = array_replace(
            $existingFormConfig,
            [static::AUTHENTICATORS => array_merge([static::SECURITY_OAUTH_CUSTOMER_TOKEN_AUTHENTICATOR], $existingAuthenticators)],
        );

        return $securityBuilder->addFirewall(
            static::SECURITY_CUSTOMER_FIREWALL_NAME,
            array_replace($customerFirewallConfiguration, [static::FORM => $mergedFormConfig]),
        );
    }

    protected function addAccessRules(SecurityBuilderInterface $securityBuilder): SecurityBuilderInterface
    {
        // Allow public access to OAuth callback and start routes
        return $securityBuilder->addAccessRules([
            [
                $this->customerPageConfig->getOauthCallbackRoutePath(),
                static::ACCESS_MODE_PUBLIC,
            ],
        ]);
    }

    protected function addAuthenticator(ContainerInterface $container): void
    {
        $container->set(static::SECURITY_OAUTH_CUSTOMER_TOKEN_AUTHENTICATOR, function () {
            return $this->authenticator;
        });
    }

    /**
     * @return array<mixed>|null
     */
    protected function findFirewall(string $firewallName, SecurityBuilderInterface $securityBuilder): ?array
    {
        $firewalls = (clone $securityBuilder)->getConfiguration()->getFirewalls();

        return $firewalls[$firewallName] ?? null;
    }
}
