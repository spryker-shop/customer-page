<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CustomerPage\Plugin\Security;

use Codeception\Stub;
use Codeception\Test\Unit;
use ReflectionClass;
use Spryker\Client\Storage\StorageDependencyProvider;
use Spryker\Client\StorageRedis\Plugin\StorageRedisPlugin;
use Spryker\Yves\Messenger\FlashMessenger\FlashMessengerInterface;
use Spryker\Yves\Security\Configurator\SecurityConfigurator;
use SprykerShop\Yves\CustomerPage\CustomerPageDependencyProvider;
use SprykerShop\Yves\CustomerPage\Plugin\Security\OauthCustomerSecurityPlugin;
use SprykerShop\Yves\CustomerPage\Plugin\Security\YvesCustomerPageSecurityPlugin;
use SprykerShopTest\Yves\CustomerPage\CustomerPageTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerShop
 * @group Yves
 * @group CustomerPage
 * @group Plugin
 * @group Security
 * @group OauthCustomerSecurityPluginTest
 * Add your own group annotations below this line
 */
class OauthCustomerSecurityPluginTest extends Unit
{
    /**
     * @uses \SprykerShop\Yves\CustomerPage\Oauth\Expander\OauthSecurityBuilderExpander::SECURITY_OAUTH_CUSTOMER_TOKEN_AUTHENTICATOR
     *
     * @var string
     */
    protected const string SECURITY_OAUTH_CUSTOMER_TOKEN_AUTHENTICATOR = 'security.secured.oauth_customer.authenticator';

    protected CustomerPageTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->tester->isSymfonyVersion5() === true) {
            $this->markTestSkipped('Compatible only with `symfony/security-core` package version >= 6. Will be enabled by default once Symfony 5 support is discontinued.');
        }

        $container = $this->tester->getContainer();
        $container->set('flash_messenger', function () {
            return Stub::makeEmpty(FlashMessengerInterface::class);
        });
        $this->tester->setDependency(StorageDependencyProvider::PLUGIN_STORAGE, new StorageRedisPlugin());
        $this->tester->setDependency(CustomerPageDependencyProvider::PLUGIN_APPLICATION, $container);

        $reflection = new ReflectionClass(SecurityConfigurator::class);
        $property = $reflection->getProperty('securityConfiguration');
        $property->setAccessible(true);
        $property->setValue(null);
    }

    public function testExtendRegistersOauthAuthenticatorWhenCustomerFirewallExists(): void
    {
        // Arrange
        $container = $this->tester->getContainer();
        $container->set(CustomerPageDependencyProvider::SERVICE_LOCALE, 'en_US');

        $basePlugin = new YvesCustomerPageSecurityPlugin();
        $basePlugin->setFactory($this->tester->getFactory());
        $this->tester->addSecurityPlugin($basePlugin);

        $oauthPlugin = new OauthCustomerSecurityPlugin();
        $oauthPlugin->setFactory($this->tester->getFactory());
        $this->tester->addSecurityPlugin($oauthPlugin);

        $this->tester->mockSecurityDependencies();

        // Act
        $this->tester->enableSecurityApplicationPlugin();
        $container->get('security.access_map');

        // Assert
        $this->assertTrue(
            $container->has(static::SECURITY_OAUTH_CUSTOMER_TOKEN_AUTHENTICATOR),
            'Expected the OAuth customer token authenticator to be registered after extend.',
        );
    }

    public function testExtendIsNoOpWhenCustomerFirewallDoesNotExist(): void
    {
        // Arrange
        $container = $this->tester->getContainer();
        $container->set(CustomerPageDependencyProvider::SERVICE_LOCALE, 'en_US');

        $oauthPlugin = new OauthCustomerSecurityPlugin();
        $oauthPlugin->setFactory($this->tester->getFactory());
        $this->tester->addSecurityPlugin($oauthPlugin);

        $this->tester->mockSecurityDependencies();

        // Act
        $this->tester->enableSecurityApplicationPlugin();
        $container->get('security.access_map');

        // Assert
        $this->assertFalse(
            $container->has(static::SECURITY_OAUTH_CUSTOMER_TOKEN_AUTHENTICATOR),
            'Expected the OAuth customer authenticator to be absent when customer firewall does not exist.',
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $reflection = new ReflectionClass(SecurityConfigurator::class);
        $property = $reflection->getProperty('securityConfiguration');
        $property->setAccessible(true);
        $property->setValue(null);
    }
}
