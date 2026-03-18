<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Resolver;

use Spryker\Service\Http\HttpServiceInterface;
use SprykerShop\Yves\CustomerPage\Storage\LastVisitedPageStorageInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LastVisitedPageRedirectResolver implements LastVisitedPageRedirectResolverInterface
{
    public function __construct(
        protected RequestStack $requestStack,
        protected LastVisitedPageStorageInterface $lastVisitedPageStorage,
        protected HttpServiceInterface $httpService,
    ) {
    }

    public function isApplicable(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return false;
        }

        return $this->lastVisitedPageStorage->get($request) !== '';
    }

    public function getRedirectUrl(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return '';
        }

        $url = $this->lastVisitedPageStorage->get($request);

        if (!$this->httpService->isValidRelativeUrl($url)) {
            return '';
        }

        return $url;
    }
}
