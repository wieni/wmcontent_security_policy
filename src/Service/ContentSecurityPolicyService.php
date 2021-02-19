<?php

namespace Drupal\wmcontent_security_policy\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\wmcontent_security_policy\ContentSecurityPolicyEvents;
use Drupal\wmcontent_security_policy\Event\SourcesAlterEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ContentSecurityPolicyService
{
    public const STATE_KEY_PREFIX = 'wmcontent_security_policy.content_security_policy';

    public const POLICY_DIRECTIVES = [
        'default-src' => 'A fallback for other resource types when they don\'t have policies of their own',
        'script-src' => 'Specifies valid sources for JavaScript',
        'style-src' => 'Specifies valid sources for sources for stylesheets',
        'img-src' => 'Specifies valid sources of images and favicons',
        'font-src' => 'Specifies valid sources for fonts loaded using @font-face',
        'frame-src' => 'Specifies valid sources for nested browsing contexts loading using elements such as <iframe>',
        'connect-src' => 'Restricts the URLs which can be loaded using script interfaces such as <a>, Fetch and XMLHttpRequest',
        'worker-src' => 'Specifies valid sources for Worker, SharedWorker, or ServiceWorker scripts.',
    ];

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    /** @var ConfigFactoryInterface */
    protected $configFactory;
    /** @var StateInterface */
    protected $state;
    /** @var array */
    protected $scriptHashes = [];

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ConfigFactoryInterface $configFactory,
        StateInterface $state
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->configFactory = $configFactory;
        $this->state = $state;
    }

    public function getDefaultSources(string $directive): array
    {
        return $this->configFactory
            ->get('wmcontent_security_policy.default_sources')
            ->get($directive) ?? [];
    }

    public function setDefaultSources(string $directive, array $sources = []): void
    {
        $this->configFactory
            ->getEditable('wmcontent_security_policy.default_sources')
            ->set($directive, $sources)
            ->save();
    }

    public function getSources(string $directive)
    {
        return $this->state->get(self::STATE_KEY_PREFIX . '.' . $directive, []);
    }

    public function setSources(string $directive, array $sources = []): void
    {
        $this->state->set(self::STATE_KEY_PREFIX . '.' . $directive, $sources);
    }

    public function addSource(string $directive, string $source): void
    {
        $source = trim($source);
        $sources = $this->getSources($directive);

        if (!in_array($source, $sources, true)) {
            $sources[] = $source;
        }

        $this->setSources($directive, $sources);
    }

    public function getHeader(): string
    {
        $directives = [];

        foreach (static::POLICY_DIRECTIVES as $key => $description) {
            $sources = array_merge(
                array_column($this->getDefaultSources($key), 'source'),
                array_column($this->getSources($key), 'source')
            );

            if ($key === 'script-src') {
                $sources = array_merge($sources, $this->scriptHashes);
            }

            $sources = array_unique($sources);

            if (empty($sources)) {
                continue;
            }

            $directives[$key] = $sources;
        }

        $this->eventDispatcher->dispatch(
            ContentSecurityPolicyEvents::SOURCES_ALTER,
            new SourcesAlterEvent($directives)
        );

        $directives = array_map(
            static function (string $key, array $sources): string {
                return $key . ' ' . implode(' ', $sources);
            },
            array_keys($directives),
            array_values($directives)
        );

        return implode('; ', $directives);
    }

    public function addScriptHash(string $hash): void
    {
        if (in_array($hash, $this->scriptHashes, true)) {
            return;
        }

        $hash = trim($hash, '"\'');
        $this->scriptHashes[] = "'$hash'";
    }
}
