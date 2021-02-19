<?php

namespace Drupal\wmcontent_security_policy\Twig\Extension;

use Drupal\wmcontent_security_policy\Service\ContentSecurityPolicyService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ContentSecurityPolicyScriptHash extends AbstractExtension
{
    /** @var ContentSecurityPolicyService */
    protected $service;

    public function __construct(
        ContentSecurityPolicyService $service
    ) {
        $this->service = $service;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('addCspHash', [$this, 'addCspHash']),
        ];
    }

    public function addCspHash(string $hash): void
    {
        $this->service->addScriptHash($hash);
    }
}
