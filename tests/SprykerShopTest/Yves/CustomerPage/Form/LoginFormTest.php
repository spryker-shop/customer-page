<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CustomerPage\Form;

use Codeception\Test\Unit;
use SprykerShop\Yves\CustomerPage\CustomerPageConfig;
use SprykerShop\Yves\CustomerPage\CustomerPageDependencyProvider;
use SprykerShop\Yves\CustomerPage\Form\LoginForm;
use SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\CustomerLoginFormExpanderPluginInterface;
use SprykerShopTest\Yves\CustomerPage\CustomerPageTester;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

/**
 * @group SprykerShopTest
 * @group Yves
 * @group CustomerPage
 * @group Form
 * @group LoginFormTest
 */
class LoginFormTest extends Unit
{
    protected const string FIELD_CAPTCHA = 'captcha';

    protected CustomerPageTester $tester;

    /**
     * @var \SprykerShop\Yves\CustomerPage\Form\LoginForm|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $loginForm;

    /**
     * @var \Symfony\Component\Form\FormBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $builderMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginForm = $this->getMockBuilder(LoginForm::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addRememberMeField', 'getConfig'])
            ->getMock();
        $this->loginForm->setFactory($this->tester->getFactory());

        $this->builderMock = $this->getMockBuilder(FormBuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testRememberMeFieldIsAddedWhenEnabled(): void
    {
        // Arrange
        $configMock = $this->getConfigMock(true);

        // Assert
        $this->loginForm->expects($this->once())
            ->method('getConfig')
            ->willReturn($configMock);

        $this->loginForm->expects($this->once())
            ->method('addRememberMeField')
            ->with($this->builderMock);

        // Act
        $this->loginForm->buildForm($this->builderMock, []);
    }

    public function testRememberMeFieldIsNotAddedWhenDisabled(): void
    {
        // Arrange
        $configMock = $this->getConfigMock(false);

        // Assert
        $this->loginForm->expects($this->once())
            ->method('getConfig')
            ->willReturn($configMock);

        $this->loginForm->expects($this->never())
            ->method('addRememberMeField')
            ->with($this->builderMock);

        // Act
        $this->loginForm->buildForm($this->builderMock, []);
    }

    public function testBuildFormAddsFieldOfCustomerLoginFormExpanderPluginAfterRememberMeField(): void
    {
        // Arrange
        $this->tester->setDependency(CustomerPageDependencyProvider::PLUGINS_CUSTOMER_LOGIN_FORM_EXPANDER, [
            $this->createCaptchaFieldExpanderPlugin(),
        ]);

        // Act
        $form = $this->createLoginForm();

        // Assert
        $this->assertSame(
            [LoginForm::FIELD_EMAIL, LoginForm::FIELD_PASSWORD, LoginForm::FIELD_REMEMBER_ME, static::FIELD_CAPTCHA],
            array_keys($form->all()),
        );
    }

    public function testBuildFormKeepsLoginFieldsWhenNoCustomerLoginFormExpanderPluginIsRegistered(): void
    {
        // Act
        $form = $this->createLoginForm();

        // Assert
        $this->assertSame(
            [LoginForm::FIELD_EMAIL, LoginForm::FIELD_PASSWORD, LoginForm::FIELD_REMEMBER_ME],
            array_keys($form->all()),
        );
    }

    /**
     * @return \Symfony\Component\Form\FormInterface<mixed>
     */
    protected function createLoginForm(): FormInterface
    {
        $loginForm = new LoginForm();
        $loginForm->setFactory($this->tester->getFactory());
        $loginForm->setConfig($this->getConfigMock(true));

        return Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->addType($loginForm)
            ->getFormFactory()
            ->create(LoginForm::class);
    }

    protected function createCaptchaFieldExpanderPlugin(): CustomerLoginFormExpanderPluginInterface
    {
        $customerLoginFormExpanderPluginMock = $this->createMock(CustomerLoginFormExpanderPluginInterface::class);
        $customerLoginFormExpanderPluginMock->expects($this->once())
            ->method('expand')
            ->willReturnCallback(function (FormBuilderInterface $builder): FormBuilderInterface {
                return $builder->add(static::FIELD_CAPTCHA, TextType::class);
            });

        return $customerLoginFormExpanderPluginMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\SprykerShop\Yves\CustomerPage\CustomerPageConfig
     */
    protected function getConfigMock(bool $isRememberMeEnabled = false): CustomerPageConfig
    {
        $configMock = $this->getMockBuilder(CustomerPageConfig::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isRememberMeEnabled'])
            ->getMock();

        $configMock->method('isRememberMeEnabled')->willReturn($isRememberMeEnabled);

        return $configMock;
    }
}
