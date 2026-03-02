<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Reader;

use Generated\Shared\Transfer\FilterTransfer;
use Generated\Shared\Transfer\OrderListFormatTransfer;
use Generated\Shared\Transfer\OrderListTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use SprykerShop\Yves\CustomerPage\CustomerPageConfig;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToCustomerClientInterface;
use SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToSalesClientInterface;
use Symfony\Component\HttpFoundation\Request;

class OrderReader implements OrderReaderInterface
{
    /**
     * @var string
     */
    protected const PARAM_PAGE = 'page';

    /**
     * @var string
     */
    protected const PARAM_PER_PAGE = 'perPage';

    /**
     * @var int
     */
    protected const DEFAULT_PAGE = 1;

    /**
     * @var \SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToSalesClientInterface
     */
    protected $salesClient;

    /**
     * @var \SprykerShop\Yves\CustomerPage\Dependency\Client\CustomerPageToCustomerClientInterface
     */
    protected $customerClient;

    /**
     * @var \SprykerShop\Yves\CustomerPage\CustomerPageConfig
     */
    protected $customerPageConfig;

    public function __construct(
        CustomerPageToSalesClientInterface $salesClient,
        CustomerPageToCustomerClientInterface $customerClient,
        CustomerPageConfig $customerPageConfig
    ) {
        $this->salesClient = $salesClient;
        $this->customerPageConfig = $customerPageConfig;
        $this->customerClient = $customerClient;
    }

    public function getOrderList(Request $request, OrderListTransfer $orderListTransfer): OrderListTransfer
    {
        $orderListTransfer = $this->expandOrderListTransfer($request, $orderListTransfer);

        if ($this->customerPageConfig->isOrderSearchEnabled()) {
            return $this->salesClient->getPaginatedCustomerOrdersOverview($orderListTransfer, true);
        }

        return $this->salesClient->getPaginatedCustomerOrdersOverview($orderListTransfer);
    }

    protected function expandOrderListTransfer(Request $request, OrderListTransfer $orderListTransfer): OrderListTransfer
    {
        $customerTransfer = $this->customerClient->getCustomer();

        $orderListTransfer
            ->setIdCustomer($customerTransfer->getIdCustomer())
            ->setCustomerReference($customerTransfer->getCustomerReference());

        $orderListTransfer->setPagination(
            $this->createPaginationTransfer($request),
        );

        if (!$orderListTransfer->getFilter()) {
            $orderListTransfer->setFilter($this->createFilterTransfer());
        }

        if (!$orderListTransfer->getFormat()) {
            $orderListTransfer->setFormat(new OrderListFormatTransfer());
        }

        return $orderListTransfer;
    }

    protected function createFilterTransfer(): FilterTransfer
    {
        $filterTransfer = new FilterTransfer();
        $filterTransfer->setOrderBy($this->customerPageConfig->getDefaultOrderHistorySortField());
        $filterTransfer->setOrderDirection($this->customerPageConfig->getDefaultOrderHistorySortDirection());

        return $filterTransfer;
    }

    protected function createPaginationTransfer(Request $request): PaginationTransfer
    {
        $paginationTransfer = new PaginationTransfer();

        $paginationTransfer->setPage(
            $request->query->getInt(static::PARAM_PAGE, static::DEFAULT_PAGE),
        );
        $paginationTransfer->setMaxPerPage(
            $request->query->getInt(static::PARAM_PER_PAGE, $this->customerPageConfig->getDefaultOrderHistoryPerPage()),
        );

        return $paginationTransfer;
    }
}
