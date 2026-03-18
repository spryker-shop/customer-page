<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage;

use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\Service\Http\HttpServiceInterface;
use Spryker\Shared\Kernel\StrategyResolver;
use Spryker\Shared\Kernel\StrategyResolverInterface;
use Spryker\Shared\Twig\TwigFunctionProvider;
use Spryker\Yves\Kernel\AbstractFactory;
use Spryker\Yves\Router\Router\ChainRouter;
use SprykerShop\Shared\CustomerPage\CustomerPageConfig as SharedCustomerPageConfig;
use SprykerShop\Yves\CustomerPage\Authenticator\CustomerAuthenticator;
use SprykerShop\Yves\CustomerPage\Authenticator\CustomerAuthenticatorInterface;
use SprykerShop\Yves\CustomerPage\Authenticator\CustomerLoginFormAuthenticator;
use SprykerShop\Yves\CustomerPage\Badge\MultiFactorAuthBadge;
use SprykerShop\Yves\CustomerPage\Builder\CustomerSecurityOptionsBuilder;
use SprykerShop\Yves\CustomerPage\Builder\CustomerSecurityOptionsBuilderInterface;
use SprykerShop\Yves\CustomerPage\Checker\LastVisitedPageUrlChecker;
use SprykerShop\Yves\CustomerPage\Checker\LastVisitedPageUrlCheckerInterface;
use SprykerShop\Yves\CustomerPage\CustomerAddress\AddressChoicesResolver;
use SprykerShop\Yves\CustomerPage\CustomerAddress\AddressChoicesResolverInterface;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToCustomerClientInterface;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToProductBundleClientInterface;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToQuoteClientInteface;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToSalesClientInterface;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToSecurityBlockerClientInterface;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToSessionClientInterface;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToShipmentClientInterface;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToStoreClientInterface;
use SprykerShop\Yves\CustomerPage\Dependency\Service\CustomerPageToCustomerServiceInterface;
use SprykerShop\Yves\CustomerPage\Dependency\Service\CustomerPageToShipmentServiceInterface;
use SprykerShop\Yves\CustomerPage\EventSubscriber\LastVisitedPageEventSubscriber;
use SprykerShop\Yves\CustomerPage\Expander\CustomerAddressExpander;
use SprykerShop\Yves\CustomerPage\Expander\CustomerAddressExpanderInterface;
use SprykerShop\Yves\CustomerPage\Expander\CustomerRememberMeExpander;
use SprykerShop\Yves\CustomerPage\Expander\CustomerRememberMeExpanderInterface;
use SprykerShop\Yves\CustomerPage\Expander\SecurityBuilderExpander;
use SprykerShop\Yves\CustomerPage\Expander\SecurityBuilderExpanderInterface;
use SprykerShop\Yves\CustomerPage\Expander\ShipmentExpander;
use SprykerShop\Yves\CustomerPage\Expander\ShipmentExpanderInterface;
use SprykerShop\Yves\CustomerPage\Expander\ShipmentGroupExpander;
use SprykerShop\Yves\CustomerPage\Expander\ShipmentGroupExpanderInterface;
use SprykerShop\Yves\CustomerPage\Form\Cloner\FormCloner;
use SprykerShop\Yves\CustomerPage\Form\DataProvider\CheckoutAddressFormDataProvider;
use SprykerShop\Yves\CustomerPage\Form\FormFactory;
use SprykerShop\Yves\CustomerPage\Form\Transformer\AddressSelectTransformer;
use SprykerShop\Yves\CustomerPage\Formatter\LoginCheckUrlFormatter;
use SprykerShop\Yves\CustomerPage\Formatter\LoginCheckUrlFormatterInterface;
use SprykerShop\Yves\CustomerPage\Handler\OrderSearchFormHandler;
use SprykerShop\Yves\CustomerPage\Handler\OrderSearchFormHandlerInterface;
use SprykerShop\Yves\CustomerPage\Logger\AuditLogger;
use SprykerShop\Yves\CustomerPage\Logger\AuditLoggerInterface;
use SprykerShop\Yves\CustomerPage\Mapper\CustomerMapper;
use SprykerShop\Yves\CustomerPage\Mapper\CustomerMapperInterface;
use SprykerShop\Yves\CustomerPage\Mapper\ItemStateMapper;
use SprykerShop\Yves\CustomerPage\Mapper\ItemStateMapperInterface;
use SprykerShop\Yves\CustomerPage\Plugin\Provider\AccessDeniedHandler;
use SprykerShop\Yves\CustomerPage\Plugin\Provider\CustomerAuthenticationFailureHandler;
use SprykerShop\Yves\CustomerPage\Plugin\Provider\CustomerAuthenticationSuccessHandler;
use SprykerShop\Yves\CustomerPage\Plugin\Provider\CustomerUserProvider;
use SprykerShop\Yves\CustomerPage\Plugin\Security\CustomerPageSecurityPlugin;
use SprykerShop\Yves\CustomerPage\Plugin\Subscriber\InteractiveLoginEventSubscriber;
use SprykerShop\Yves\CustomerPage\Reader\OrderReader;
use SprykerShop\Yves\CustomerPage\Reader\OrderReaderInterface;
use SprykerShop\Yves\CustomerPage\Resolver\LastVisitedPageRedirectResolver;
use SprykerShop\Yves\CustomerPage\Resolver\LastVisitedPageRedirectResolverInterface;
use SprykerShop\Yves\CustomerPage\Security\Customer;
use SprykerShop\Yves\CustomerPage\Storage\LastVisitedPageCookieStorage;
use SprykerShop\Yves\CustomerPage\Storage\LastVisitedPageStorageInterface;
use SprykerShop\Yves\CustomerPage\Twig\GetUsernameTwigFunctionProvider;
use SprykerShop\Yves\CustomerPage\Twig\IsLoggedTwigFunctionProvider;
use SprykerShop\Yves\CustomerPage\UserChecker\CustomerConfirmationUserChecker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Security\Http\EventListener\UserCheckerListener;
use Twig\TwigFunction;

