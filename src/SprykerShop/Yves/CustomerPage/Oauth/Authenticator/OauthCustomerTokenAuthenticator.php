<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Oauth\Authenticator;

use Generated\Shared\Transfer\OauthCustomerResolveRequestTransfer;
use Generated\Shared\Transfer\ResourceOwnerRequestTransfer;
use SprykerShop\Yves\CustomerPage\Badge\MultiFactorAuthBadge;
use SprykerShop\Yves\CustomerPage\CustomerPageConfig;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToCustomerClientInterface;
use SprykerShop\Yves\CustomerPage\Oauth\Exception\AuthenticationStrategyNotFoundException;
use SprykerShop\Yves\CustomerPage\Oauth\Reader\ResourceOwnerReaderInterface;
use SprykerShop\Yves\CustomerPage\Security\Customer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class OauthCustomerTokenAuthenticator extends AbstractAuthenticator
{
    protected const string ROLE_USER = 'ROLE_USER';

    protected const string ACCESS_MODE_PRE_AUTH = 'ACCESS_MODE_PRE_AUTH';

    /**
     * @uses \Spryker\Shared\MultiFactorAuth\MultiFactorAuthConstants::CODE_BLOCKED
     */
    protected const int CODE_BLOCKED = 1;

    /**
     * @see \SprykerShop\Yves\CustomerPage\Oauth\Security\Handler\OauthCustomerAuthenticationSuccessHandler::REQUEST_ATTRIBUTE_CUSTOMER
     */
    protected const string REQUEST_ATTRIBUTE_CUSTOMER = '_oauth_customer';

    protected const string REQUEST_PARAM_CODE = 'code';

    protected const string REQUEST_PARAM_STATE = 'state';

    protected const string ERROR_MESSAGE_AUTHENTICATION_FAILED = 'authentication.failed';

    protected const string ERROR_MESSAGE_CUSTOMER_RESTRICTED = 'customer.oauth.restricted';

    public function __construct(
        protected ResourceOwnerReaderInterface $resourceOwnerReader,
        protected CustomerPageToCustomerClientInterface $customerClient,
        protected AuthenticationSuccessHandlerInterface $authenticationSuccessHandler,
        protected AuthenticationFailureHandlerInterface $authenticationFailureHandler,
        protected CustomerPageConfig $customerPageConfig,
        protected MultiFactorAuthBadge $multiFactorAuthBadge,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->getString('_route') === $this->customerPageConfig->getOauthCallbackRouteName();
    }

    public function authenticate(Request $request): Passport
    {
        $code = $request->query->getString(static::REQUEST_PARAM_CODE);
        $state = $request->query->getString(static::REQUEST_PARAM_STATE);

        if ($code === '' || $state === '') {
            throw new CustomUserMessageAuthenticationException(static::ERROR_MESSAGE_AUTHENTICATION_FAILED);
        }

        $resourceOwnerRequestTransfer = (new ResourceOwnerRequestTransfer())
            ->setCode($code)
            ->setState($state);

        try {
            $resourceOwnerResponseTransfer = $this->resourceOwnerReader
                ->getResourceOwner($resourceOwnerRequestTransfer);
        } catch (AuthenticationStrategyNotFoundException $exception) {
            throw new CustomUserMessageAuthenticationException(static::ERROR_MESSAGE_AUTHENTICATION_FAILED);
        }

        if ($resourceOwnerResponseTransfer->getIsSuccessful() !== true) {
            throw new CustomUserMessageAuthenticationException(
                $resourceOwnerResponseTransfer->getErrorMessage() ?? static::ERROR_MESSAGE_AUTHENTICATION_FAILED,
            );
        }

        $resourceOwnerTransfer = $resourceOwnerResponseTransfer->getResourceOwnerOrFail();

        $oauthCustomerResolveResponseTransfer = $this->customerClient->resolveCustomer(
            (new OauthCustomerResolveRequestTransfer())->setResourceOwner($resourceOwnerTransfer),
        );

        if (!$oauthCustomerResolveResponseTransfer->getIsSuccessful()) {
            $errorMessage = static::ERROR_MESSAGE_CUSTOMER_RESTRICTED;

            foreach ($oauthCustomerResolveResponseTransfer->getMessages() as $messageTransfer) {
                $errorMessage = $messageTransfer->getMessage();

                break;
            }

            throw new CustomUserMessageAuthenticationException($errorMessage);
        }

        $customerTransfer = $oauthCustomerResolveResponseTransfer->getCustomerOrFail();

        // Store customer for use in success handler
        $request->attributes->set(static::REQUEST_ATTRIBUTE_CUSTOMER, $customerTransfer);

        return new SelfValidatingPassport(
            new UserBadge(
                $customerTransfer->getEmailOrFail(),
                fn (string $email) => new Customer($customerTransfer, $email, '', [static::ROLE_USER]),
            ),
            [$this->multiFactorAuthBadge->enable($customerTransfer)],
        );
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        return new PostAuthenticationToken(
            $passport->getUser(),
            $firewallName,
            $this->isUserPreAuthenticated($passport) ? [static::ACCESS_MODE_PRE_AUTH] : $passport->getUser()->getRoles(),
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return $this->authenticationSuccessHandler->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return $this->authenticationFailureHandler->onAuthenticationFailure($request, $exception);
    }

    protected function isUserPreAuthenticated(Passport $passport): bool
    {
        $badge = $passport->getBadge(MultiFactorAuthBadge::class);

        return $badge !== null && ($badge->getIsRequired() === true || $badge->getStatus() === static::CODE_BLOCKED);
    }
}
