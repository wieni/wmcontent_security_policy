<?php

namespace Drupal\wmcontent_security_policy\Twig\Extension;

use Drupal\wmcontent_security_policy\Service\ContentSecurityPolicyInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ContentSecurityPolicyScriptHash extends AbstractExtension
{
    /** @var ContentSecurityPolicyInterface */
    protected $contentSecurityPolicy;

    public function __construct(
        ContentSecurityPolicyInterface $contentSecurityPolicy
    ) {
        $this->contentSecurityPolicy = $contentSecurityPolicy;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('addCspHash', [$this, 'addCspHash']),
        ];
    }

    public function addCspHash(string $hash): void
    {
        $this->contentSecurityPolicy->addScriptHash($hash);
    }
}