/**
 * @method \SprykerShop\Yves\CustomerPage\CustomerPageConfig getConfig()
 */
class CustomerPageFactory extends AbstractFactory
{
    public function createInteractiveLoginEventSubscriber(): EventSubscriberInterface
    {
        return new InteractiveLoginEventSubscriber();
    }

    /**
     * @return \SprykerShop\Yves\CustomerPage\Form\FormFactory
     */
    public function createCustomerFormFactory()
    {
        return new FormFactory();
    }

    /**
     * @return \SprykerShop\Yves\CustomerPage\Plugin\Provider\CustomerAuthenticationSuccessHandler
     */
    public function createCustomerAuthenticationSuccessHandler()
    {
        return new CustomerAuthenticationSuccessHandler();
    }

    /**
     * @param string|null $targetUrl
     *
     * @return \SprykerShop\Yves\CustomerPage\Plugin\Provider\CustomerAuthenticationFailureHandler
     */
    public function createCustomerAuthenticationFailureHandler(?string $targetUrl = null)
    {
        return new CustomerAuthenticationFailureHandler($this->getFlashMessenger(), $targetUrl);
    }

    /**
     * @return \Symfony\Component\Security\Core\User\UserProviderInterface
     */
    public function createCustomerUserProvider()
    {
        return new CustomerUserProvider();
    }

