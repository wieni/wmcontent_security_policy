<?php

namespace Drupal\wmcontent_security_policy\Service;

interface ContentSecurityPolicyInterface
{
    public const POLICY_DIRECTIVES = [
        'default-src' => 'A fallback for other resource types when they don\'t have policies of their own',
        'script-src' => 'Specifies valid sources for JavaScript',
        'style-src' => 'Specifies valid sources for sources for stylesheets',
        'img-src' => 'Specifies valid sources of images and favicons',
        'font-src' => 'Specifies valid sources for fonts loaded using @font-face',
        'frame-src' => 'Specifies valid sources for nested browsing contexts loading using elements such as <iframe>',
        'connect-src' => 'Restricts the URLs which can be loaded using script interfaces such as <a>, Fetch and XMLHttpRequest',
        'worker-src' => 'Specifies valid sources for Worker, SharedWorker, or ServiceWorker scripts.',
        'object-src' => 'Specifies valid sources for the <object>, <embed>, and <applet> elements.',
    ];

    public function getDefaultSources(string $directive): array;

    public function setDefaultSources(string $directive, array $sources = []): void;

    public function getSources(string $directive);

    public function setSources(string $directive, array $sources = []): void;

    public function addSource(string $directive, string $source): void;

    public function getHeader(): string;

    public function addScriptHash(string $hash): void;
}
