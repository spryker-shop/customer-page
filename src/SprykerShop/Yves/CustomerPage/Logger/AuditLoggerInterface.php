<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Logger;

use Generated\Shared\Transfer\CustomerResponseTransfer;

interface AuditLoggerInterface
{
    public function addFailedLoginAuditLog(): void;

    public function addSuccessfulLoginAuditLog(): void;

    public function addPasswordResetRequestedAuditLog(): void;

    public function addPasswordUpdatedAfterResetAuditLog(CustomerResponseTransfer $customerResponseTransfer): void;
}
