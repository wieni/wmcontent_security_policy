<?php

namespace Drupal\wmcontent_security_policy\Service;

use Drupal\Core\State\StateInterface;

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
    ];

    /** @var StateInterface */
    protected $state;
    /** @var array */
    protected $scriptHashes = [];

    public function __construct(
        StateInterface $state
    ) {
        $this->state = $state;
    }

    public static function getDefaultSources(): array
    {
        $defaults = [
            'default-src' => [
                '\'self\'',
            ],
            'script-src' => [
                '\'self\'',
                'cookie.wieni.be',
                'www.google.com',
                'www.gstatic.com',
                'www.googletagmanager.com',
                'www.google-analytics.com',
                'tagmanager.google.com',
                '\'sha256-n4MwUTyKKCBlMIFkvcS3dkmlRFEcqSm/V0IOhZelzA0=\'', //inline gtm script (public/themes/custom/drupack/templates/drupal/html.html.twig)
                'polyfill.io',
            ],
            'style-src' => [
                '\'self\'',
                '\'unsafe-inline\'',
                'fonts.googleapis.com',
                'cookie.wieni.be',
                'tagmanager.google.com',
                'use.fontawesome.com',
                'use.typekit.net',
                'p.typekit.net',
                'blob:',
            ],
            'img-src' => [
                '\'self\'',
                'data:',
                'www.google-analytics.com',
            ],
            'font-src' => [
                '\'self\'',
                'use.fontawesome.com',
                'use.typekit.net',
            ],
            'frame-src' => [
                '\'self\'',
                'www.google.com',
                'maps.google.com',
                'www.youtube.com',
                'player.vimeo.com',
            ],
            'connect-src' => [
                '\'self\'',
                'cookie.wieni.be',
                'use.typekit.net',
            ],
        ];

        if (!empty($_ENV['IMGIX_CDN'])) {
            $defaults['img-src'][] = $_ENV['IMGIX_CDN'];
        }
        if (!empty($_ENV['S3_CNAME'])) {
            $defaults['script-src'][] = $_ENV['S3_CNAME'];
            $defaults['connect-src'][] = $_ENV['S3_CNAME'];
            $defaults['style-src'][] = $_ENV['S3_CNAME'];
        }

        return $defaults;
    }

    public function getSources(string $directive)
    {
        return $this->state->get(self::STATE_KEY_PREFIX . '.' . $directive, []);
    }

    public function setSources(string $directive, array $sources = []): void
    {
        if (!empty($sources)) {
//            $sources = array_diff($sources, array_values(self::getDefaultSources()[$directive]));
        }

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
        $sources = self::getDefaultSources();
        $sources['script-src'] = array_merge($sources['script-src'], $this->scriptHashes);

        $directives = array_map(
            function (string $directive) use ($sources): ?string {
                $sources = array_merge($sources[$directive] ?? [], $this->getSources($directive));
                if (!$sources) {
                    return null;
                }
                return $directive . ' ' . implode(' ', $sources);
            },
            array_merge(array_keys(self::POLICY_DIRECTIVES), ['worker-src'])
        );

        return implode('; ', array_filter($directives));
    }

    public function addScriptHash(string $hash): void
    {
        if (!in_array($hash, $this->scriptHashes, true)) {
            return;
        }

        $hash = trim($hash, '"\'');
        $this->scriptHashes[] = "'$hash'";
    }
}
