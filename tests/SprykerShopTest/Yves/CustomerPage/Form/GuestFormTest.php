<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CustomerPage\Form;

use SprykerShop\Yves\CustomerPage\Form\GuestForm;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

/**
 * @group SprykerShopTest
 * @group Yves
 * @group CustomerPage
 * @group Form
 * @group GuestFormTest
 */
class GuestFormTest extends TypeTestCase
{
    /**
     * @var string
     */
    protected const INVALID_EMAIL = 'test<>@spryker.com';

    /**
     * @return void
     */
    public function testGuestFormIsValid()
    {
        // Arrange
        $guestForm = $this->factory->create(GuestForm::class);
        $data = $this->getCorrectTestData();

        // Act
        $guestForm->submit($data);

        // Assert
        $this->assertTrue($guestForm->isSynchronized());
        $this->assertTrue($this->isFormValid($guestForm));
    }

    /**
     * @return void
     */
    public function testEmailIsRequired(): void
    {
        // Arrange
        $guestForm = $this->factory->create(GuestForm::class);
        $data = $this->getCorrectTestData();
        $data[GuestForm::FIELD_EMAIL] = '';

        // Act
        $guestForm->submit($data);

        // Assert
        $this->assertTrue($guestForm->isSynchronized());
        $this->assertFalse($this->isFormValid($guestForm));
    }

    /**
     * @return void
     */
    public function testEmailIsNotValid(): void
    {
        // Arrange
        $guestForm = $this->factory->create(GuestForm::class);
        $data = $this->getCorrectTestData();
        $data[GuestForm::FIELD_EMAIL] = static::INVALID_EMAIL;

        // Act
        $guestForm->submit($data);

        // Assert
        $this->assertTrue($guestForm->isSynchronized());
        $this->assertFalse($this->isFormValid($guestForm));
    }

    /**
     * @return array<string, mixed>
     */
    protected function getCorrectTestData(): array
    {
        return [
            GuestForm::FIELD_SALUTATION => 'Mr',
            GuestForm::FIELD_FIRST_NAME => 'Dummy',
            GuestForm::FIELD_LAST_NAME => 'Dummyngo',
            GuestForm::FIELD_EMAIL => 'dummy@dummy.com',
            GuestForm::FIELD_IS_GUEST => true,
            GuestForm::FIELD_ACCEPT_TERMS => '1',
        ];
    }

    /**
     * @return list<\Symfony\Component\Form\FormExtensionInterface>
     */
    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(
                Validation::createValidator(),
            ),
        ];
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     *
     * @return bool
     */
    protected function isFormValid(FormInterface $form): bool
    {
        foreach ($form as $element) {
            if ($element->getErrors()->count() !== 0) {
                return false;
            }
        }

        return true;
    }
}
