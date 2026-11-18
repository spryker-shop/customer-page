<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Form\Hydrator;

use ArrayObject;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerShop\Yves\CustomerPage\Dependency\Service\CustomerPageToShipmentServiceInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

interface ShippingAddressFormHydratorInterface
{
    public function hydrateShippingAddressSubForm(FormEvent $event, CustomerPageToShipmentServiceInterface $shipmentService): void;

    public function executeCheckoutAddressStepPreGroupItemsByShipmentPlugins(QuoteTransfer $quoteTransfer): QuoteTransfer;

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ShipmentGroupTransfer> $shipmentGroupCollection
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ShipmentGroupTransfer> $bundleItemsShipmentGroupCollection
     *
     * @return \ArrayObject<string, \Generated\Shared\Transfer\ShipmentGroupTransfer>
     */
    public function mergeShipmentGroupsByShipmentHash(
        ArrayObject $shipmentGroupCollection,
        ArrayObject $bundleItemsShipmentGroupCollection
    ): ArrayObject;

    public function setDeliverToMultipleAddressesEnabled(FormInterface $form): FormInterface;

    public function isDeliverToMultipleAddressesEnabled(FormInterface $form): bool;
}
