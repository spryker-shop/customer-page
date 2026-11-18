<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Form\Hydrator;

use ArrayObject;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerShop\Yves\CustomerPage\Dependency\Service\CustomerPageToShipmentServiceInterface;
use SprykerShop\Yves\CustomerPage\Form\CheckoutAddressCollectionForm;
use SprykerShop\Yves\CustomerPage\Form\CheckoutAddressForm;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class ShippingAddressFormHydrator implements ShippingAddressFormHydratorInterface
{
    /**
     * @var array<\SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\CheckoutAddressStepPreGroupItemsByShipmentPluginInterface>
     */
    protected array $checkoutAddressStepPreGroupItemsByShipmentPlugins;

    /**
     * @param array<\SprykerShop\Yves\CustomerPageExtension\Dependency\Plugin\CheckoutAddressStepPreGroupItemsByShipmentPluginInterface> $checkoutAddressStepPreGroupItemsByShipmentPlugins
     */
    public function __construct(array $checkoutAddressStepPreGroupItemsByShipmentPlugins)
    {
        $this->checkoutAddressStepPreGroupItemsByShipmentPlugins = $checkoutAddressStepPreGroupItemsByShipmentPlugins;
    }

    public function hydrateShippingAddressSubForm(FormEvent $event, CustomerPageToShipmentServiceInterface $shipmentService): void
    {
        $quoteTransfer = $event->getData();

        if (!($quoteTransfer instanceof QuoteTransfer)) {
            return;
        }

        $quoteTransfer = $this->executeCheckoutAddressStepPreGroupItemsByShipmentPlugins($quoteTransfer);

        $shipmentGroupCollection = $this->mergeShipmentGroupsByShipmentHash(
            $shipmentService->groupItemsByShipment($quoteTransfer->getItems()),
            $shipmentService->groupItemsByShipment($quoteTransfer->getBundleItems()),
        );

        $shippingAddressForm = $event->getForm()->get(CheckoutAddressCollectionForm::FIELD_SHIPPING_ADDRESS);

        if (count($shipmentGroupCollection) > 1) {
            $this->setDeliverToMultipleAddressesEnabled($shippingAddressForm);

            return;
        }

        if ($this->isDeliverToMultipleAddressesEnabled($shippingAddressForm) || $shipmentGroupCollection->count() < 1) {
            return;
        }

        $shipmentGroupTransfer = $shipmentGroupCollection->getIterator()->current();

        if (!$shipmentGroupTransfer->getShipment() || !$shipmentGroupTransfer->getShipment()->getShippingAddress()) {
            return;
        }

        $shippingAddressForm->setData(clone $shipmentGroupTransfer->getShipment()->getShippingAddress());
    }

    public function executeCheckoutAddressStepPreGroupItemsByShipmentPlugins(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        foreach ($this->checkoutAddressStepPreGroupItemsByShipmentPlugins as $checkoutAddressStepPreGroupItemsByShipmentPlugin) {
            $quoteTransfer = $checkoutAddressStepPreGroupItemsByShipmentPlugin->preGroupItemsByShipment($quoteTransfer);
        }

        return $quoteTransfer;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ShipmentGroupTransfer> $shipmentGroupCollection
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ShipmentGroupTransfer> $bundleItemsShipmentGroupCollection
     *
     * @return \ArrayObject<string, \Generated\Shared\Transfer\ShipmentGroupTransfer>
     */
    public function mergeShipmentGroupsByShipmentHash(
        ArrayObject $shipmentGroupCollection,
        ArrayObject $bundleItemsShipmentGroupCollection
    ): ArrayObject {
        $indexedShipmentGroups = [];

        foreach ($shipmentGroupCollection as $shipmentGroupTransfer) {
            $indexedShipmentGroups[$shipmentGroupTransfer->getHash()] = $shipmentGroupTransfer;
        }

        foreach ($bundleItemsShipmentGroupCollection as $shipmentGroupTransfer) {
            $indexedShipmentGroups[$shipmentGroupTransfer->getHash()] = $shipmentGroupTransfer;
        }

        return new ArrayObject($indexedShipmentGroups);
    }

    public function setDeliverToMultipleAddressesEnabled(FormInterface $form): FormInterface
    {
        if (!$form->has(CheckoutAddressForm::FIELD_ID_CUSTOMER_ADDRESS)) {
            return $form;
        }

        $selectedDeliverToMultiShipmentAddressTransfer = (new AddressTransfer())
            ->setIdCustomerAddress((int)CheckoutAddressForm::VALUE_DELIVER_TO_MULTIPLE_ADDRESSES);

        $form->setData($selectedDeliverToMultiShipmentAddressTransfer);

        return $form;
    }

    public function isDeliverToMultipleAddressesEnabled(FormInterface $form): bool
    {
        if (!$form->has(CheckoutAddressForm::FIELD_ID_CUSTOMER_ADDRESS)) {
            return false;
        }

        $idCustomerAddress = $form->get(CheckoutAddressForm::FIELD_ID_CUSTOMER_ADDRESS)->getViewData();

        return $idCustomerAddress === CheckoutAddressForm::VALUE_DELIVER_TO_MULTIPLE_ADDRESSES;
    }
}
