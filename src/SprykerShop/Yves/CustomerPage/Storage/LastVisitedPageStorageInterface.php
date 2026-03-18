<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Storage;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface LastVisitedPageStorageInterface
{
    public function save(Response $response, Request $request): void;

    public function get(Request $request): string;

    public function clear(Response $response): void;
}
