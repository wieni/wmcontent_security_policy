<?php

namespace Drupal\wmcontent_security_policy\EventSubscriber;

use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\wmcontent_security_policy\Service\ContentSecurityPolicyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class KernelSubscriber implements EventSubscriberInterface
{
    /** @var ContentSecurityPolicyInterface */
    protected $contentSecurityPolicy;
    /** @var RouteMatchInterface */
    protected $routeMatch;
    /** @var AdminContext */
    protected $adminContext;

    public function __construct(
        ContentSecurityPolicyInterface $contentSecurityPolicy,
        RouteMatchInterface $routeMatch,
        AdminContext $adminContext
    ) {
        $this->contentSecurityPolicy = $contentSecurityPolicy;
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
        $contentSecurityPolicy = $this->contentSecurityPolicy->getHeader();

        if ($reportTo = $this->contentSecurityPolicy->getReportTo()) {
            $contentSecurityPolicy .= sprintf('; report-to %s', ContentSecurityPolicyInterface::REPORT_TO_CSP_ENDPOINT_NAME);
            $response->headers->set(
                'reporting-endpoints',
                sprintf('%s="%s"', ContentSecurityPolicyInterface::REPORT_TO_CSP_ENDPOINT_NAME, $reportTo)
            );
        }

        $response->headers->set(
            'content-security-policy',
            $contentSecurityPolicy
        );

        if ($response instanceof CacheableResponseInterface) {
            $response->getCacheableMetadata()->addCacheTags([
                'config:wmcontent_security_policy.default_sources',
                'wmcontent_security_policy.custom_sources',
            ]);
        }
    }
}
