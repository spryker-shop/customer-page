<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Dependency\Plugin\BusinessOnBehalfWidget;

use Spryker\Yves\Kernel\Dependency\Plugin\WidgetPluginInterface;

interface MenuItemBusinessOnBehalfWidgetPluginInterface extends WidgetPluginInterface
{
    const NAME = 'MenuItemBusinessOnBehalfWidgetPlugin';

    /**
     * @return void
     */
    public function initialize(): void;
}