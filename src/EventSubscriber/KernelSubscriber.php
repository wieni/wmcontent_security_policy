<?php

namespace Drupal\wmcontent_security_policy\EventSubscriber;

use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\wmcontent_security_policy\Service\ContentSecurityPolicyService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class KernelSubscriber implements EventSubscriberInterface
{
    /** @var ContentSecurityPolicyService */
    protected $contentSecurityPolicy;
    /** @var RouteMatchInterface */
    protected $routeMatch;
    /** @var AdminContext */
    protected $adminContext;

    public function __construct(
        ContentSecurityPolicyService $contentSecurityPolicyService,
        RouteMatchInterface $routeMatch,
        AdminContext $adminContext
    ) {
        $this->contentSecurityPolicy = $contentSecurityPolicyService;
        $this->routeMatch = $routeMatch;
        $this->adminContext = $adminContext;
    }

    public static function getSubscribedEvents(): array
    {
        $events[KernelEvents::RESPONSE][] = ['setContentSecurityPolicyHeader'];

        return $events;
    }

    public function setContentSecurityPolicyHeader(FilterResponseEvent $event): void
    {
        if ($this->adminContext->isAdminRoute($this->routeMatch->getRouteObject())) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set(
            'content-security-policy',
            $this->contentSecurityPolicy->getHeader()
        );
    }
}