    public function createAccessDeniedHandler(string $targetUrl): AccessDeniedHandlerInterface
    {
        return new AccessDeniedHandler($targetUrl);
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerTransfer $customerTransfer
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function createSecurityUser(CustomerTransfer $customerTransfer)
    {
        return new Customer(
            $customerTransfer,
            $customerTransfer->getEmail(),
            $customerTransfer->getPassword(),
            [CustomerPageSecurityPlugin::ROLE_NAME_USER],
        );
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerTransfer $customerTransfer
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    public function createUsernamePasswordToken(CustomerTransfer $customerTransfer)
    {
        $user = $this->createSecurityUser($customerTransfer);

        if (class_exists(AuthenticationProviderManager::class) === true) {
            return new UsernamePasswordToken(
                $user,
                $user->getPassword(),
                SharedCustomerPageConfig::SECURITY_FIREWALL_NAME,
                [CustomerPageSecurityPlugin::ROLE_NAME_USER],
            );
        }

        return new UsernamePasswordToken(
            $user,
            SharedCustomerPageConfig::SECURITY_FIREWALL_NAME,
            [CustomerPageSecurityPlugin::ROLE_NAME_USER],
        );
    }

    /**
     * @param string $targetUrl
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createRedirectResponse($targetUrl)
    {
        return new RedirectResponse($targetUrl);
    }

    public function createCustomerAuthenticator(): CustomerAuthenticatorInterface
    {
        return new CustomerAuthenticator(
            $this->getCustomerClient(),
            $this->getTokenStorage(),
        );
    }

    public function createCheckoutAddressFormDataProvider(): CheckoutAddressFormDataProvider
    {
        return new CheckoutAddressFormDataProvider(
            $this->getCustomerClient(),
            $this->getStoreClient(),
            $this->getCustomerService(),
            $this->getShipmentClient(),
            $this->getProductBundleClient(),
            $this->getShipmentService(),
            $this->createAddressChoicesResolver(),
            $this->getCheckoutAddressCollectionFormExpanderPlugins(),
        );
    }

    public function createAddressSelectTransformer(): DataTransformerInterface
    {
        return new AddressSelectTransformer();
    }

    public function getTokenStorage(): TokenStorageInterface
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::SERVICE_SECURITY_TOKEN_STORAGE);
    }

    public function getRouter(): ChainRouter
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::SERVICE_ROUTER);
    }

    public function getRequestStack(): RequestStack
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::SERVICE_REQUEST_STACK);
    }

    public function createOrderReader(): OrderReaderInterface
    {
        return new OrderReader(
            $this->getSalesClient(),
            $this->getCustomerClient(),
            $this->getConfig(),
        );
    }

    public function getSalesClient(): CustomerPageToSalesClientInterface
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::CLIENT_SALES);
    }

    /**
     * @return \SprykerShop\Yves\CustomerPage\Plugin\AuthenticationHandler
     */
    public function getAuthenticationHandler()
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGIN_AUTHENTICATION_HANDLER);
    }

