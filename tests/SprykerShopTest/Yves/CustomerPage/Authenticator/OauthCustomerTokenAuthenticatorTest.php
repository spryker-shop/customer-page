<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CustomerPage\Authenticator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\MultiFactorAuthValidationResponseTransfer;
use Generated\Shared\Transfer\OauthCustomerResolveResponseTransfer;
use Generated\Shared\Transfer\ResourceOwnerResponseTransfer;
use Generated\Shared\Transfer\ResourceOwnerTransfer;
use SprykerShop\Yves\CustomerPage\Badge\MultiFactorAuthBadge;
use SprykerShop\Yves\CustomerPage\CustomerPageConfig;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToCustomerClientInterface;
use SprykerShop\Yves\CustomerPage\Oauth\Authenticator\OauthCustomerTokenAuthenticator;
use SprykerShop\Yves\CustomerPage\Oauth\Reader\ResourceOwnerReaderInterface;
use SprykerShop\Yves\CustomerPage\Security\Customer;
use SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\AuthenticationHandlerPluginInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * @group SprykerShopTest
 * @group Yves
 * @group CustomerPage
 * @group Authenticator
 * @group OauthCustomerTokenAuthenticatorTest
 */
class OauthCustomerTokenAuthenticatorTest extends Unit
{
    /**
     * @uses \SprykerShop\Yves\CustomerPage\Oauth\Authenticator\OauthCustomerTokenAuthenticator::ACCESS_MODE_PRE_AUTH
     */
    protected const string ACCESS_MODE_PRE_AUTH = 'ACCESS_MODE_PRE_AUTH';

    /**
     * @uses \SprykerShop\Yves\CustomerPage\Oauth\Authenticator\OauthCustomerTokenAuthenticator::ROLE_USER
     */
    protected const string ROLE_USER = 'ROLE_USER';

    /**
     * @uses \Spryker\Shared\MultiFactorAuth\MultiFactorAuthConstants::CODE_BLOCKED
     */
    protected const int CODE_BLOCKED = 1;

    protected const string SOME_CUSTOMER_EMAIL = 'test@example.com';

    protected const string SOME_CODE = 'SOME_OAUTH_CODE';

    protected const string SOME_STATE = 'SOME_OAUTH_STATE';

    protected const string FIREWALL_NAME = 'secured';

    public function testCreateTokenReturnsFullTokenWhenMultiFactorAuthIsNotRequired(): void
    {
        // Arrange
        $authenticator = $this->createAuthenticator($this->createMultiFactorAuthBadge(false));

        // Act
        $passport = $authenticator->authenticate($this->createOauthCallbackRequest());
        $token = $authenticator->createToken($passport, static::FIREWALL_NAME);

        // Assert
        $this->assertSame(
            [static::ROLE_USER],
            $token->getRoleNames(),
            'Expected a full (non pre-auth) token when Multi-Factor Authentication is not required.',
        );
    }

    public function testCreateTokenReturnsPreAuthTokenWhenMultiFactorAuthIsRequired(): void
    {
        // Arrange
        $authenticator = $this->createAuthenticator($this->createMultiFactorAuthBadge(true));

        // Act
        $passport = $authenticator->authenticate($this->createOauthCallbackRequest());
        $token = $authenticator->createToken($passport, static::FIREWALL_NAME);

        // Assert
        $this->assertSame(
            [static::ACCESS_MODE_PRE_AUTH],
            $token->getRoleNames(),
            'Expected the token to collapse to ACCESS_MODE_PRE_AUTH when Multi-Factor Authentication is required.',
        );
    }

    public function testCreateTokenReturnsPreAuthTokenWhenMultiFactorAuthCodeIsBlocked(): void
    {
        // Arrange
        $authenticator = $this->createAuthenticator(
            $this->createMultiFactorAuthBadge(false, static::CODE_BLOCKED),
        );

        // Act
        $passport = $authenticator->authenticate($this->createOauthCallbackRequest());
        $token = $authenticator->createToken($passport, static::FIREWALL_NAME);

        // Assert
        $this->assertSame(
            [static::ACCESS_MODE_PRE_AUTH],
            $token->getRoleNames(),
            'Expected the token to collapse to ACCESS_MODE_PRE_AUTH when the Multi-Factor Authentication code is blocked.',
        );
    }

    public function testCreateTokenReturnsFullTokenWhenPassportCarriesNoMultiFactorAuthBadge(): void
    {
        // Arrange
        $authenticator = $this->createAuthenticator($this->createMultiFactorAuthBadge(true));

        // Act
        $token = $authenticator->createToken($this->createPassportWithoutBadge(), static::FIREWALL_NAME);

        // Assert
        $this->assertSame(
            [static::ROLE_USER],
            $token->getRoleNames(),
            'Expected a full token instead of a fatal error when the passport carries no Multi-Factor Authentication badge.',
        );
    }

    protected function createAuthenticator(MultiFactorAuthBadge $multiFactorAuthBadge): OauthCustomerTokenAuthenticator
    {
        $resourceOwnerResponseTransfer = (new ResourceOwnerResponseTransfer())
            ->setIsSuccessful(true)
            ->setResourceOwner((new ResourceOwnerTransfer())->setEmail(static::SOME_CUSTOMER_EMAIL));

        $resourceOwnerReaderMock = $this->createMock(ResourceOwnerReaderInterface::class);
        $resourceOwnerReaderMock->method('getResourceOwner')->willReturn($resourceOwnerResponseTransfer);

        $oauthCustomerResolveResponseTransfer = (new OauthCustomerResolveResponseTransfer())
            ->setIsSuccessful(true)
            ->setCustomer($this->createCustomerTransfer());

        $customerClientMock = $this->createMock(CustomerPageToCustomerClientInterface::class);
        $customerClientMock->method('resolveCustomer')->willReturn($oauthCustomerResolveResponseTransfer);

        return new OauthCustomerTokenAuthenticator(
            $resourceOwnerReaderMock,
            $customerClientMock,
            $this->createMock(AuthenticationSuccessHandlerInterface::class),
            $this->createMock(AuthenticationFailureHandlerInterface::class),
            $this->createMock(CustomerPageConfig::class),
            $multiFactorAuthBadge,
        );
    }

    protected function createMultiFactorAuthBadge(bool $isRequired, ?int $status = null): MultiFactorAuthBadge
    {
        $pluginMock = $this->createMock(AuthenticationHandlerPluginInterface::class);
        $pluginMock->method('isApplicable')->willReturn(true);
        $pluginMock->method('validateCustomerMultiFactorStatus')->willReturn(
            (new MultiFactorAuthValidationResponseTransfer())
                ->setIsRequired($isRequired)
                ->setStatus($status),
        );

        return new MultiFactorAuthBadge([$pluginMock]);
    }

    protected function createOauthCallbackRequest(): Request
    {
        return new Request(['code' => static::SOME_CODE, 'state' => static::SOME_STATE]);
    }

    protected function createCustomerTransfer(): CustomerTransfer
    {
        return (new CustomerTransfer())->setEmail(static::SOME_CUSTOMER_EMAIL);
    }

    protected function createPassportWithoutBadge(): Passport
    {
        $customerTransfer = $this->createCustomerTransfer();

        return new SelfValidatingPassport(
            new UserBadge(
                static::SOME_CUSTOMER_EMAIL,
                fn (string $email): Customer => new Customer($customerTransfer, $email, '', [static::ROLE_USER]),
            ),
        );
    }
}
