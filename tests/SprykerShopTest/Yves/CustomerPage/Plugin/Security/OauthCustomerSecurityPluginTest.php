<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CustomerPage\Plugin\Security;

use Codeception\Stub;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\MultiFactorAuthValidationResponseTransfer;
use ReflectionClass;
use Spryker\Client\Storage\StorageDependencyProvider;
use Spryker\Client\StorageRedis\Plugin\StorageRedisPlugin;
use Spryker\Yves\Messenger\FlashMessenger\FlashMessengerInterface;
use Spryker\Yves\Router\Router\ChainRouter;
use Spryker\Yves\Security\Configurator\SecurityConfigurator;
use SprykerShop\Yves\CustomerPage\CustomerPageDependencyProvider;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToCustomerClientInterface;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToSessionClientInterface;
use SprykerShop\Yves\CustomerPage\Oauth\Security\Handler\OauthCustomerAuthenticationSuccessHandler;
use SprykerShop\Yves\CustomerPage\Plugin\Security\OauthCustomerSecurityPlugin;
use SprykerShop\Yves\CustomerPage\Plugin\Security\YvesCustomerPageSecurityPlugin;
use SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\AuthenticationHandlerPluginInterface;
use SprykerShopTest\Yves\CustomerPage\CustomerPageTester;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

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

    /**
     * @uses \SprykerShop\Yves\CustomerPage\Oauth\Security\Handler\OauthCustomerAuthenticationSuccessHandler::ROUTE_CUSTOMER_OAUTH_MFA
     */
    protected const string ROUTE_NAME_CUSTOMER_OAUTH_MFA = 'multiFactorAuth/customerLogin';

    protected const string ROUTE_PATH_CUSTOMER_OAUTH_MFA = '/multi-factor-auth/customer/login';

    /**
     * @uses \SprykerShop\Yves\CustomerPage\Oauth\Security\Handler\OauthCustomerAuthenticationSuccessHandler::MULTI_FACTOR_AUTH_LOGIN_CUSTOMER_EMAIL_SESSION_KEY
     */
    protected const string MULTI_FACTOR_AUTH_LOGIN_CUSTOMER_EMAIL_SESSION_KEY = '_multi_factor_auth_login_customer_email';

    /**
     * @uses \SprykerShop\Yves\CustomerPage\Oauth\Authenticator\OauthCustomerTokenAuthenticator::ACCESS_MODE_PRE_AUTH
     */
    protected const string ACCESS_MODE_PRE_AUTH = 'ACCESS_MODE_PRE_AUTH';

    protected const string SOME_CUSTOMER_EMAIL = 'test@example.com';

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

    public function testOauthCustomerSuccessHandlerRedirectsToMfaPageOnPreAuth(): void
    {
        // Arrange
        $customerTransfer = (new CustomerTransfer())->setEmail(static::SOME_CUSTOMER_EMAIL);

        $request = Request::create('/');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $request->attributes->set('_oauth_customer', $customerTransfer);

        $tokenMock = $this->getMockBuilder(TokenInterface::class)->getMock();
        $tokenMock->method('getRoleNames')->willReturn([static::ACCESS_MODE_PRE_AUTH]);

        $sessionClientMock = $this->getMockBuilder(CustomerPageToSessionClientInterface::class)->getMock();
        $sessionClientMock->expects($this->once())
            ->method('set')
            ->with(static::MULTI_FACTOR_AUTH_LOGIN_CUSTOMER_EMAIL_SESSION_KEY, static::SOME_CUSTOMER_EMAIL);

        $routerMock = $this->getMockBuilder(ChainRouter::class)->disableOriginalConstructor()->getMock();
        $routerMock->method('generate')
            ->with(static::ROUTE_NAME_CUSTOMER_OAUTH_MFA)
            ->willReturn(static::ROUTE_PATH_CUSTOMER_OAUTH_MFA);

        $customerClientMock = $this->getMockBuilder(CustomerPageToCustomerClientInterface::class)->getMock();

        $handler = new OauthCustomerAuthenticationSuccessHandler(
            $customerClientMock,
            $sessionClientMock,
            $routerMock,
        );

        // Act
        $response = $handler->onAuthenticationSuccess($request, $tokenMock);

        // Assert
        $this->assertSame(
            static::ROUTE_PATH_CUSTOMER_OAUTH_MFA,
            $response->headers->get('Location'),
            'Expected redirect to MFA page when token has ACCESS_MODE_PRE_AUTH role.',
        );
    }

    public function testOauthCustomerMfaBadgeSetsIsRequiredWhenHandlerPluginRequiresMfa(): void
    {
        // Arrange
        $this->tester->setDependency(
            CustomerPageDependencyProvider::PLUGINS_CUSTOMER_AUTHENTICATION_HANDLER,
            [$this->createMfaRequiredAuthenticationHandlerPluginMock()],
        );

        $customerTransfer = (new CustomerTransfer())->setEmail(static::SOME_CUSTOMER_EMAIL);

        // Act — create badge with the handler plugin configured above (dep is set before factory call)
        $badge = $this->tester->getFactory()->createMultiFactorAuthBadge();
        $badge->enable($customerTransfer);

        // Assert
        $this->assertTrue(
            $badge->getIsRequired(),
            'Expected MFA badge to report isRequired=true when MFA handler plugin returns isRequired=true.',
        );
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\AuthenticationHandlerPluginInterface
     */
    protected function createMfaRequiredAuthenticationHandlerPluginMock(): AuthenticationHandlerPluginInterface
    {
        $pluginMock = $this->getMockBuilder(AuthenticationHandlerPluginInterface::class)->getMock();

        $pluginMock->method('isApplicable')->willReturn(true);

        $pluginMock->method('validateCustomerMultiFactorStatus')->willReturn(
            (new MultiFactorAuthValidationResponseTransfer())
                ->setIsRequired(true)
                ->setStatus(null),
        );

        return $pluginMock;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $reflection = new ReflectionClass(SecurityConfigurator::class);
        $property = $reflection->getProperty('securityConfiguration');
        $property->setValue(null);
    }
}
