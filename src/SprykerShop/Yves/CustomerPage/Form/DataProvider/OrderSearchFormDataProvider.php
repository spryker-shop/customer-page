<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Form\DataProvider;

use SprykerShop\Yves\CustomerPage\CustomerPageConfig;
use SprykerShop\Yves\CustomerPage\Form\OrderSearchForm;

class OrderSearchFormDataProvider
{
    /**
     * @var \SprykerShop\Yves\CustomerPage\CustomerPageConfig
     */
    protected $customerPageConfig;

    /**
     * @var string|null
     */
    protected $currentTimezone;

    public function __construct(CustomerPageConfig $customerPageConfig, ?string $currentTimezone)
    {
        $this->customerPageConfig = $customerPageConfig;
        $this->currentTimezone = $currentTimezone;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return [
            OrderSearchForm::OPTION_ORDER_SEARCH_TYPES => $this->getOrderSearchTypes(),
            OrderSearchForm::OPTION_CURRENT_TIMEZONE => $this->getStoreTimezone(),
        ];
    }

    protected function getOrderSearchTypes(): array
    {
        $searchTypes = [];

        foreach ($this->customerPageConfig->getOrderSearchTypes() as $searchType) {
            $searchTypes[$this->generateSearchTypeGlossaryKey($searchType)] = $searchType;
        }

        return $searchTypes;
    }

    protected function getStoreTimezone(): ?string
    {
        return $this->currentTimezone;
    }

    protected function generateSearchTypeGlossaryKey(string $searchType): string
    {
        return sprintf('customer.order_history.search_type.%s', $searchType);
    }
}
