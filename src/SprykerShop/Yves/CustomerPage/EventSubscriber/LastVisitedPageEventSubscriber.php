<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\EventSubscriber;

use SprykerShop\Yves\CustomerPage\Checker\LastVisitedPageUrlCheckerInterface;
use SprykerShop\Yves\CustomerPage\Storage\LastVisitedPageStorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LastVisitedPageEventSubscriber implements EventSubscriberInterface
{
    protected const int LISTENER_PRIORITY = -255;

    public function __construct(
        protected LastVisitedPageUrlCheckerInterface $lastVisitedPageUrlChecker,
        protected LastVisitedPageStorageInterface $lastVisitedPageStorage,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', static::LISTENER_PRIORITY],
            LogoutEvent::class => ['onLogout'],
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $response = $event->getResponse();

        if ($response === null) {
            return;
        }

        $this->lastVisitedPageStorage->clear($response);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->lastVisitedPageUrlChecker->isEligibleForPostLoginRedirect($request)) {
            return;
        }

        $this->lastVisitedPageStorage->save(
            $event->getResponse(),
            $request,
        );
    }
}