    public function getProductBundleClient(): CustomerPageToProductBundleClientInterface
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::CLIENT_PRODUCT_BUNDLE);
    }

    /**
     * @return \Spryker\Shared\Kernel\Communication\Application
     */
    public function getApplication()
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGIN_APPLICATION);
    }

    /**
     * @return \Spryker\Yves\Messenger\FlashMessenger\FlashMessengerInterface
     */
    public function getFlashMessenger()
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::SERVICE_FLASH_MESSENGER);
    }

    /**
     * @return array<\SprykerShop\Yves\CustomerPage\Plugin\CheckoutAuthenticationHandlerPluginInterface>
     */
    public function getCustomerAuthenticationHandlerPlugins()
    {
        return [
            $this->getLoginCheckoutAuthenticationHandlerPlugin(),
            $this->getGuestCheckoutAuthenticationHandlerPlugin(),
            $this->getRegistrationAuthenticationHandlerPlugin(),
        ];
    }

    /**
     * @return \SprykerShop\Yves\CustomerPage\Plugin\LoginCheckoutAuthenticationHandlerPlugin
     */
    public function getLoginCheckoutAuthenticationHandlerPlugin()
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGIN_LOGIN_AUTHENTICATION_HANDLER);
    }

    /**
     * @return \SprykerShop\Yves\CustomerPage\Plugin\GuestCheckoutAuthenticationHandlerPlugin
     */
    public function getGuestCheckoutAuthenticationHandlerPlugin()
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGIN_GUEST_AUTHENTICATION_HANDLER);
    }

    /**
     * @return \SprykerShop\Yves\CustomerPage\Plugin\RegistrationCheckoutAuthenticationHandlerPlugin
     */
    public function getRegistrationAuthenticationHandlerPlugin()
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGIN_REGISTRATION_AUTHENTICATION_HANDLER);
    }

    /**
     * @deprecated Use {@link \SprykerShop\Yves\CustomerPage\CustomerPageFactory::getFlashMessenger()} instead.
     *
     * @return \Spryker\Yves\Messenger\FlashMessenger\FlashMessengerInterface
     */
    public function getMessenger()
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::SERVICE_FLASH_MESSENGER);
    }

    public function getCustomerClient(): CustomerPageToCustomerClientInterface
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::CLIENT_CUSTOMER);
    }

    public function getQuoteClient(): CustomerPageToQuoteClientInteface
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::CLIENT_QUOTE);
    }

    /**
     * @return array<string>
     */
    public function getCustomerOverviewWidgetPlugins(): array
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGIN_CUSTOMER_OVERVIEW_WIDGETS);
    }

    /**
     * @return array<string>
     */
    public function getCustomerOrderListWidgetPlugins(): array
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGIN_CUSTOMER_ORDER_LIST_WIDGETS);
    }

    /**
     * @return array<string>
     */
    public function getCustomerOrderViewWidgetPlugins(): array
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGIN_CUSTOMER_ORDER_VIEW_WIDGETS);
    }

    /**
     * @return \SprykerShop\Yves\CustomerPage\Dependency\Service\CustomerPageToUtilValidateServiceInterface
     */
    public function getUtilValidateService()
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::SERVICE_UTIL_VALIDATE);
    }

    /**
     * @return array<string>
     */
    public function getCustomerMenuItemWidgetPlugins(): array
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGIN_CUSTOMER_MENU_ITEM_WIDGETS);
    }

    /**
     * @return array<\SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\PreRegistrationCustomerTransferExpanderPluginInterface>
     */
    public function getPreRegistrationCustomerTransferExpanderPlugins(): array
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGIN_PRE_REGISTRATION_CUSTOMER_TRANSFER_EXPANDER);
    }

    /**
     * @return array<\SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\CustomerRedirectStrategyPluginInterface>
     */
    public function getAfterLoginCustomerRedirectPlugins(): array
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGIN_AFTER_LOGIN_CUSTOMER_REDIRECT);
    }

    /**
     * @return array<\SprykerShop\Yves\AgentPage\Plugin\FixAgentTokenAfterCustomerAuthenticationSuccessPlugin>
     */
    public function getAfterCustomerAuthenticationSuccessPlugins(): array
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGIN_AFTER_CUSTOMER_AUTHENTICATION_SUCCESS);
    }

    public function createGetUsernameTwigFunctionProvider(): TwigFunctionProvider
    {
        return new GetUsernameTwigFunctionProvider($this->getCustomerClient());
    }

    public function createGetUsernameTwigFunction(): TwigFunction
    {
        $functionProvider = $this->createGetUsernameTwigFunctionProvider();

        return new TwigFunction(
            $functionProvider->getFunctionName(),
            $functionProvider->getFunction(),
            $functionProvider->getOptions(),
        );
    }

    public function createIsLoggedTwigFunctionProvider(): TwigFunctionProvider
    {
        return new IsLoggedTwigFunctionProvider($this->getCustomerClient());
    }

    public function createIsLoggedTwigFunction(): TwigFunction
    {
        $functionProvider = $this->createIsLoggedTwigFunctionProvider();

        return new TwigFunction(
            $functionProvider->getFunctionName(),
            $functionProvider->getFunction(),
            $functionProvider->getOptions(),
        );
    }

    public function getShipmentService(): CustomerPageToShipmentServiceInterface
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::SERVICE_SHIPMENT);
    }

    public function createCustomerMapper(): CustomerMapperInterface
    {
        return new CustomerMapper();
    }

    public function createItemStateMapper(): ItemStateMapperInterface
    {
        return new ItemStateMapper();
    }

    public function createCustomerExpander(): CustomerAddressExpanderInterface
    {
        return new CustomerAddressExpander($this->createCustomerMapper());
    }

    public function createShipmentGroupExpander(): ShipmentGroupExpanderInterface
    {
        return new ShipmentGroupExpander();
    }

    public function createShipmentExpander(): ShipmentExpanderInterface
    {
        return new ShipmentExpander();
    }

    public function createAddressChoicesResolver(): AddressChoicesResolverInterface
    {
        return new AddressChoicesResolver();
    }

    public function getShipmentClient(): CustomerPageToShipmentClientInterface
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::CLIENT_SHIPMENT);
    }

    public function getLocale(): string
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::SERVICE_LOCALE);
    }

    public function getCustomerService(): CustomerPageToCustomerServiceInterface
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::SERVICE_CUSTOMER);
    }

    /**
     * @return array<\SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\CheckoutAddressStepPreGroupItemsByShipmentPluginInterface>
     */
    public function getCheckoutAddressStepPreGroupItemsByShipmentPlugins(): array
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGINS_CHECKOUT_ADDRESS_STEP_PRE_GROUP_ITEMS_BY_SHIPMENT);
    }

    public function createOrderSearchFormHandler(): OrderSearchFormHandlerInterface
    {
        return new OrderSearchFormHandler(
            $this->getCustomerClient(),
            $this->getOrderSearchFormHandlerPlugins(),
        );
    }

    /**
     * @return array<\SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\OrderSearchFormHandlerPluginInterface>
     */
    public function getOrderSearchFormHandlerPlugins(): array
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGINS_ORDER_SEARCH_FORM_HANDLER);
    }

    public function createFormCloner(): FormCloner
    {
        return new FormCloner();
    }

    public function createCustomerConfirmationUserChecker(): UserCheckerInterface
    {
        return new CustomerConfirmationUserChecker(
            $this->getPreAuthUserCheckPlugins(),
        );
    }

    public function createUserCheckerListener(): EventSubscriberInterface
    {
        return new UserCheckerListener(
            $this->createCustomerConfirmationUserChecker(),
        );
    }

    public function createLoginCheckUrlFormatter(): LoginCheckUrlFormatterInterface
    {
        return new LoginCheckUrlFormatter(
            $this->getConfig(),
            $this->getLocale(),
            $this->getStoreClient(),
        );
    }

    /**
     * @return array<\SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\PreAuthUserCheckPluginInterface>
     */
    public function getPreAuthUserCheckPlugins(): array
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGINS_PRE_AUTH_USER_CHECK);
    }

    public function getStoreClient(): CustomerPageToStoreClientInterface
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::CLIENT_STORE);
    }

    /**
     * @return list<\SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\CheckoutAddressCollectionFormExpanderPluginInterface>
     */
    public function getCheckoutAddressCollectionFormExpanderPlugins(): array
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGINS_CHECKOUT_ADDRESS_COLLECTION_FORM_EXPANDER);
    }

    /**
     * @return list<\SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\CheckoutMultiShippingAddressesFormExpanderPluginInterface>
     */
    public function getCheckoutMultiShippingAddressesFormExpanderPlugins(): array
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGINS_CHECKOUT_MULTI_SHIPPING_ADDRESSES_FORM_EXPANDER);
    }

    public function getSecurityBlockerClient(): CustomerPageToSecurityBlockerClientInterface
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::CLIENT_SECURITY_BLOCKER);
    }

    public function createCustomerSecurityOptionsBuilder(): CustomerSecurityOptionsBuilderInterface
    {
        return new CustomerSecurityOptionsBuilder(
            $this->getConfig(),
            $this->createCustomerUserProvider(),
            $this->createLoginCheckUrlFormatter(),
        );
    }

    public function createCustomerLoginAuthenticator(): AuthenticatorInterface
    {
        return new CustomerLoginFormAuthenticator(
            $this->createCustomerUserProvider(),
            $this->createRememberMeBadge(),
            $this->createCustomerAuthenticationSuccessHandler(),
            $this->createCustomerAuthenticationFailureHandler(),
            $this->getRouter(),
            $this->createMultiFactorAuthBadge(),
        );
    }

    public function createRememberMeBadge(): RememberMeBadge
    {
        return new RememberMeBadge();
    }

    public function createMultiFactorAuthBadge(): MultiFactorAuthBadge
    {
        return new MultiFactorAuthBadge($this->getCustomerMultiFactorAuthenticationHandlerPlugins());
    }

    public function createSecurityBuilderExpander(): SecurityBuilderExpanderInterface
    {
        if (class_exists(AuthenticationProviderManager::class) === true) {
            return new CustomerPageSecurityPlugin();
        }

        return new SecurityBuilderExpander(
            $this->createCustomerSecurityOptionsBuilder(),
            $this->getCustomerClient(),
            $this->getConfig(),
            $this->createInteractiveLoginEventSubscriber(),
            $this->createCustomerLoginAuthenticator(),
            $this->createUserCheckerListener(),
        );
    }

    public function createCustomerRememberMeExpander(): CustomerRememberMeExpanderInterface
    {
        return new CustomerRememberMeExpander(
            $this->createCustomerUserProvider(),
            $this->createCustomerSecurityOptionsBuilder(),
        );
    }

    public function createAuditLogger(): AuditLoggerInterface
    {
        return new AuditLogger();
    }

    /**
     * @return array<\SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\AuthenticationHandlerPluginInterface>
     */
    public function getCustomerMultiFactorAuthenticationHandlerPlugins(): array
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::PLUGINS_CUSTOMER_AUTHENTICATION_HANDLER);
    }

    public function getSessionClient(): CustomerPageToSessionClientInterface
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::CLIENT_SESSION);
    }

    public function createLastVisitedPageEventSubscriber(): EventSubscriberInterface
    {
        return new LastVisitedPageEventSubscriber(
            $this->createLastVisitedPageUrlChecker(),
            $this->createLastVisitedPageStorageResolver()->get($this->getConfig()->getLastVisitedPageStorageType()),
        );
    }

    public function createLastVisitedPageUrlChecker(): LastVisitedPageUrlCheckerInterface
    {
        return new LastVisitedPageUrlChecker(
            $this->getCustomerClient(),
            $this->getHttpService(),
        );
    }

    /**
     * @return \Spryker\Shared\Kernel\StrategyResolverInterface<\SprykerShop\Yves\CustomerPage\Storage\LastVisitedPageStorageInterface>
     */
    public function createLastVisitedPageStorageResolver(): StrategyResolverInterface
    {
        return new StrategyResolver(
            [$this->getConfig()->getLastVisitedPageStorageType() => $this->createLastVisitedPageCookieStorage()],
            $this->getConfig()->getLastVisitedPageStorageType(),
        );
    }

    public function createLastVisitedPageCookieStorage(): LastVisitedPageStorageInterface
    {
        return new LastVisitedPageCookieStorage($this->getConfig());
    }

    public function createLastVisitedPageRedirectResolver(): LastVisitedPageRedirectResolverInterface
    {
        return new LastVisitedPageRedirectResolver(
            $this->getRequestStack(),
            $this->createLastVisitedPageStorageResolver()->get($this->getConfig()->getLastVisitedPageStorageType()),
            $this->getHttpService(),
        );
    }

    public function getHttpService(): HttpServiceInterface
    {
        return $this->getProvidedDependency(CustomerPageDependencyProvider::SERVICE_HTTP);
    }
}
