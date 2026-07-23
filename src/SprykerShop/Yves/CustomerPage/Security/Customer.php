<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Security;

use Generated\Shared\Transfer\CustomerTransfer;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

class Customer implements CustomerUserInterface, PasswordAuthenticatedUserInterface, EquatableInterface
{
    protected const string SERIALIZATION_KEY_CUSTOMER_TRANSFER = 'customerTransfer';

    protected const string SERIALIZATION_KEY_USERNAME = 'username';

    protected const string SERIALIZATION_KEY_ROLES = 'roles';

    protected const string SERIALIZATION_KEY_STATE_HASH = 'stateHash';

    /**
     * Sessions written before `__serialize()` was introduced used default object serialization,
     * where protected property names are prefixed with "\0*\0".
     */
    protected const string LEGACY_PROTECTED_PROPERTY_PREFIX = "\0*\0";

    /**
     * @var \Generated\Shared\Transfer\CustomerTransfer
     */
    protected $customerTransfer;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string|null
     */
    protected $password;

    /**
     * @var array
     */
    protected $roles = [];

    protected ?string $stateHash = null;

    /**
     * @param \Generated\Shared\Transfer\CustomerTransfer $customerTransfer
     * @param string $username
     * @param string $password
     * @param array $roles
     */
    public function __construct(CustomerTransfer $customerTransfer, $username, $password, array $roles = [])
    {
        $this->customerTransfer = $customerTransfer;
        $this->username = $username;
        $this->password = $password;
        $this->roles = $roles;
        $this->stateHash = $this->computeStateHash();
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return string|null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string|null The password
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function eraseCredentials(): void
    {
    }

    public function isEqualTo(SymfonyUserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        return $user->getStateHash() === $this->stateHash;
    }

    public function getStateHash(): ?string
    {
        return $this->stateHash;
    }

    public function __serialize(): array
    {
        $customerTransferData = $this->customerTransfer->modifiedToArray();
        unset($customerTransferData[CustomerTransfer::PASSWORD]);
        $cleanUserTransfer = (new CustomerTransfer())->fromArray($customerTransferData, true);

        return [
            static::SERIALIZATION_KEY_CUSTOMER_TRANSFER => $cleanUserTransfer,
            static::SERIALIZATION_KEY_USERNAME => $this->username,
            static::SERIALIZATION_KEY_ROLES => $this->roles,
            static::SERIALIZATION_KEY_STATE_HASH => $this->stateHash,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $data = $this->normalizeLegacySessionData($data);

        $this->customerTransfer = $data[static::SERIALIZATION_KEY_CUSTOMER_TRANSFER];
        $this->username = $data[static::SERIALIZATION_KEY_USERNAME];
        $this->roles = $data[static::SERIALIZATION_KEY_ROLES];
        $this->password = null;

        $this->stateHash = $data[static::SERIALIZATION_KEY_STATE_HASH] ?? $this->computeStateHash();
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function normalizeLegacySessionData(array $data): array
    {
        $normalizedData = [];

        foreach ($data as $key => $value) {
            $normalizedData[str_replace(static::LEGACY_PROTECTED_PROPERTY_PREFIX, '', $key)] = $value;
        }

        return $normalizedData;
    }

    public function getCustomerTransfer(): CustomerTransfer
    {
        return $this->customerTransfer;
    }

    protected function computeStateHash(): string
    {
        return hash('md5', implode(',', $this->roles));
    }
}
